<?php
// DIC configuration

use Jose\Component\Checker\AlgorithmChecker;
use Jose\Component\Checker\HeaderCheckerManager;
use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Core\Converter\StandardConverter;
use Jose\Component\Core\JWK;
use Jose\Component\Signature\Algorithm\HS256;
use Jose\Component\Signature\JWSBuilder;
use Jose\Component\Signature\JWSLoader;
use Jose\Component\Signature\JWSTokenSupport;
use Jose\Component\Signature\JWSVerifier;
use Jose\Component\Signature\Serializer\CompactSerializer;
use Jose\Component\Signature\Serializer\JWSSerializerManager;
use Psr\Container\ContainerInterface;
use App\Utils\JWT;

$container = $app->getContainer();

// view renderer
//$container['renderer'] = function ($c) {
//    $settings = $c->get('settings')['renderer'];
//    return new Slim\Views\PhpRenderer($settings['template_path']);
//};

// monolog
$container['logger'] = function (ContainerInterface $c) {
    $settings = $c->get('settings')['logger'];
    $logger = new Monolog\Logger($settings['name']);
    $logger->pushProcessor(new Monolog\Processor\UidProcessor());
    $logger->pushHandler(new Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
    return $logger;
};
// redis
$container['redis'] = function (ContainerInterface $container) {
    $redis = new Redis();
    $redis->connect($container['settings']['redis']['host'], $container['settings']['redis']['port']);
    return $redis;
};

// illuminate/database
$container['db'] = function (ContainerInterface $container) {
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($container['settings']['db']);

    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
};

// jwt相关
$container['jsonConverter'] = function (ContainerInterface $container) {
    $jsonConverter = new StandardConverter();

    return $jsonConverter;
};

$container['jwk'] = function (ContainerInterface $container) {
    $jwtSignatureKey = $container->get('jwtSignatureKey');
    $jwk = JWK::create([
        'kty' => 'oct',
        'k' => $jwtSignatureKey,
    ]);

    return $jwk;
};

$container['algorithmManager'] = function (ContainerInterface $container) {
    $algorithmManager = AlgorithmManager::create([
        new HS256(),
    ]);

    return $algorithmManager;
};

$container['jwsVerifier'] = function (ContainerInterface $container) {
    $algorithmManager = $container->get('algorithmManager');

    $jwsVerifier = new JWSVerifier(
        $algorithmManager
    );

    return $jwsVerifier;
};

$container['serializerManager'] = function (ContainerInterface $container) {
    $jsonConverter = $container->get('jsonConverter');

    $serializerManager = JWSSerializerManager::create([
        new CompactSerializer($jsonConverter),
    ]);

    return $serializerManager;
};

$container['headerCheckerManager'] = function (ContainerInterface $container) {
    $headerCheckerManager = HeaderCheckerManager::create(
        [
            new AlgorithmChecker(['HS256']), // We check the header "alg" (algorithm)
        ],
        [
            new JWSTokenSupport(), // Adds JWS token type support
        ]
    );

    return $headerCheckerManager;
};

$container['jwsLoader'] = function (ContainerInterface $container) {
    $serializerManager = $container->get('serializerManager');
    $jwsVerifier = $container->get('jwsVerifier');
    $headerCheckerManager = $container->get('headerCheckerManager');

    $jwsLoader = new JWSLoader(
        $serializerManager,
        $jwsVerifier,
        $headerCheckerManager
    );

    return $jwsLoader;
};

$container['jwsBuilder'] = function (ContainerInterface $container) {
    $algorithmManager = $container->get('algorithmManager');

    $jsonConverter = $container->get('jsonConverter');

    $jwsBuilder = new JWSBuilder($jsonConverter, $algorithmManager);

    return $jwsBuilder;
};

$container['utils'] = function (ContainerInterface $container) {
    $utils = new JWT($container);

    return $utils;
};
