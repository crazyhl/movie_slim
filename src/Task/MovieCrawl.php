<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-02-15
 * Time: 15:05
 */

namespace App\Task;


use App\Model\CategorySourceCategoryRelation;
use App\Model\MovieCover;
use App\Model\MovieInfo;
use App\Model\MovieInfoSource;
use App\Model\MovieVideoList;
use App\Model\SourceInfo;
use Carbon\Carbon;
use GuzzleHttp\Client;
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

        // 获取源站本地分类映射
        $sourceCategoryRelationArr = CategorySourceCategoryRelation::where('source_site_id', $id)->get()->toArray();
        // 如果分类没有做映射就不用查了，毕竟查了也不会插入数据
        if ($sourceCategoryRelationArr) {
            $totalPage = 1;
            $sourceCategoryRelationArr = array_combine(array_column($sourceCategoryRelationArr, 'source_site_category_id'), $sourceCategoryRelationArr);
            for ($i = 1; $i <= $totalPage; $i++) {
                $queryParaArr['pg'] = $i;
                $requestUrl = $sourceInfo->api_url . '?' . http_build_query($queryParaArr);
                $guzzleClient = new Client();

                $response = $guzzleClient->get($requestUrl, ['verify' => false]);
                $statusCode = $response->getStatusCode();
                if ($statusCode == 200) {
                    // 首先获取一下总页数
                    $responseBody = $response->getBody();
                    $xmlElementArr = simplexml_load_string($responseBody, null, LIBXML_NOCDATA);
                    $pageCount = $xmlElementArr->children()->attributes()['pagecount']->__toString();
//                    $totalPage = $pageCount;
                    foreach ($xmlElementArr->children()->children() as $xmlElement) {
                        $sourceCategoryId = $xmlElement->tid->__toString();

                        // 如果分类本地存在映射才继续执行
                        if ($sourceCategoryRelationArr[$sourceCategoryId]) {
                            // 生成所有源数据
                            $sourceName = $xmlElement->name->__toString();
                            $sourceId = $xmlElement->id->__toString();
                            $sourceLastUpdate = Carbon::createFromTimeString($xmlElement->last->__toString());
                            $sourceCover = $xmlElement->pic->__toString();
                            $sourceLang = $xmlElement->lang->__toString();
                            $sourceArea = $xmlElement->area->__toString();
                            $sourceYear = $xmlElement->year->__toString();
                            $sourceNote = $xmlElement->note->__toString();
                            $sourceActor = $xmlElement->actor->__toString();
                            $sourceDirector = $xmlElement->director->__toString();
                            $sourceDescription = $xmlElement->des->__toString();

                            $movieInfoSource = MovieInfoSource::where([
                                ['source_site_id', '=', $id],
                                ['source_id', '=', $sourceId],
                            ])->first();
                            var_dump($movieInfoSource);

                            // 首先根据 id 查询 movie_info_source 库
                            if ($movieInfoSource === null) {
                                // 如果查询不到，就说明是新的影片或剧集，这时候要新建 movio_info
                                // 然后在插入到 movie_info_source 中，为后续查询更新做准备
                                // 下载cover
                                $coverSuffix = substr($sourceCover, strripos($sourceCover, '.'));
                                $coverFileName = $id . '_' . $sourceId . $coverSuffix;
                                $coverSaveFilePath = '/public/static/image/' . $coverFileName;;
                                $coverFilePath = APP_DIR . $coverSaveFilePath;
                                $resource = fopen($coverFilePath, 'w+');
                                $stream = \GuzzleHttp\Psr7\stream_for($resource);
                                $coverClient = new Client();
                                $coverResponse = $coverClient->get($sourceCover, ['save_to' => $stream, 'verify' => false]);
                                if ($coverResponse->getStatusCode() == 200) {
                                    // 图片下载成功
                                    $coverFileMd5 = md5_file($coverFilePath);
                                    $movieCover = MovieCover::where('file_md5', $coverFileMd5)->first();
                                    if ($movieCover === null) {
                                        // 文件不存在就创建
                                        $movieCover = new MovieCover();
                                        $movieCover->file_md5 = $coverFileMd5;
                                        $movieCover->file_path = $coverFilePath;
                                        $movieCover->save();
                                    } else {
                                        // 文件存在就删除好了
                                        unlink($coverFilePath);
                                    }
                                }
                                // 保存 movieInfo
                                $movieInfo = new MovieInfo();
                                $movieInfo->name = $sourceName;
                                $movieInfo->show_name = $sourceName;
                                $movieInfo->category_id = $sourceCategoryRelationArr[$sourceCategoryId]['category_id'];
                                $movieInfo->cover = $coverSaveFilePath;
                                $movieInfo->lang = $sourceLang;
                                $movieInfo->area = $sourceArea;
                                $movieInfo->year = $sourceYear;
                                $movieInfo->note = $sourceNote;
                                $movieInfo->actor = $sourceActor;
                                $movieInfo->director = $sourceDirector;
                                $movieInfo->description = $sourceDescription;
                                $movieInfo->save();
                                // 保存 movieSourceInfo
                                $movieInfoSource = new MovieInfoSource();
                                $movieInfoSource->local_id = $movieInfo->id;
                                $movieInfoSource->name = $sourceName;
                                $movieInfoSource->show_name = $sourceName;
                                $movieInfoSource->source_site_id = $id;
                                $movieInfoSource->source_id = $sourceId;
                                $movieInfoSource->source_category_id = $sourceCategoryId;
                                $movieInfoSource->source_last_update = $sourceLastUpdate;
                                $movieInfoSource->cover = $coverSaveFilePath;
                                $movieInfoSource->lang = $sourceLang;
                                $movieInfoSource->area = $sourceArea;
                                $movieInfoSource->year = $sourceYear;
                                $movieInfoSource->note = $sourceNote;
                                $movieInfoSource->actor = $sourceActor;
                                $movieInfoSource->director = $sourceDirector;
                                $movieInfoSource->description = $sourceDescription;
                                $movieInfoSource->save();
                                // 保存影片信息
                                foreach ($xmlElement->dl->children() as $sourceVideoList) {
                                    $movieVideoList = new MovieVideoList();
                                    $movieVideoList->movie_info_id = $movieInfo->id;
                                    $movieVideoList->video_info = $sourceVideoList->__toString();
                                    $movieVideoList->source_site_id = $id;
                                    $movieVideoList->save();
                                }
                            } else if ($sourceLastUpdate->greaterThan($movieInfoSource->source_last_update)) {
                                // 如果存在并且 线上更新时间大于本地更新时间 则要更新数据
                                // 下载cover
                                $coverSuffix = substr($sourceCover, strripos($sourceCover, '.'));
                                $coverFileName = $id . '_' . $sourceId . $coverSuffix;
                                $coverSaveFilePath = '/public/static/image/' . $coverFileName;;
                                $coverFilePath = APP_DIR . $coverSaveFilePath;
                                $resource = fopen($coverFilePath, 'w+');
                                $stream = \GuzzleHttp\Psr7\stream_for($resource);
                                $coverClient = new Client();
                                $coverResponse = $coverClient->get($sourceCover, ['save_to' => $stream, 'verify' => false]);
                                if ($coverResponse->getStatusCode() == 200) {
                                    // 图片下载成功
                                    $coverFileMd5 = md5_file($coverFilePath);
                                    $movieCover = MovieCover::where('file_md5', $coverFileMd5)->first();
                                    if ($movieCover === null) {
                                        // 文件不存在就创建
                                        $movieCover = new MovieCover();
                                        $movieCover->file_md5 = $coverFileMd5;
                                        $movieCover->file_path = $coverFilePath;
                                        $movieCover->save();
                                    } else {
                                        // 文件存在就删除好了
                                        unlink($coverFilePath);
                                    }
                                }
                                if ($sourceInfo['is_default_info'] == 1) {
                                    // 如果采用默认数据，则需要更新movieInfo
                                    $movieInfo = MovieInfo::find($movieInfoSource->local_id);
                                    if ($movieInfo) {
                                        // 如果找到了就更新movieInfo
                                        $movieInfo->name = $sourceName;
                                        $movieInfo->show_name = $sourceName;
                                        $movieInfo->category_id = $sourceCategoryRelationArr[$sourceCategoryId]['category_id'];
                                        $movieInfo->cover = $coverSaveFilePath;
                                        $movieInfo->lang = $sourceLang;
                                        $movieInfo->area = $sourceArea;
                                        $movieInfo->year = $sourceYear;
                                        $movieInfo->note = $sourceNote;
                                        $movieInfo->actor = $sourceActor;
                                        $movieInfo->director = $sourceDirector;
                                        $movieInfo->description = $sourceDescription;
                                        $movieInfo->save();
                                    }
                                }

                                // 保存 movieSourceInfo
                                $movieInfoSource->name = $sourceName;
                                $movieInfoSource->show_name = $sourceName;
                                $movieInfoSource->source_site_id = $id;
                                $movieInfoSource->source_id = $sourceId;
                                $movieInfoSource->source_category_id = $sourceCategoryId;
                                $movieInfoSource->source_last_update = $sourceLastUpdate;
                                $movieInfoSource->cover = $coverSaveFilePath;
                                $movieInfoSource->lang = $sourceLang;
                                $movieInfoSource->area = $sourceArea;
                                $movieInfoSource->year = $sourceYear;
                                $movieInfoSource->note = $sourceNote;
                                $movieInfoSource->actor = $sourceActor;
                                $movieInfoSource->director = $sourceDirector;
                                $movieInfoSource->description = $sourceDescription;
                                $movieInfoSource->save();
                                // 更新videoList
                                MovieVideoList::where([
                                    ['movie_info_id', '=', $movieInfo->id],
                                    ['source_site_id', '=', $id],
                                ])->delete();
                                foreach ($xmlElement->dl->children() as $sourceVideoList) {
                                    $movieVideoList = new MovieVideoList();
                                    $movieVideoList->movie_info_id = $movieInfo->id;
                                    $movieVideoList->video_info = $sourceVideoList->__toString();
                                    $movieVideoList->source_site_id = $id;
                                    $movieVideoList->save();
                                }
                            }
                        }
                    }
                } else {
                    // 如果不是 200 就说明出错了，重新投递
                    return false;
                }
            }
        }

        // 处理完毕后检测是否需要再次投递任务
        // 重新投递的任务就只有抓取当天所有更新片子的任务，其余任务不投递
        if ($sourceInfo->is_async_crawl == 1 && $sourceInfo->crawl_interval > 0
            && $queryParaArr['ac'] == 'videolist' && $queryParaArr['ids'] == ''
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
