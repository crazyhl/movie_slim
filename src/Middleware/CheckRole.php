<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-03-13
 * Time: 15:20
 */

namespace App\Middleware;


use App\Controller\User;
use App\Utils\JWT;
use Jose\Component\Core\Converter\StandardConverter;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Model\User as UserModel;

/**
 * 角色控制器相关的中间件
 * Class CheckUrl
 * @package App\Middleware
 */
class CheckRole
{
    /**
     * @var Container $container ;
     */
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
        // 初始化数据库连接
        $this->container->get('db');
    }


    /**
     * @param Request $request
     * @param Response $response
     * @param $next
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        $response = $next($request, $response);

        return $response;
    }
}
