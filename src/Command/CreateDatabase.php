<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-02-13
 * Time: 17:33
 */

namespace App\Command;


use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Schema\Blueprint;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateDatabase extends Command
{
    protected static $defaultName = 'db:createDatabase';
    private $container;

    public function __construct(ContainerInterface $container, $name = null)
    {
        parent::__construct($name);
        $this->container = $container;
    }

    protected function configure()
    {
        $this->setDescription('创建数据库表命令');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // ...
        /**
         * @var Manager
         */
        $this->container->get('db');
        //category 分类
        $tableName = 'category';
        Manager::schema()->dropIfExists($tableName);
        Manager::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('分类id');
            $table->integer('sort')->default(0)->index()->comment('排序');
            $table->boolean('is_show')->default(1)->index()->comment('是否显示出来');
            $table->timestamps();
        });
        $output->writeln($tableName . ' 表创建完毕');
        //source_site 目标站信息
        $tableName = 'source_site';
        Manager::schema()->dropIfExists($tableName);
        Manager::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('目标站名称');
            $table->string('api_url')->comment('接口api url');
            $table->boolean('is_async_crawl')->default(0)->comment('是否异步爬取');
            $table->integer('crawl_interval')->default(0)->comment('爬取间隔');
            $table->timestamps();
        });
        $output->writeln($tableName . ' 表创建完毕');
        //category_source_site_category_relation 目标站分类和本地分类映射
        $tableName = 'category_source_site_category_relation';
        Manager::schema()->dropIfExists($tableName);
        Manager::schema()->create($tableName, function (Blueprint $table) {
            $table->integer('category_id')->comment('本地分类id');
            $table->integer('source_site_category_id')->comment('目标站分类id');
            $table->primary([
                'category_id',
                'source_site_category_id',
            ], 'category_source_site_category');
        });
        $output->writeln($tableName . ' 表创建完毕');
        $output->writeln([
            '数据库都创建好了',
        ]);
    }
}
