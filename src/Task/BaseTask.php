<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-02-15
 * Time: 15:00
 */

namespace App\Task;


abstract class BaseTask
{
    abstract public function execute($task);
}
