<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-02-15
 * Time: 15:05
 */

namespace App\Task;


class MovieCrawl extends BaseTask
{

    public function execute(array $task)
    {
        $id = $task[1];
        $queryParaArr = json_decode($task[2]);
        var_dump($id);
        var_dump($task[2]);
        var_dump($queryParaArr);
    }
}
