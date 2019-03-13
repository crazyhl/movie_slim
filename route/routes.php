<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes

$app->group('', function () use ($app) {
    $this->get('/asdf', \App\Controller\TestController::class . ':test') ;
    $this->get('/asdf123', \App\Controller\TestController::class . ':test') ;
    $app->post('/login', \App\Controller\User::class . ':login');

})->add(new \App\Middleware\CheckLogin($app->getContainer()));


//$app->get('/[{name}]', \App\Controller\TestController::class . ':test');

