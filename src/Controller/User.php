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
use App\Model\User as UserModel;

class User extends BaseController
{
    /**
     * 用户登录
     * @param Request $request
     * @param Response $response
     * @return int
     */
    public function login(Request $request, Response $response) {
        $postParams = $request->getParsedBody();
        $user = UserModel::where('username', $postParams['username'])->first();

        if (empty($user)) {
            // 用户不存在
            return $this->renderJson($response, [
                'status' => -1,
                'message' => '用户不存在'
            ]);
        }

        $password = $postParams['password'];
        $passwordVerify  = password_verify($password, $user->getAuthPassword());

        if ($passwordVerify) {
            // 验证通过了
            if (password_needs_rehash($user->getAuthPassword(), PASSWORD_DEFAULT)) {
                // 判断是否需要 rehash
                $user->password = $password;
                $user->save();
            }
            // 登录成功
            return $this->renderJson($response, [
                'status' => 0,
                'message' => '登录成功'
            ]);
        } else {
            // 没验证通过
            return $this->renderJson($response, [
                'status' => -2,
                'message' => '密码错误'
            ]);
        }
    }
}
