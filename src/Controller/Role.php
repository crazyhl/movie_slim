<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-03-09
 * Time: 14:50
 */

namespace App\Controller;


use Slim\Http\Request;
use Slim\Http\Response;

class Role extends BaseController
{
    /**
     * 获取用户信息
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function info(Request $request, Response $response)
    {
        return $response->withJson([
            'status' => 0,
            'message' => '',
            'data' => 'asdf',
        ]);
    }
}
