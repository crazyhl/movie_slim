<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-02-14
 * Time: 16:13
 */

namespace App\Command;


use App\Model\Category;
use App\Model\CategorySourceCategoryRelation;
use App\Model\Permission;
use App\Model\SourceInfo;
use App\Utils;
use Illuminate\Database\Capsule\Manager;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddTestData extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'test:addData';
    private $container;

    public function __construct(ContainerInterface $container, $name = null)
    {
        parent::__construct($name);
        $this->container = $container;
    }

    protected function configure()
    {
        $this->setDescription('增加测试数据');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // 增加测试源站任务
//        $sourceSite = new SourceInfo();
//        $sourceSite->name = '最大云播';
//        $sourceSite->type = 0;
//        $sourceSite->api_url = 'http://www.zdziyuan.com/inc/api_zuidam3u8.php';
//        $sourceSite->is_async_crawl = 0;
//        $sourceSite->save();

        // 增加测试投递任务
//        /**
//         * @var $redis \Redis
//         */
//        $redis = $this->container->get('redis');
//        $crawKey = $this->container->get('redisKey')['crawlRedisTaskQueueKey'];
////        var_dump($crawKey);
////        $redis->del($crawKey);
//        $task = 'movie::4::' . json_encode([
//                'ac' => 'videolist',
//                'ids' => '',
//                't' => '',
//                'h' => ''
//            ]) . '::0';
//        $redis->zAdd($crawKey, time(), $task);

        //        // 增加测试分类
//        $sourceSiteId = 4;
//        $sourceSiteCategoryId = [
//            1 => '电影片',
//            2 => '连续剧',
//            3 => '综艺片',
//            4 => '动漫片',
//            5 => '动作片',
//            6 => '喜剧片',
//            7 => '爱情片',
//            8 => '科幻片',
//            9 => '恐怖片',
//            10 => '剧情片',
//            11 => '战争片',
//            12 => '国产剧',
//            13 => '香港剧',
//            14 => '韩国剧',
//            15 => '欧美剧',
//            16 => '福利片',
//            17 => '伦理片',
//            18 => '音乐片',
//            19 => '台湾剧',
//            20 => '日本剧',
//            21 => '海外剧',
//        ];
//
//        foreach ($sourceSiteCategoryId as $id => $name) {
//            $category = new Category();
//            $category->name = $name;
//            $category->save();
//            $categroySourceRelation = new CategorySourceCategoryRelation();
//            $categroySourceRelation->category_id = $category->id;
//            $categroySourceRelation->source_site_id = $sourceSiteId;
//            $categroySourceRelation->source_site_category_id = $id;
//            $categroySourceRelation->save();
//        }

//        /**
//         * @var Utils $utils
//         */
//        $utils = $this->container->get('utils');
//
//        $token = $utils->jwtEncode(['uid' => 123]);
//
//        $output->writeln($token);
//
//        $jws = $utils->jwtDecode($token);
//        var_dump($jws->getPayload());
//
//        $output->writeln([
//            '测试数据填充成功',
//        ]);
//        /**
//         * @var $db Manager
//         */
//        $db = $this->container->get('db');
//        $db->getConnection()->enableQueryLog();
//        $user = \App\Model\User::with('roles', 'permissions')->find(1);
//        var_dump($user->roles);
//        var_dump($user->permissions);
//        $log = $db->getConnection()->getQueryLog();
//        var_dump($log);
        $category = Category::find(2);
        $permission = new Permission();
        $permission->name = ' testPermission2';
        $permission->slug = ' testPermission2';
        $permission->description = '';
        $permission->save();

        $category->permissions()->attach($permission);
    }
}
