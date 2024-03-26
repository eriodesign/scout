<?php

namespace Eriodesign\Scout\Command;

use Illuminate\Events\Dispatcher;
use Eriodesign\Scout\Events\ModelsImported;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected static $defaultName = 'scout:import';
    protected static $defaultDescription = 'Import the given model into the search index';

    protected function configure()
    {
        $this->addArgument('model', InputArgument::OPTIONAL, 'Class name of model to bulk import');
        $this->addOption('chunk', '--c', InputOption::VALUE_REQUIRED, 'The number of records to import at a time (Defaults to configuration value: `scout.chunk.searchable`');
    }
    /**
     * Execute the console command.
     * @return void
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $class = $input->getArgument('model');
        $model = new $class;
        $provider = app(Dispatcher::class);
        $provider->listen(ModelsImported::class, function ($event) use (&$output, $class) {
            $key = $event->models->last()->getScoutKey();
            $output->writeln('<comment>Imported ['.$class.'] models up to ID:</comment> '.$key);
        });

        $model::makeAllSearchable($input->getOption('chunk'));
        $provider->forget(ModelsImported::class);
        $output->writeln('All ['.$class.'] records have been imported.');
        return 1;
    }

}
