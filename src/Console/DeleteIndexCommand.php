<?php

namespace Laravel\Scout\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Laravel\Scout\EngineManager;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'scout:delete-index')]
class DeleteIndexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:delete-index {name : 索引名称}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '删除指定索引';

    /**
     * Execute the console command.
     *
     * @param  \Laravel\Scout\EngineManager  $manager
     * @return void
     */
    public function handle(EngineManager $manager)
    {
        try {
            $manager->engine()->deleteIndex($name = $this->indexName($this->argument('name')));

            $this->info('Index "'.$name.'" deleted.');
        } catch (Exception $exception) {
            $this->error($exception->getMessage());
        }
    }

    /**
     * Get the fully-qualified index name for the given index.
     *
     * @param  string  $name
     * @return string
     */
    protected function indexName($name)
    {
        if (class_exists($name)) {
            return (new $name)->searchableAs();
        }

        $prefix = config('plugin.eriodesign.scout.app.prefix');

        return ! Str::startsWith($name, $prefix) ? $prefix.$name : $name;
    }
}
