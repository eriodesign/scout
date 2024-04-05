<?php

namespace Eriodesign\Scout\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class FlushCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected static $defaultName = 'scout:flush';
    protected static $defaultDescription = "从索引中清除模型的所有记录";
    /**
     * @return void
     */
    protected function configure()
    {
        $this->addArgument('model', InputArgument::OPTIONAL, '模型');
    }
    /**
     * Execute the console command.
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $class = $input->getArgument('model');

        $model = new $class;

        $model::removeAllFromSearch();

        $output->writeln('All ['.$class.'] records have been flushed.');
        return 1;
    }
}
