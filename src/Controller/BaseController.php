<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-02-13
 * Time: 15:38
 */

namespace App\Controller;


use Psr\Container\ContainerInterface;
use Slim\Http\Response;

class BaseController
{
    protected $container;
    protected $containerDependencies = [];

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        // 初始化数据库连接
        $this->container->get('db');
    }

    /**
     * 写一个魔术方法 用在在 controller 里面获取 container 里面的依赖
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        // TODO: Implement __get() method.
        if (isset($this->containerDependencies[$name])) {
            return $this->containerDependencies[$name];
        }

        $this->containerDependencies[$name] = $this->container->get($name);

        return $this->containerDependencies[$name];
    }
}
