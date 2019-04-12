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
 * 检测菜单权限的中间件
 * Class CheckUrl
 * @package App\Middleware
 */
class CheckUrl
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
        $this->container->logger->info('path: ' . $request->getUri()->getPath());
        // 如果url为空或者 need_permissions 为空就可以直接过，否则就得查权限了
        $response = $next($request, $response);

        return $response;
    }
}
