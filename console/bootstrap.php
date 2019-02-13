<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-02-12
 * Time: 14:01
 */
if (PHP_SAPI == 'cli') {
    // Instantiate the app
    $settings = require __DIR__ . '/../config/settings.php';

    //I try adding here path_info but this is wrong, I'm sure
//    $settings['environment'] = $env;

    $app = new \Slim\App($settings);

    $container = $app->getContainer();

    // Set up dependencies
    require __DIR__ . '/../config/dependencies.php';
}
