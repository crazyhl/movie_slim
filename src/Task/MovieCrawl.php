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
    private $sourceName;
    private $sourceId;
    private $sourceLastUpdate;
    private $sourceCover;
    private $sourceLang;
    private $sourceArea;
    private $sourceYear;
    private $sourceNote;
    private $sourceActor;
    private $sourceDirector;
    private $sourceDescription;
    private $sourceCategoryRelationArr;
    private $coverSaveFilePath;
    private $sourceSiteId;
    private $sourceCategoryId;

    public function execute(array $task)
    {
        $this->sourceSiteId = $task[1];
        $queryParaArr = json_decode($task[2], true);
        // 实例化db
        $this->container->get('db');
        /**
         * @var $logger Logger
         */
        $logger = $this->container->get('logger');
        $sourceInfo = SourceInfo::find($this->sourceSiteId);
        if (empty($sourceInfo)) {
            $logger->error('movie:' . $this->sourceSiteId . '该源站不存在，任务跳过');
            return;
        }
        if (empty($sourceInfo->api_url)) {
            $logger->error('movie:' . $this->sourceSiteId . '该源站 api_url 不存在，任务跳过');
            return;
        }

        // 获取源站本地分类映射
        $sourceCategoryRelationArr = CategorySourceCategoryRelation::where('source_site_id', $this->sourceSiteId)->get()->toArray();
        // 如果分类没有做映射就不用查了，毕竟查了也不会插入数据
        if ($sourceCategoryRelationArr) {
            $totalPage = 1;
            $this->sourceCategoryRelationArr = array_combine(array_column($sourceCategoryRelationArr, 'source_site_category_id'), $sourceCategoryRelationArr);
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
                        $this->sourceCategoryId = $xmlElement->tid->__toString();

                        // 如果分类本地存在映射才继续执行
                        if ($this->sourceCategoryRelationArr[$this->sourceCategoryId]) {
                            // 生成所有源数据
                            $this->sourceName = $xmlElement->name->__toString();
                            $this->sourceId = $xmlElement->id->__toString();
                            $this->sourceLastUpdate = Carbon::createFromTimeString($xmlElement->last->__toString());
                            $this->sourceCover = $xmlElement->pic->__toString();
                            $this->sourceLang = $xmlElement->lang->__toString();
                            $this->sourceArea = $xmlElement->area->__toString();
                            $this->sourceYear = $xmlElement->year->__toString();
                            $this->sourceNote = $xmlElement->note->__toString();
                            $this->sourceActor = $xmlElement->actor->__toString();
                            $this->sourceDirector = $xmlElement->director->__toString();
                            $this->sourceDescription = $xmlElement->des->__toString();

                            $movieInfoSource = MovieInfoSource::where([
                                ['source_site_id', '=', $this->sourceSiteId],
                                ['source_id', '=', $this->sourceId],
                            ])->first();
                            // 首先根据 id 查询 movie_info_source 库
                            if ($movieInfoSource === null) {
                                // 如果查询不到，就说明是新的影片或剧集，这时候要新建 movio_info
                                // 然后在插入到 movie_info_source 中，为后续查询更新做准备
                                // 下载cover
                                $this->coverSaveFilePath = $this->downloadCover();
                                // 保存 movieInfo
                                $movieInfo = $this->saveMovieInfo(new MovieInfo());

                                // 保存 movieSourceInfo
                                $movieInfoSource = $this->saveMovieSourceInfo(new MovieInfoSource(), $movieInfo);

                                // 保存影片信息
                                foreach ($xmlElement->dl->children() as $sourceVideoList) {
                                    $this->saveMovieVideoList($movieInfo, $sourceVideoList);
                                }
                            } else if ($this->sourceLastUpdate->greaterThan($movieInfoSource->source_last_update)) {
                                // 如果存在并且 线上更新时间大于本地更新时间 则要更新数据
                                // 下载cover
                                $this->coverSaveFilePath = $this->downloadCover();
                                $movieInfo = MovieInfo::find($movieInfoSource->local_id);
                                if ($sourceInfo['is_default_info'] == 1) {
                                    // 如果采用默认数据，则需要更新movieInfo
                                    $movieInfo = $this->saveMovieInfo($movieInfo);
                                }

                                // 保存 movieSourceInfo
                                $movieInfoSource = $this->saveMovieSourceInfo($movieInfoSource, $movieInfo);
                                // 更新videoList
                                MovieVideoList::where([
                                    ['movie_info_id', '=', $movieInfo->id],
                                    ['source_site_id', '=', $this->sourceSiteId],
                                ])->delete();
                                foreach ($xmlElement->dl->children() as $sourceVideoList) {
                                    $this->saveMovieVideoList($movieInfo, $sourceVideoList);
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
                $this->sourceSiteId,
                $task[2],
                0
            ]));
        }
    }

    /**
     * 下载封面
     * @return string
     */
    private function downloadCover()
    {
        $coverSuffix = substr($this->sourceCover, strripos($this->sourceCover, '.'));
        $coverFileName = $this->sourceSiteId . '_' . $this->sourceId . $coverSuffix;
        $coverSaveFilePath = '/public/static/image/' . $coverFileName;;
        $coverFilePath = APP_DIR . $coverSaveFilePath;
        $resource = fopen($coverFilePath, 'w+');
        $stream = \GuzzleHttp\Psr7\stream_for($resource);
        $coverClient = new Client();
        $coverResponse = $coverClient->get($this->sourceCover, ['save_to' => $stream, 'verify' => false]);
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

        return $coverSaveFilePath;
    }

    /**
     * 保存或更新 MovieInfo
     * @param MovieInfo $movieInfo
     * @return MovieInfo
     */
    private function saveMovieInfo(MovieInfo $movieInfo)
    {
        $movieInfo->name = $this->sourceName;
        $movieInfo->show_name = $this->sourceName;
        $movieInfo->category_id = $this->sourceCategoryRelationArr[$this->sourceCategoryId]['category_id'];
        $movieInfo->cover = $this->coverSaveFilePath;
        $movieInfo->lang = $this->sourceLang;
        $movieInfo->area = $this->sourceArea;
        $movieInfo->year = $this->sourceYear;
        $movieInfo->note = $this->sourceNote;
        $movieInfo->actor = $this->sourceActor;
        $movieInfo->director = $this->sourceDirector;
        $movieInfo->description = $this->sourceDescription;
        $movieInfo->save();

        return $movieInfo;
    }

    /**
     * 保存源信息
     * @param MovieInfoSource $movieInfoSource
     * @param MovieInfo $movieInfo
     * @return MovieInfoSource
     */
    private function saveMovieSourceInfo(MovieInfoSource $movieInfoSource, MovieInfo $movieInfo)
    {
        $movieInfoSource->local_id = $movieInfo->id;
        $movieInfoSource->name = $this->sourceName;
        $movieInfoSource->show_name = $this->sourceName;
        $movieInfoSource->source_site_id = $this->sourceSiteId;
        $movieInfoSource->source_id = $this->sourceId;
        $movieInfoSource->source_category_id = $this->sourceCategoryId;
        $movieInfoSource->source_last_update = $this->sourceLastUpdate;
        $movieInfoSource->cover = $this->coverSaveFilePath;
        $movieInfoSource->lang = $this->sourceLang;
        $movieInfoSource->area = $this->sourceArea;
        $movieInfoSource->year = $this->sourceYear;
        $movieInfoSource->note = $this->sourceNote;
        $movieInfoSource->actor = $this->sourceActor;
        $movieInfoSource->director = $this->sourceDirector;
        $movieInfoSource->description = $this->sourceDescription;
        $movieInfoSource->save();

        return $movieInfoSource;
    }

    /**
     * @param MovieInfo $movieInfo
     * @param \SimpleXMLElement $sourceVideoList
     */
    private function saveMovieVideoList(MovieInfo $movieInfo, \SimpleXMLElement $sourceVideoList)
    {
        $movieVideoList = new MovieVideoList();
        $movieVideoList->movie_info_id = $movieInfo->id;
        $movieVideoList->video_info = $sourceVideoList->__toString();
        $movieVideoList->source_site_id = $this->sourceSiteId;
        $movieVideoList->save();
    }
}
