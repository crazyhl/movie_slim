<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-03-05
 * Time: 18:20
 */

namespace App\Command;


use App\Model\Menu;
use App\Model\Permission;
use App\Model\Role;
use App\Model\User;
use Carbon\Carbon;
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
        $permission = new Permission();
        $permission->name = '超级管理员';
        $permission->slug = 'admin';
        $permission->description = '默认的超级管理员';
        $permission->save();
        $output->writeln('权限创建完毕');


        $role = new Role();
        $role->name = '超级管理员';
        $role->slug = 'admin';
        $role->description = '默认的超级管理员';
        $role->save();
        $role->permissions()->attach($permission);
        $output->writeln('角色创建完毕');


        $user = new User();
        $user->username = 'admin';
        $user->password = $input->getOption('password');
        $user->save();
        $user->roles()->attach($role);
        $user->permissions()->attach($permission);

        $output->writeln('管理员创建完毕');

        $menu = new Menu();
        $menu->name = '系统设置';
        $menu->description = '设置页面';
        $menu->url = '/admin/settings';
        $menu->position = 1;
        $menu->is_open = 1;
        $menu->save();

        $menu->permissions()->attach($permission);

        $menu = new Menu();
        $menu->name = '菜单管理';
        $menu->description = '菜单管理';
        $menu->url = '/admin/menu';
        $menu->position = 1;
        $menu->is_open = 1;
        $menu->save();

        $menu->permissions()->attach($permission);

        $menu = new Menu();
        $menu->name = '权限管理';
        $menu->description = '权限管理';
        $menu->url = '/admin/permissions';
        $menu->position = 1;
        $menu->is_open = 1;
        $menu->save();

        $menu->permissions()->attach($permission);

        $menu = new Menu();
        $menu->name = '角色管理';
        $menu->description = '角色管理';
        $menu->url = '/admin/role';
        $menu->position = 1;
        $menu->is_open = 1;
        $menu->save();

        $menu->permissions()->attach($permission);

        $output->writeln('管理员菜单创建完毕');
    }
}
