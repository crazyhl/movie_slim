<?php
return [
    'settings' => [
        'displayErrorDetails' => true, // set to false in production
        'addContentLengthHeader' => false, // Allow the web server to send the content-length header

        // Renderer settings
//        'renderer' => [
//            'template_path' => __DIR__ . '/../templates/',
//        ],
        // 数据库 illuminate/database
        'db' => [
            'driver'    => 'mysql',
            'host'      => 'mysql',
            'port'      => '3306',
            'database'  => 'movie',
            'username'  => 'root',
            'password'  => '123456789',
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_general_ci',
            'prefix'    => '',
        ],
        // redis
        'redis' => [
            'host'      => 'redis',
            'port'      => 6379,
            'timeout'  => '300',
        ],

        // Monolog settings
        'logger' => [
            'name' => 'slim-app',
            'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
            'level' => \Monolog\Logger::DEBUG,
        ],
    ],
];
