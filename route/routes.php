<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes
// 如果他要登录，就无限的重复登录好了
$app->post('/login', \App\Controller\User::class . ':login');

$app->group('', function () use ($app) {
    $app->post('/logout', \App\Controller\User::class . ':logout');
    $app->group('/admin', function () use ($app) {
        $this->get('/userInfo', \App\Controller\User::class . ':info') ;
        $this->get('/getMenu/{position}', \App\Controller\User::class . ':getMenu') ;
    });
// 这里面的所有请求都会检测是否未登录
})->add(new \App\Middleware\CheckLogin($app->getContainer()));
