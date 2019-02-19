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

class DownloadCover extends BaseTask
{
    public function execute(array $task)
    {
        // TODO: Implement execute() method.
    }
}
