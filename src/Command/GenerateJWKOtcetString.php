<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-02-12
 * Time: 15:12
 */

namespace App\Command;

use Jose\Component\KeyManagement\JWKFactory;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateJWKOtcetString extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'jwt:generateOctString';
    private $container;

    public function __construct(ContainerInterface $container, $name = null)
    {
        parent::__construct($name);
        $this->container = $container;
    }

    protected function configure()
    {
        $this->setDescription('生成 jwk otc 签名 string');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $key = JWKFactory::createOctKey(
            1024, // Size in bits of the key. We recommend at least 128 bits.
            [
                'alg' => 'HS256', // This key must only be used with the HS256 algorithm
                'use' => 'sig'    // This key is used for signature/verification operations only
            ]
        );
        // ...
        $output->writeln([
            '生成的key是：' . $key->get('k'),
        ]);
    }
}
