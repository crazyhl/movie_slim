<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-02-12
 * Time: 12:34
 */
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/bootstrap.php';

use Symfony\Component\Console\Application;
use App\Command\TestCommand;

$application = new Application();
// TODO 这块等待注册各种 Command
$application->add(new TestCommand($app->getContainer()));
$application->run();