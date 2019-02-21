<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-02-15
 * Time: 15:05
 */

namespace App\Task;


use App\Model\MovieCover;
use GuzzleHttp\Client;

class DownloadCover extends BaseTask
{
    public function execute(array $task)
    {
        // TODO: Implement execute() method.
        return $this->downloadCover($task[0], $task[1]);
    }

    /**
     * 下载封面
     * @return string
     */
    private function downloadCover($sourceCover, $coverSaveFilePath)
    {
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
        } else {
            return false;
        }

        return true;
    }
}
