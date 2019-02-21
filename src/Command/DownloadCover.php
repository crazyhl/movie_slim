<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-02-14
 * Time: 17:29
 */

namespace App\Command;


use App\Task\BaseTask;
use App\Task\DownloadCover as DownloadCoverTask;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadCover extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'downloadCover:startTask';
    private $container;

    public function __construct(ContainerInterface $container, $name = null)
    {
        parent::__construct($name);
        $this->container = $container;
    }

    protected function configure()
    {
        $this->setDescription('开启下载封面图任务');
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
        $downloadCoverKey = $this->container->get('redisKey')['downloadCoverRedisTaskQueueKey'];
        // 死循环始终跑着任务
        while (true) {
            // 只弹出第一个任务
            $tasks = $redis->zRangeByScore($downloadCoverKey, 0, time(), ['withscores' => true, 'limit' => [0, 1]]);
            if (empty($tasks)) {
                // 随机休眠
                usleep(rand(100000, 1000000));
                continue;
            }
            // 有任务就处理任务,并且可以保证这个任务肯定是到时间了
            foreach ($tasks as $task => $time) {
                $redis->zDelete($downloadCoverKey, $task);
                $taskArr = explode('::', $task);
                $taskInstance = new DownloadCoverTask($this->container);
                if ($taskInstance instanceof BaseTask) {
                    $result = $taskInstance->execute($taskArr);
                    if ($result === false) {
                        // 处理失败的时候重新投递任务，需要检测次数
                        $count = $taskArr[2] + 1;
                        if ($count < 3) {
                            $taskArr[2] = $count;
                            $redis->zAdd($downloadCoverKey, $time + 5, implode('::', $taskArr));
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
