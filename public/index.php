<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}
mb_internal_encoding("utf-8");
require __DIR__ . '/../vendor/autoload.php';

//session_start();


// Instantiate the app
$settings = require __DIR__ . '/../config/settings.php';
// 时区
ini_set('date.timezone', $settings['timezone']);
$app = new \Slim\App($settings);

// Set up dependencies
require __DIR__ . '/../config/dependencies.php';

// Register middleware
require __DIR__ . '/../config/middleware.php';

// Register routes
require __DIR__ . '/../route/routes.php';

// Run app
$app->run();
