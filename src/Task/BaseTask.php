<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-02-15
 * Time: 15:00
 */

namespace App\Task;


use Psr\Container\ContainerInterface;

abstract class BaseTask
{
    private $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    abstract public function execute(array $task);
}
