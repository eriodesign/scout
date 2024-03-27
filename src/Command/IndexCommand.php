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
    protected static $defaultDescription = 'Create an index';
    /**
     * @return void
     */
    protected function configure()
    {
        $this->addArgument('name', InputArgument::OPTIONAL, 'The name of the index');
        $this->addOption('key', '-k', InputOption::VALUE_REQUIRED, 'The name of the primary key');
    }

    /**
     * Execute the console command.
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
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
