<?php

namespace Laravel\Scout\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'scout:flush')]
class FlushCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:flush {model : 要清除的模型的类名}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "从索引中清除模型的所有记录";

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $class = $this->argument('model');

        $model = new $class;

        $model::removeAllFromSearch();

        $this->info('所有 ['.$class.'] 记录已被刷新');
    }
}
