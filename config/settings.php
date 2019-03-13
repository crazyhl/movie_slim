<?php

$redisKeyConfig = require __DIR__ . '/redisKey.php';

return [
    'redisKey' => $redisKeyConfig,
    'jwtSignatureKey' => '_avTzfR0fatqKFdsv2SQkWUbuUPMXuBoGvuQNKZigTvCktCMkZ3S4626BiiJlVr9ldDbkPaJZsp_0q3vN5pXsHWz6BvX961arIHmfziwxiRyqBVTBRGiP-9t6iDfS2wpv3bXqqS7NwYUiaQzUrKm6jYjPKJ7LNpKBzKoiBbFcjs', // jwt 签名key , 这个key 使用 jwt:generateOctString 生成
    'jwtIss' => 'yourJwtIss', // jwt iss
    'jwtAud' => 'youreJwtAud', // jwt aud
    'jwtExp' => 3600, // jwt 默认有效期
    'defaultCoverDir' => '/public/static/image/', // 图片默认存储的相对路径
    'timezone' => 'Asia/Shanghai',
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
//        'renderer' => [
//            'template_path' => __DIR__ . '/../templates/',
//        ],
        // 数据库 illuminate/database
        'db' => [
            'driver' => 'mysql',
            'host' => 'mysql',
            'port' => '3306',
            'database' => 'movie',
            'username' => 'crazyhl',
            'password' => '123456789',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
            'prefix' => '',
        ],
        // redis
        'redis' => [
            'host' => 'redis',
            'port' => 6379,
            'timeout' => '300',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],
    ],
    'cors' => [
        "origin" => ["http://192.168.50.95:8080", "http://localhost:8080"],
        "methods" => ["GET", "POST", "PUT", "PATCH", "DELETE", "OPTIONS"],
        "headers.allow" => ['Access-Control-Request-Headers', 'Access-Control-Request-Method', 'Authorization'],
        "headers.expose" => ['JWT-Token'],
        "credentials" => true,
        "cache" => 0,
        "error" => function ($request, $response, $arguments) {
            $data["status"] = "error";
            $data["message"] = $arguments["message"];
            return $response
                ->withHeader("Content-Type", "application/json")
                ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        }
    ],
];
