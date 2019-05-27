<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-03-09
 * Time: 14:50
 */

namespace App\Controller;


use Psr\Http\Message\RequestInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Model\Menu as MenuModel;

class Menu extends BaseController
{
    /**
     * 获取用户菜单
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function getUserMenu(Request $request, Response $response, $args)
    {
        $position = $args['position'];
        $user = $request->getAttribute('user');
        $rolePermissions = $user->getRolePermissions();
        // 得到了所有的权限id
        $rolePermissionIds = array_column($rolePermissions, 'id');
        $rolePermissionSlugs = array_column($rolePermissions, 'slug');
        $isAdmin = false;
        if (in_array('admin', $rolePermissionSlugs)) {
            $isAdmin = true;
        }
        // 根据 position 获取菜单
        $menus = MenuModel::where('position', '=', $position)
            ->where('is_open', '=', '1')
            ->where('is_show', '=', '1')
            ->with('permissions')
            ->orderBy('parent', 'ASC')
            ->orderBy('order', 'DESC')
            ->get();

        $userMenus = [];
        foreach ($menus as $menu) {
            $addToUser = false;
            if (!$isAdmin && $menu->permissions) {
                foreach ($menu->permissions as $menuPermission) {
                    if (in_array($menuPermission, $rolePermissionIds)) {
                        $addToUser = true;
                        break;
                    }
                }
            } else {
                $addToUser = true;
            }

            if ($addToUser) {
                $menuArray = $menu->toArray();
                unset($menuArray['permissions']);
                unset($menuArray['updated_at']);
                unset($menuArray['created_at']);
                unset($menuArray['position']);
                unset($menuArray['is_open']);
                unset($menuArray['parent']);
                unset($menuArray['order']);
                if ($menu->parent == 0) {
                    $menuArray['children'] = [];
                    $userMenus[$menu->id] = $menuArray;
                } else {
                    if ($userMenus[$menu->parent]) {
                        $userMenus[$menu->parent]['children'][] = $menuArray;
                    }
                }
            }
        }

        return $response->withJson([
            'status' => 0,
            'message' => '',
            'data' => array_values($userMenus),
        ]);
    }

    /**
     * 获取菜单列表
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function lists(Request $request, Response $response)
    {
        $where = $this->generateGetMenuListWhereParams($request);

        $menuList = MenuModel::where($where)
            ->orderBy('order', 'ASC')
            ->paginate(20);

        // 获取一下当前菜单的信息，用于生成面包屑导航
        $menuItemArr = [];
        if ($where['parent'] > 0) {
            $menuItem = MenuModel::find($where['parent']);
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
        $dataList['menuList'] = $menuList;
        $dataList['menuItem'] = $menuItemArr;

        return $response->withJson([
            'status' => 0,
            'message' => '',
            'data' => $dataList,
        ]);
    }

    /**
     * 树形菜单列表
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function treeList(Request $request, Response $response)
    {
        $where = $this->generateGetMenuListWhereParams($request);
        $menuList = MenuModel::where($where)
            ->orderBy('parent', 'DESC')
            ->orderBy('order', 'ASC')
            ->get();

        $menuList = $menuList->toArray();
        $menuIdArr = array_column($menuList, 'id');

        foreach ($menuList as $key => $menuItem) {
            $this->logger->info(array_search($menuItem['parent'], $menuIdArr));
            if (array_search($menuItem['parent'], $menuIdArr) !== false) {
                $menuList[array_search($menuItem['parent'], $menuIdArr)]['children'][] = $menuList[$key];
            }
        }

        $dataList = [];
        foreach ($menuList as $item) {
            if ($item['parent'] == 0) {
                $dataList[] = $item;
            }
        }

        $dataList = $this->treeMenuToListMenu($dataList);

        return $response->withJson([
            'status' => 0,
            'message' => '',
            'data' => $dataList,
        ]);
    }

    public function add(Request $request, Response $response) {
        $responseArr = [
            'status' => 0,
            'message' => '',
            'data' => [],
        ];
        // 利用 slug 校验是否存在
        $slug = $request->getParsedBodyParam('slug');
        $existMenu = MenuModel::where('slug', '=', $slug)->first();
        // 已存在就报错
        if ($existMenu) {
            $responseArr['status'] = -2;
            $responseArr['message'] = '菜单已存在';
            return $response->withJson($responseArr);
        }

        // 不存在的情况下在添加
        $menu = new MenuModel();
        $menu->name = $request->getParsedBodyParam('name');
        $menu->slug = $slug;
        $menu->description = $request->getParsedBodyParam('description');
        $menu->icon = $request->getParsedBodyParam('icon', '');
        $menu->url = $request->getParsedBodyParam('url', '');
        $menu->order = $request->getParsedBodyParam('order');
        $menu->parent = $request->getParsedBodyParam('parentId');
        $menu->is_open = $request->getParsedBodyParam('is_open');
        $menu->is_show = $request->getParsedBodyParam('is_show');
        $menu->position = $request->getParsedBodyParam('position');
        $result = $menu->save();


        if ($result) {
            $responseArr['data'] = $menu;
        } else {
            $responseArr['status'] = -1;
            $responseArr['message'] = '添加失败';
        }

        return $response->withJson($responseArr);
    }


    // ------------------------- private function ---------------------------------------

    /**
     * 获取生成菜单条件
     * @param Request $request
     * @return array
     */
    private function generateGetMenuListWhereParams(Request $request)
    {
        $parentId = $request->getQueryParam('parentId', null); //$args['parentId'] ?: null;
        $isModelHiddenBind = $request->getQueryParam('isModelHiddenBind', 0); //$args['isModelHiddenBind'] ?: null;
        $modelType = $request->getQueryParam('modelType', null); //$args['modelType'] ?: null;
        $modelId = $request->getQueryParam('modelId', null); //$args['modelId'] ?: 0;

        // 构造 where
        $where = [];
        if ($parentId !== null) {
            $where[] = ['parent', '=', $parentId];
        }
        $where[] = ['is_model_hidden_bind', '=', $isModelHiddenBind];
        if ($modelType !== null) {
            $where[] = ['model_type', '=', $modelType];
            $where[] = ['model_id', '=', $modelId];
        }

        return $where;
    }

    /**
     * 转成树形菜单
     * @param $menuList
     * @param int $deep
     * @return array
     */
    private function treeMenuToListMenu($menuList, $deep = 0)
    {
        static $returnMenuList = [];
        if (empty($menuList)) {
            return $returnMenuList;
        }
        $this->logger->info('$menuList:' . $deep . ':' . json_encode($menuList));
        foreach ($menuList as $item) {
            $name = $item['name'];
            $position = $item['position'];
            $positionName = ($position == 1) ? '[后]' : '[前]';

            $name = $positionName . $name;
            if ($deep > 0) {
                $name = str_pad('└'
                        , strlen('└') - mb_strlen('└', 'UTF-8') + $deep * 3 + 1
                        , '　', STR_PAD_LEFT)
                    . $name;
            }
            $returnMenuList[] = [
                'id' => $item['id'],
                'name' => $name,
                'position' => $position,
            ];
            if ($item['children']) {
                $this->treeMenuToListMenu($item['children'], $deep + 1);
            }
        }
        return $returnMenuList;
    }
}
