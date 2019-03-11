<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-02-23
 * Time: 13:52
 */

namespace App\Utils;


use Jose\Component\Core\Converter\StandardConverter;
use Jose\Component\Core\JWK;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\JWSLoader;
use Psr\Container\ContainerInterface;

class JWT
{
    /**
     * @var ContainerInterface $container
     */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * 序列化 token
     * @param array $payload
     * @return mixed
     */
    public function encode(array $payload = [])
    {
        $basePayload = [
            'iat' => time(),
            'nbf' => time(),
            'exp' => time() + $this->container->get('jwtExp'),
            'iss' => $this->container->get('jwtIss'),
            'aud' => $this->container->get('jwtAud'),
        ];
        $payload = array_merge($basePayload, $payload);

        /**
         * @var StandardConverter $jsonConverter
         */
        $jsonConverter = $this->container->get('jsonConverter');

        $encodePayload = $jsonConverter->encode($payload);

        /**
         * @var JWK $jwk
         */
        $jwk = $this->container->get('jwk');

        /**
         * @var JWSBuilder $jwsBuilder
         */
        $jwsBuilder = $this->container->get('jwsBuilder');

        $jws = $jwsBuilder->create()->withPayload($encodePayload)
            ->addSignature($jwk, ['alg' => 'HS256'])->build();

        $serializerManager = $this->container->get('serializerManager'); //new CompactSerializer($jsonConverter);

        $token = $serializerManager->serialize('jws_compact', $jws, 0);

        return $token;
    }

    /**
     * 反序列化token
     * @param $token
     * @return \Jose\Component\Signature\JWS
     * @throws \Exception
     */
    public function decode($token)
    {
        /**
         * @var JWSLoader $jwsLoader
         */
        $jwsLoader = $this->container->get('jwsLoader');
        /**
         * @var JWK $jwk
         */
        $jwk = $this->container->get('jwk');
        $signature = 0;

        try {
            $jws = $jwsLoader->loadAndVerifyWithKey($token, $jwk, $signature);
            return $jws;
        } catch (\Exception $e) {
//            var_dump($e->getMessage());
            throw $e;
        }
    }
}
