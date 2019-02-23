<?php

$redisKeyConfig = require __DIR__ . '/redisKey.php';

return [
    'redisKey' => $redisKeyConfig,
    'jwtSignatureKey' => 'jv8WZosvAQPrO0wAoFM7RmzX2uvcBDDuPqcJOJuh3ZzlRvmpNrOSuB91nvOVJUhqu_ZILExKF2WmrjDI5CuVumBB4oCUFiFDt01u7yPGYsRyJhmC7WzyoS4T0DfnmphRrAVmkmk9xsk-nKT46L0uRBueC1D4i9qUaGvfGwiDRa4', // jwt 签名key , 这个key 使用 jwt:generateOctString 生成
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
            'username' => 'root',
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
];
