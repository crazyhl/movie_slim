<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes
$app->post('/login', \App\Controller\User::class . ':login');

$app->get('/[{name}]', \App\Controller\TestController::class . ':test');

