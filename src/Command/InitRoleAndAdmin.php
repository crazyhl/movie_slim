<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-03-05
 * Time: 18:20
 */

namespace App\Command;


use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitRoleAndAdmin extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'admin:init';
    private $container;

    public function __construct(ContainerInterface $container, $name = null)
    {
        parent::__construct($name);
        $this->container = $container;
    }

    protected function configure()
    {
        $this->setDescription('初始化 管理员角色权限 以及 管理员账号')
            ->addOption('password', null, InputArgument::OPTIONAL, 'admin user password', '123456789');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

    }
}
