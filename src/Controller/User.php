<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-03-09
 * Time: 14:50
 */

namespace App\Controller;


use App\Utils\JWT;
use Slim\Http\Cookies;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Model\User as UserModel;

class User extends BaseController
{
    /**
     * 用户登录
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function login(Request $request, Response $response)
    {
        $postParams = $request->getParsedBody();
        /**
         * @var $user \App\Model\User
         */
        $user = UserModel::where('username', $postParams['username'])->first();

        if (empty($user)) {
            // 用户不存在
            return $response->withJson([
                'status' => -1,
                'message' => '用户不存在',
                'data' => '',
            ]);
        }

        $password = $postParams['password'];
        $passwordVerify = password_verify($password, $user->getAuthPassword());

        if ($passwordVerify) {
            // 验证通过了
            if (password_needs_rehash($user->getAuthPassword(), PASSWORD_DEFAULT)) {
                // 判断是否需要 rehash
                $user->password = $password;
                $user->save();
            }

            $jwtUtil = new JWT($this->container);
            $jwtToken = $jwtUtil->encode(['uid' => $user->id]);
            // redis 保存一份 token 到 reids 中，
            $redis = $this->container->redis;
            $redis->set($this->container->get('redisKey')['jwtUserToken'] . $user->id, $jwtToken);

            // 登录成功
            return $response->withJson([
                'status' => 0,
                'message' => '登录成功',
                'data' => '',
            ])->withHeader('JWT-Token', $jwtToken);
        } else {
            // 没验证通过
            return $response->withJson([
                'status' => -2,
                'message' => '密码错误',
                'data' => '',
            ]);
        }
    }

    public function logout(Request $request, Response $response)
    {
        $user = $request->getAttribute('user');
        $uid = $request->getAttribute('uid');
        $this->container->logger->info("uid: " . $uid);
        /**
         * @var \Redis $redis
         */
        $redis = $this->container->redis;
        // 清除redis
        $redis->delete($this->container->get('redisKey')['jwtUserToken'] . $uid);

        return $response->withoutHeader('JWT-Token')->withJson([
            'status' => 0,
            'message' => '登出成功',
            'data' => '',
        ]);
    }
}
