<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);
$app->add(new Tuupola\Middleware\CorsMiddleware([
    "origin" => ["http://192.168.50.95:8080", "http://localhost:8080"],
    "methods" => ["GET", "POST", "PUT", "PATCH", "DELETE"],
    "headers.allow" => [],
    "headers.expose" => [],
    "credentials" => true,
    "cache" => 0,
]));
