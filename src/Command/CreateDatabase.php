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
        //category 分类
        $tableName = 'category';
        Manager::schema()->dropIfExists($tableName);
        Manager::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('分类id');
            $table->integer('parent_id')->default(0)->index()->comment('父分类id');
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
            // 这个就先这样，虽然目前值支持电影的，天知道我还会不会增加其他的呢
            $table->tinyInteger('type')->comment('目标站类型 0 电影');
            $table->string('api_url')->comment('接口api url');
            $table->boolean('is_async_crawl')->default(0)->comment('是否异步爬取'
                . '异步任务投递只会重新投递抓取最近24小时的任务');
            $table->boolean('is_default_info')->default(1)
                ->comment('是否采用默认信息，这个是保存默认视频相关信息用的，多个是1的就按照最近更新的覆盖好了'
                    . '如果抓到的片子库里没有，就默认采用第一个抓到的信息');
            $table->integer('crawl_interval')->default(0)->comment('爬取间隔');
            $table->timestamps();
        });
        $output->writeln($tableName . ' 表创建完毕');

        //category_source_site_category_relation 目标站分类和本地分类映射
        $tableName = 'category_source_site_category_relation';
        Manager::schema()->dropIfExists($tableName);
        Manager::schema()->create($tableName, function (Blueprint $table) {
            $table->integer('category_id')->comment('本地分类id');
            $table->integer('source_site_id')->comment('目标站id');
            $table->integer('source_site_category_id')->comment('目标站分类id');
            $table->primary([
                'category_id',
                'source_site_id',
                'source_site_category_id',
            ], 'category_source_site_category');
        });
        $output->writeln($tableName . ' 表创建完毕');

        //movie_info 电影信息
        $tableName = 'movie_info';
        Manager::schema()->dropIfExists($tableName);
        Manager::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->comment('片名');
            $table->string('show_name')->comment('显示片名，外显用这个字段');
            $table->integer('category_id')->index()->comment('本地分类id');
            $table->string('cover')->comment('电影封面图');
            $table->string('lang')->comment('语言');
            $table->string('area')->comment('区域');
            $table->integer('year')->comment('上映年份');
            $table->text('note')->comment('目前抓回来的都是空的，多跑点数据就知道是干啥的了');
            $table->string('actor')->comment('演员');
            $table->string('director')->comment('导演');
            $table->text('description')->comment('简介');
            $table->boolean('is_show')->default(1)->comment('是否外显');
            $table->timestamps();
        });
        $output->writeln($tableName . ' 表创建完毕');

        //movie_info_source 源电影信息，所有的抓取数据我们都会保留一份源信息，
        //1是用来溯源 2我们可以替换不同源的信息到我们的主表去，让信息更丰满 3 用来决策是否更新，这个才是最重要
        $tableName = 'movie_info_source';
        Manager::schema()->dropIfExists($tableName);
        Manager::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('local_id')->index()->comment('本地id');
            $table->string('name')->comment('片名');
            $table->string('show_name')->comment('显示片名，外显用这个字段');
            $table->integer('source_site_id')->index()->comment('源站id');
            $table->integer('source_id')->index()->comment('源id');
            $table->integer('source_category_id')->index()->comment('源分类id');
            $table->timestamp('source_last_update')->index()->comment('源站更新时间');
            $table->string('cover')->comment('电影封面图');
            $table->string('lang')->comment('语言');
            $table->string('area')->comment('区域');
            $table->integer('year')->comment('上映年份');
            $table->text('note')->comment('等于是标题的扩展说明');
            $table->string('actor')->comment('演员');
            $table->string('director')->comment('导演');
            $table->text('description')->comment('简介');
            $table->timestamps();
            $table->index([
                'source_site_id',
                'source_id'
            ]);
        });
        $output->writeln($tableName . ' 表创建完毕');

        //movie_video_list 视频播放列表，
        $tableName = 'movie_video_list';
        Manager::schema()->dropIfExists($tableName);
        Manager::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->integer('movie_info_id')->index()->comment('视频信息id');
            $table->integer('source_site_id')->index()->comment('源站id');
            $table->text('video_info')->comment('视频信息');
            $table->timestamps();
        });
        $output->writeln($tableName . ' 表创建完毕');

        //movie_cover 视频封面，考虑到可能重复较多，用一个表保持唯一，这样就能节省磁盘容量
        $tableName = 'movie_cover';
        Manager::schema()->dropIfExists($tableName);
        Manager::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->char('file_md5', 32)->index()->comment('文件md5');
            $table->string('file_path')->comment('文件路径');
            $table->timestamps();
        });
        $output->writeln($tableName . ' 表创建完毕');

        //user 用户表
        $tableName = 'user';
        Manager::schema()->dropIfExists($tableName);
        Manager::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('username', 32)->unique()->comment('用户名');
            $table->string('password', 255)->comment('密码');
            $table->timestamps();
        });
        $output->writeln($tableName . ' 表创建完毕');

        //role 角色表
        $tableName = 'role';
        Manager::schema()->dropIfExists($tableName);
        Manager::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 32)->comment('角色名称');
            $table->string('slug', 32)->unique()->comment('英文别名');
            $table->string('description', 255)->comment('角色说明');
            $table->boolean('is_open')->default(1)->index()->comment('是否启用');
            $table->integer('expire')->default(0)->comment('有效期');
            $table->timestamps();
        });
        $output->writeln($tableName . ' 表创建完毕');

        //role_user 角色用户关系表
        $tableName = 'role_user';
        Manager::schema()->dropIfExists($tableName);
        Manager::schema()->create($tableName, function (Blueprint $table) {
            $table->integer('role_id')->unsigned();
            $table->integer('user_id')->unsigned();
            // 设置一个双主键
            $table->primary([
                'role_id',
                'user_id'
            ]);
        });
        $output->writeln($tableName . ' 表创建完毕');

        //permission 权限表
        $tableName = 'permission';
        Manager::schema()->dropIfExists($tableName);
        Manager::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 32)->comment('权限名称');
            $table->string('slug', 32)->unique()->comment('英文别名');
            $table->string('description', 255)->comment('权限说明');
            $table->boolean('is_open')->default(1)->index()->comment('是否启用');
            $table->integer('expire')->default(0)->comment('有效期');
            // 这个就是是指不是我们后台手工添加的，而是指在相关模型操作的时候，隐试绑定的特殊权限,也就是专属菜单
            // 那权限也需要考虑是否添加在这结果相关的字段个字段了
            $table->boolean('is_model_hidden_bind')->default(0)->index()
                ->comment('是否是模型隐式绑定');
            $table->integer('model_id')->default(0)->unsigned();
            $table->string('model_type', 255)->default('');

            $table->timestamps();
        });
        $output->writeln($tableName . ' 表创建完毕');

        //permission_role 角色权限关系表
        $tableName = 'permission_role';
        Manager::schema()->dropIfExists($tableName);
        Manager::schema()->create($tableName, function (Blueprint $table) {
            $table->integer('role_id')->unsigned();
            $table->integer('permission_id')->unsigned();
            // 设置一个双主键
            $table->primary([
                'role_id',
                'permission_id'
            ]);
        });
        $output->writeln($tableName . ' 表创建完毕');

        //permission_role 用户权限关系表
        $tableName = 'permission_user';
        Manager::schema()->dropIfExists($tableName);
        Manager::schema()->create($tableName, function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->integer('permission_id')->unsigned();
            // 设置一个双主键
            $table->primary([
                'user_id',
                'permission_id'
            ]);
        });
        $output->writeln($tableName . ' 表创建完毕');

        // menu 菜单表
        // 计划是所有的菜单现在这边创建好，然后再去代码那边写代码
        // 增加一个跟model的绑定相关字段，一个菜单目前看来只会跟一个主model 绑定
        $tableName = 'menu';
        Manager::schema()->dropIfExists($tableName);
        Manager::schema()->create($tableName, function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 32)->comment('菜单名称');
            $table->string('description', 255)->comment('权限说明');
            $table->string('icon', 255)->comment('图标');
            $table->string('url', 255)->unique()->comment('url');
            $table->integer('order')->unsigned()->default(0)->comment('排序');
            $table->integer('parent')->unsigned()->default(0)->comment('父级菜单');
            $table->boolean('is_open')->default(1)->index()->comment('是否启用');
            $table->boolean('need_permissions')->default(0)->index()->comment('是否需要权限验证');
            $table->boolean('is_show')->default(1)->index()
                ->comment('是否显示，如果不显示基本上都是页面内菜单');
            $table->tinyInteger('position')->default(1)->comment('位置 0 前台 1 后台');
            // 这个就是是指不是我们后台手工添加的，而是指在相关模型操作的时候，隐试绑定的特殊权限,也就是专属菜单
            // 那权限也需要考虑是否添加在这结果相关的字段个字段了
            $table->boolean('is_model_hidden_bind')->default(0)->index()
                ->comment('是否是模型隐式绑定');
            $table->integer('model_id')->default(0)->unsigned();
            $table->string('model_type', 255)->default('');

            $table->timestamps();
        });
        $output->writeln($tableName . ' 表创建完毕');

        //permission_model 用户模型关系表
        $tableName = 'permission_model_relation';
        Manager::schema()->dropIfExists($tableName);
        Manager::schema()->create($tableName, function (Blueprint $table) {
            $table->integer('permission_id')->unsigned();
            $table->integer('model_id')->unsigned();
            $table->string('model_type', 255);
            // 设置一个双主键
            $table->primary([
                'permission_id',
                'model_id',
                'model_type',
            ], 'permission_model_type');
        });
        $output->writeln($tableName . ' 表创建完毕');

        $output->writeln([
            '数据库都创建好了',
        ]);
    }
}
