<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-02-15
 * Time: 15:05
 */

namespace App\Task;


use App\Model\SourceInfo;
use Monolog\Logger;

class MovieCrawl extends BaseTask
{

    public function execute(array $task)
    {
        $id = $task[1];
        $queryParaArr = json_decode($task[2], true);
        // 实例化db
        $this->container->get('db');
        /**
         * @var $logger Logger
         */
        $logger = $this->container->get('logger');
        $sourceInfo = SourceInfo::find($id);
        if (empty($sourceInfo)) {
            $logger->error('movie:' . $id . '该源站不存在，任务跳过');
            return;
        }
        if (empty($sourceInfo->api_url)) {
            $logger->error('movie:' . $id . '该源站 api_url 不存在，任务跳过');
            return;
        }

        // 处理完毕后检测是否需要再次投递任务
        // 重新投递的任务就只有抓取当天所有更新片子的任务，其余任务不投递
        if ($sourceInfo->is_async_crawl == 1 && $sourceInfo->crawl_interval > 0
            && $queryParaArr['action'] == 'videolist' && $queryParaArr['ids'] == ''
            && $queryParaArr['t'] == '' && $queryParaArr['h'] == 24) {
            /**
             * @var $redis \Redis
             */
            $redis = $this->container->get('redis');
            $crawlKey = $this->container->get('redisKey')['crawlRedisTaskQueueKey'];
            $redis->zAdd($crawlKey, time() + $sourceInfo->crawl_interval, implode('::', [
                'movie',
                $id,
                $task[2],
                0
            ]));
        }

    }
}
