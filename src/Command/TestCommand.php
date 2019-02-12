<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-02-12
 * Time: 15:12
 */

namespace App\Command;


use Illuminate\Database\Capsule\Manager;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'test:slim-command';
    private $container;

    public function __construct(ContainerInterface $container, $name = null)
    {
        parent::__construct($name);
        $this->container = $container;
    }

    protected function configure()
    {
        $this->setDescription('测试 slim 和 symfony console 结合情况');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // ...
        $output->writeln([
            'symfony console 的输出跑起来了',
        ]);

        /**
         * @var Manager
         */
        $db = $this->container->get('db');
        $result = $db->table('user')->get();
        var_dump($result);

        $logger = $this->container->get('logger');
        $logger->info("slim 的日志也跑起来了");
    }
}
