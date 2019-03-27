<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-03-13
 * Time: 15:20
 */

namespace App\Middleware;


use App\Controller\User;
use App\Utils\JWT;
use Jose\Component\Core\Converter\StandardConverter;
use Slim\Container;
use Slim\Http\Request;
use Slim\Http\Response;
use App\Model\User as UserModel;

class CheckLogin
{
    /**
     * @var Container $container ;
     */
    private $container;

    public function __construct($container)
    {
        $this->container = $container;
        // 初始化数据库连接
        $this->container->get('db');
    }


    /**
     * @param Request $request
     * @param Response $response
     * @param $next
     * @return Response
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        $authorization = $request->getHeader('Authorization')[0];
        if (empty($authorization)) {
            // 没有 jwt header
            $this->container->logger->info('$authorization: ' . $authorization);
            $response = $response->withJson([
                'status' => -10000,
                'message' => '请登陆后访问',
                'data' => '',
            ]);
        } else if (strpos($authorization, 'JWT ') !== 0) {
            // 没有以 JWT 开头
            // 没有 jwt header
            $response = $response->withJson([
                'status' => -10001,
                'message' => '非法请求',
                'data' => '',
            ]);
        } else {
            /**
             * @var \Redis $redis
             */
            $redis = $this->container->redis;

            // 这时候就要解密了
            $jwtToken = explode(' ', $authorization, 2)[1];
            $jwtUtils = new JWT($this->container);
            $jwtInfo = $jwtUtils->decode($jwtToken);
            // 获取 payload
            $jwtPayloadString = $jwtInfo->getPayload();

            /**
             * @var StandardConverter $jsonConverter
             */
            $jsonConverter = $this->container->get('jsonConverter');
            // 解密一下
            $payLoadArr = $jsonConverter->decode($jwtPayloadString);
            // 获取相关字段
            $exp = $payLoadArr['exp'];
            $uid = $payLoadArr['uid'];

            $redisUserTokenkey = $this->container->get('redisKey')['jwtUserToken'] . $uid;

            // 如果过期时间或者uid有一个是空，就说明数据有问题，重新登录就好了
            if (empty($exp) || empty($uid)) {
                $response = $response->withJson([
                    'status' => -10002,
                    'message' => '登录信息无效，请重新登录',
                    'data' => '',
                ]);
                $redis->delete($redisUserTokenkey);
            } else if ($exp < time()) {
                // exp 超时了
                $response = $response->withJson([
                    'status' => -10003,
                    'message' => '登录信息失效，请重新登录',
                    'data' => '',
                ]);
                $redis->delete($redisUserTokenkey);
            } else {
                // 从redis中获取token
                $redisToken = $redis->get($this->container->get('redisKey')['jwtUserToken'] . $uid);
                if (empty($redisToken)) {
                    // 如果没有就说token是伪造的
                    $response = $response->withJson([
                        'status' => -10004,
                        'message' => '登录信息失效，请重新登录',
                        'data' => '',
                    ]);
                    $redis->delete($redisUserTokenkey);
                } else if ($redisToken !== $jwtToken) {
                    // 传递过来的token 跟redis 的不同，就说明可能是被拦截了
                    // 如果没有就说token是伪造的
                    $response = $response->withJson([
                        'status' => -10005,
                        'message' => '登录信息失效，请重新登录',
                        'data' => '',
                    ]);
                    $redis->delete($redisUserTokenkey);
                } else {
                    // 如果都过了，就可以在header里面加上uid 和 用户信息，让后续的请求使用了
                    $user = UserModel::where('id', $uid)->first();
                    // 需要注意的是，这里返回的是一个新的request, withHeader
                    // 返回的是一个 clone 对象
                    $newRequest = $request->withAttribute('uid', $uid)->withAttribute('user', $user);
                    // 重置token
                    $jwtUtil = new JWT($this->container);
                    $jwtToken = $jwtUtil->encode(['uid' => $uid]);
                    // redis 保存一份 token 到 reids 中，
                    $redis->set($this->container->get('redisKey')['jwtUserToken'] . $uid, $jwtToken);
                    // 重新放到header中
                    $newResponse = $response->withHeader('JWT-Token', $jwtToken);
                    $response = $next($newRequest, $newResponse);
                }
            }
        }


        return $response;
    }
}
