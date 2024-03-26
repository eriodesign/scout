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
    protected static $defaultDescription = 'Delete an index';

    protected function configure()
    {
        $this->addArgument('name', InputArgument::OPTIONAL, 'The name of the index');
    }

    /**
     * Execute the console command.
     *
     * @param  \Eriodesign\Scout\EngineManager  $manager
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $name = $input->getArgument('name');
            app(EngineManager::class)->engine()->deleteIndex($name);
            $output->writeln('Index "'.$name.'" deleted.');
        } catch (\Exception $exception) {
            $output->writeln($exception->getMessage());
        }
        return 1;
    }
}
