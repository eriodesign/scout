<?php

namespace Laravel\Scout\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Laravel\Scout\EngineManager;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'scout:sync-index-settings')]
class SyncIndexSettingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:sync-index-settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '将配置的索引设置与搜索引擎（Meilisearch）同步';

    /**
     * Execute the console command.
     *
     * @param  \Laravel\Scout\EngineManager  $manager
     * @return void
     */
    public function handle(EngineManager $manager)
    {
        $engine = $manager->engine();

        $driver = config('plugin.eriodesign.scout.app.driver');

        if (! method_exists($engine, 'updateIndexSettings')) {
            return $this->error('"'.$driver.'" 引擎不支持更新索引设置');
        }

        try {
            $indexes = (array) config('plugin.eriodesign.scout.app.'.$driver.'.index-settings', []);

            if (count($indexes)) {
                foreach ($indexes as $name => $settings) {
                    if (! is_array($settings)) {
                        $name = $settings;

                        $settings = [];
                    }

                    if (class_exists($name)) {
                        $model = new $name;
                    }

                    if (isset($model) &&
                        config('plugin.eriodesign.scout.app.soft_delete', false) &&
                        in_array(SoftDeletes::class, class_uses_recursive($model))) {
                        $settings['filterableAttributes'][] = '__soft_deleted';
                    }

                    $engine->updateIndexSettings($indexName = $this->indexName($name), $settings);

                    $this->info('['.$indexName.'] 索引设置已同步成功');
                }
            } else {
                $this->info('未找到 "'.$driver.'" 搜索引擎');
            }
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
