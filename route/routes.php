<?php

use Slim\Http\Request;
use Slim\Http\Response;

// Routes
$app->get('/[{name}]', \App\Controller\TestController::class . ':test' );

