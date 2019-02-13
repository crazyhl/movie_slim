<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-02-13
 * Time: 15:45
 */

namespace App\Controller;


use Slim\Http\Request;
use Slim\Http\Response;

class TestController extends BaseController
{
    public function test(Request $request, Response $response)
    {
        return $response->getBody()->write('啦啦啦 controller 成了');
    }
}
