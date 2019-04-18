<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-02-13
 * Time: 15:45
 */

namespace App\Controller;


use Carbon\Carbon;
use Slim\Http\Request;
use Slim\Http\Response;

class TestController extends BaseController
{
    public function test(Request $request, Response $response, $args)
    {
        // Sample log message
//        $this->logger->info("Slim-Skeleton '/' route");
        $this->container->logger->info('$args: ' . json_encode($args));

        Carbon::now()->startOfDay();
        // Render index view
        return $response->getBody()->write('home ' . $args['name']);
    }
}
