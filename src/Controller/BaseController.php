<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-02-13
 * Time: 15:38
 */

namespace App\Controller;


use Psr\Container\ContainerInterface;

class BaseController
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }
}
