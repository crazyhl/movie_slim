<?php

// Routes
// 如果他要登录，就无限的重复登录好了
$app->post('/login', \App\Controller\User::class . ':login');
//$app->get('/test', \App\Controller\TestController::class . ':test')->add(new \App\Middleware\CheckUrl($app->getContainer()));
//$app->get('/testaddf/{id}', \App\Controller\TestController::class . ':test')->add(new \App\Middleware\CheckUrl($app->getContainer()));
//$app->get('/testasdf/{name}/{id}/{age}', \App\Controller\TestController::class . ':test')->add(new \App\Middleware\CheckUrl($app->getContainer()));
//$app->get('/', \App\Controller\Menu::class . ':treeList');

$app->group('', function () use ($app) {
    // 登出
    $app->post('/logout', \App\Controller\User::class . ':logout');
    // 用户信息
    $app->get('/userInfo', \App\Controller\User::class . ':info');
    // 获取菜单
    $app->get('/getMenu/{position}', \App\Controller\Menu::class . ':getUserMenu');
    // 后台管理相关
    $app->group('/admin', function () use ($app) {
        // 角色相关
        $app->group('/role', function () use ($app) {
            $app->get('', \App\Controller\Role::class . ':lists');
        })->add(new \App\Middleware\CheckRole($app->getContainer()));
        // 菜单相关
        $app->group('/menu', function () use ($app) {
            $app->get('/treeList', \App\Controller\Menu::class . ':treeList');
            $app->get('', \App\Controller\Menu::class . ':lists');
        });
    });
// 这里面的所有请求都会检测是否未登录
})->add(new \App\Middleware\CheckLogin($app->getContainer()))
    ->add(new \App\Middleware\CheckUrl($app->getContainer()));



