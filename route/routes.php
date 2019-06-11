<?php

// Routes
// 如果他要登录，就无限的重复登录好了
$app->post('/login', \App\Controller\User::class . ':login')->setName('login');
$app->get('/test', \App\Controller\TestController::class . ':test')->setName('testGet')->add(new \App\Middleware\CheckUrl($app->getContainer()));

$app->get('/test1[/{field1}[/{field2}]]', \App\Controller\TestController::class . ':test')
    ->setName('test1')
    ->add(new \App\Middleware\CheckUrl($app->getContainer()))
    ->add(new \App\Middleware\ValidateMiddleware($app->getContainer(), new \App\Validator\TestValidator3()));
$app->post('/testpost', \App\Controller\TestController::class . ':test')
    ->setName('testPost')
    ->add(new \App\Middleware\CheckUrl($app->getContainer()))
    ->add(new \App\Middleware\ValidateMiddleware($app->getContainer(), new \App\Validator\TestValidator2()));

$app->group('', function () use ($app) {
    // 登出
    $app->post('/logout', \App\Controller\User::class . ':logout')
        ->setName('logout');
    // 用户信息
    $app->get('/userInfo', \App\Controller\User::class . ':info')
        ->setName('userInfo');
    // 获取菜单
    $app->get('/getMenu/{position}', \App\Controller\Menu::class . ':getUserMenu')
        ->setName('getUserMenu');
    // 后台管理相关
    $app->group('/admin', function () use ($app) {
        // 角色相关
        $app->group('/role', function () use ($app) {
            $app->get('', \App\Controller\Role::class . ':lists')
                ->setName('adminRoleList');
        })->add(new \App\Middleware\CheckRole($app->getContainer()));
        // 菜单相关
        $app->group('/menu', function () use ($app) {
            $app->get('', \App\Controller\Menu::class . ':lists')
                ->setName('adminMenuList');
            $app->get('/treeList', \App\Controller\Menu::class . ':treeList')
                ->setName('adminMenuTreeList');
            $app->post('/add', \App\Controller\Menu::class . ':add')
                ->setName('adminMenuAdd')
                ->add(new \App\Middleware\ValidateMiddleware($app->getContainer(), new \App\Validator\AddMenuValidator()));
            $app->get('/{id}', \App\Controller\Menu::class . ':get')
                ->setName('adminMenuGetById');
        });
    });
// 这里面的所有请求都会检测是否未登录
})->add(new \App\Middleware\CheckLogin($app->getContainer()))
    ->add(new \App\Middleware\CheckUrl($app->getContainer()));



