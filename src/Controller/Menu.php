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
        $parentId = $args['parentId'] ?: 0;
        $this->container->logger->info('$parentId: ' . $parentId);

        $roleList = MenuModel::where('parent', '=', $parentId)->paginate(20);

        // 获取一下当前菜单的信息，用于生成面包屑导航
        $menuItemArr = [];
        if ($parentId > 0) {
            $menuItem = MenuModel::find($parentId);
            if ($menuItem) {
                $menuItemArr[] = $menuItem;
                $menuParentId = $menuItem->parent;
                while ($menuParentId) {
                    $loopMenuItem = MenuModel::find($menuParentId);
                    $menuParentId = $loopMenuItem->parent;
                    if ($loopMenuItem) {
                        array_unshift($menuItemArr, $loopMenuItem);
                    }
                }
            }
        }

        $dataList = [];
        $dataList['roleList'] = $roleList;
        $dataList['menuItem'] = $menuItemArr;

        return $response->withJson([
            'status' => 0,
            'message' => '',
            'data' => $dataList,
        ]);
    }
}
