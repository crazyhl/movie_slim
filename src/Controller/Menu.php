<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-03-09
 * Time: 14:50
 */

namespace App\Controller;


use Slim\Http\Request;
use Slim\Http\Response;
use App\Model\Menu as MenuModel;

class Menu extends BaseController
{
    /**
     * 获取菜单列表
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function lists(Request $request, Response $response, $args)
    {
        $parentId  = $args['parentId'] ?: 0;
        $this->container->logger->info('$parentId: ' . $parentId);

        $roleList = MenuModel::where('parent', '=', $parentId)->paginate(20);

        return $response->withJson([
            'status' => 0,
            'message' => '',
            'data' => $roleList,
        ]);
    }
}
