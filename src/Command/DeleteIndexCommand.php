<?php

namespace Eriodesign\Scout\Command;

use Symfony\Component\Console\Command\Command;
use Eriodesign\Scout\EngineManager;
use support\Container;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteIndexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected static $defaultName = 'scout:delete-index';
    protected static $defaultDescription = '删除索引';

    protected function configure()
    {
        $this->addArgument('name', InputArgument::OPTIONAL, '索引名称');
    }

    /**
     * Execute the console command.
     *
     * @param  \Eriodesign\Scout\EngineManager  $manager
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $name = $input->getArgument('name');
            app(EngineManager::class)->engine()->deleteIndex($name);
            $output->writeln('索引 "'.$name.'" 已删除.');
        } catch (\Exception $exception) {
            $output->writeln($exception->getMessage());
        }
        return 1;
    }
}
