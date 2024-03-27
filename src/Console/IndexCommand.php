<?php

namespace Laravel\Scout\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Laravel\Scout\EngineManager;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'scout:index')]
class IndexCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:index
            {name : 索引名称}
            {--k|key= : 主键的名称}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建一个索引';

    /**
     * Execute the console command.
     *
     * @param  \Laravel\Scout\EngineManager  $manager
     * @return void
     */
    public function handle(EngineManager $manager)
    {
        $engine = $manager->engine();

        try {
            $options = [];

            if ($this->option('key')) {
                $options = ['primaryKey' => $this->option('key')];
            }

            if (class_exists($modelName = $this->argument('name'))) {
                $model = new $modelName;
            }

            $name = $this->indexName($this->argument('name'));

            $engine->createIndex($name, $options);

            if (method_exists($engine, 'updateIndexSettings')) {
                $driver = config('plugin.eriodesign.scout.app.driver');

                $class = isset($model) ? get_class($model) : null;

                $settings = config('plugin.eriodesign.scout.app.'.$driver.'.index-settings.'.$name)
                                ?? config('plugin.eriodesign.scout.app.'.$driver.'.index-settings.'.$class)
                                ?? [];

                if (isset($model) &&
                    config('plugin.eriodesign.scout.app.soft_delete', false) &&
                    in_array(SoftDeletes::class, class_uses_recursive($model))) {
                    $settings['filterableAttributes'][] = '__soft_deleted';
                }

                if ($settings) {
                    $engine->updateIndexSettings($name, $settings);
                }
            }

            $this->info('索引 ["'.$name.'"] 创建成功');
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
