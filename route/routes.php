<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes
// 如果他要登录，就无限的重复登录好了
$app->post('/login', \App\Controller\User::class . ':login');

$app->group('', function () use ($app) {
    $app->post('/logout', \App\Controller\User::class . ':logout');
    $this->get('/asdf', \App\Controller\TestController::class . ':test') ;
    $this->get('/asdf123', \App\Controller\TestController::class . ':test') ;

// 这里面的所有请求都会检测是否未登录
})->add(new \App\Middleware\CheckLogin($app->getContainer()));
