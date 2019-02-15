<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-02-14
 * Time: 17:29
 */

namespace App\Command;


use App\Task\BaseTask;
use App\Task\MovieCrawl;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CrawlTask extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'crawl:startTask';
    private $container;

    public function __construct(ContainerInterface $container, $name = null)
    {
        parent::__construct($name);
        $this->container = $container;
    }

    protected function configure()
    {
        $this->setDescription('开启抓取任务');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->container->get('db');
        /**
         * @var $logger Logger
         */
        $logger = $this->container->get('logger');
        /**
         * @var $redis \Redis
         */
        $redis = $this->container->get('redis');
        $crawKey = $this->container->get('redisKey')['crawlRedisTaskQueueKey'];
        // 死循环始终跑着任务
        while (true) {
            // 只弹出第一个任务
            $tasks = $redis->zRangeByScore($crawKey, 0, time(), ['withscores' => true, 'limit' => [0, 1]]);
            if (empty($tasks)) {
                // 随机休眠
                usleep(rand(100000, 1000000));
                continue;
            }
            // 有任务就处理任务,并且可以保证这个任务肯定是到时间了
            foreach ($tasks as $task => $time) {
                // 首先删除任务，防止重复，虽然还是有概率重复，但是因为我们的任务对于唯一并没有要求，所以无所谓
                // 另外基本上我们只运行一个异步任务，所以这个应该不会有问题
                // 任务格式 类型::id::任务方案(json)::重试次数
                // 例如 1550217418-movie::4::{"action":"videolist","ids":"","t":"","h":"24"}::0
                // 这个 json 目前
                // 重试目前只重试3次，重试的时机在拉取信息失败的时候重试，解析失败不重试，因为解析失败属于应该更新代码
                // 而不是任务跑起来有问题
                $redis->zDelete($crawKey, $task);
                $taskArr = explode('::', $task);
                $taskInstance = null;
                switch ($taskArr[0]) {
                    case 'movie':
                        // 影视
                        $taskInstance = new MovieCrawl($this->container);
                        break;
                }

                if ($taskInstance instanceof BaseTask) {
                    $result = $taskInstance->execute($taskArr);
                    if ($result === false) {
                        // 处理失败的时候重新投递任务，需要检测次数
                        $count = $taskArr[3] + 1;
                        if ($count < 3) {
                            $redis->zAdd($crawKey, $time + 5, $task);
                            $logger->alert($task . ' 执行出错，重新投递 当前第' . ($count) . '次');
                        } else {
                            $logger->error($task
                                . ' 投递三次均执行出错，请检查目标站是否存活，如果存活，请检查解析器是否需要更新');
                        }
                    }
                } else {
                    $logger->error($task . ' 该任务没有找到对应任务实例，不处理');
                }

                $output->writeln($time . '-' . $task);
            }
        }
    }
}
