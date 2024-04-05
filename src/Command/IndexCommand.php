<?php

namespace Eriodesign\Scout\Command;

use support\Container;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Eriodesign\Scout\EngineManager;

class IndexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected static $defaultName = 'scout:index';
    protected static $defaultDescription = '创建索引';
    /**
     * @return void
     */
    protected function configure()
    {
        $this->addArgument('name', InputArgument::OPTIONAL, '索引名称');
        $this->addOption('key', '-k', InputOption::VALUE_REQUIRED, '主键的名称');
    }

    /**
     * Execute the console command.
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $engine = app(EngineManager::class)->engine();
        $name = $input->getArgument('name');
        try {
            $options = [];
            if ($input->getOption('key')) {
                $options = ['primaryKey' => $input->getOption('key')];
            }
            $engine->createIndex($name, $options);
            $output->writeln('索引 ["'.$name.'"] 创建成功');
        } catch (\Exception $exception) {
            $output->writeln($exception->getMessage());
        }
        return 1;
    }
}
