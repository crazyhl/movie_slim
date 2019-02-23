<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-02-12
 * Time: 12:34
 */
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/bootstrap.php';

use App\Command\GenerateJWKOtcetString;
use Symfony\Component\Console\Application;
use App\Command\TestCommand;
use App\Command\CreateDatabase;
use App\Command\AddTestData;
use App\Command\CrawlTask;
use App\Command\DownloadCover;

$application = new Application();
// TODO 这块等待注册各种 Command
$application->add(new TestCommand($app->getContainer()));
$application->add(new CreateDatabase($app->getContainer()));
$application->add(new AddTestData($app->getContainer()));
$application->add(new CrawlTask($app->getContainer()));
$application->add(new DownloadCover($app->getContainer()));
$application->add(new GenerateJWKOtcetString($app->getContainer()));
$application->run();
