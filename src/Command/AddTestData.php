<?php
/**
 * Created by PhpStorm.
 * User: haoliang
 * Date: 2019-02-14
 * Time: 16:13
 */

namespace App\Command;


use App\Model\SourceInfo;
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
        $this->container->get('db');
        $sourceSite = new SourceInfo();
        $sourceSite->name = '最大云播';
        $sourceSite->type = 0;
        $sourceSite->api_url = 'http://www.zdziyuan.com/inc/api_zuidam3u8.php';
        $sourceSite->is_async_crawl = 0;
        $sourceSite->save();
        $output->writeln([
            '测试数据填充成功',
        ]);
    }
}
