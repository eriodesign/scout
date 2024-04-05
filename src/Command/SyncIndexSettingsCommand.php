<?php

namespace Eriodesign\Scout\Command;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Eriodesign\Scout\EngineManager;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class SyncIndexSettingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected static $defaultName = 'scout:sync-index-settings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected static $defaultDescription = '将配置的索引设置与搜索引擎（Meilisearch）同步';


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
     * @return int
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $engine = app(EngineManager::class)->engine();

        $driver = config('plugin.eriodesign.scout.app.driver');

        if (!method_exists($engine, 'updateIndexSettings')) {
            $output->writeln('"' . $driver . '" 引擎不支持更新索引设置');
            return 0;
        }

        try {
            $indexes = (array) config('plugin.eriodesign.scout.app.' . $driver . '.index-settings', []);

            if (count($indexes)) {
                foreach ($indexes as $name => $settings) {
                    if (!is_array($settings)) {
                        $name = $settings;
                        $settings = [];
                    }

                    if (class_exists($name)) {
                        $model = new $name;
                    }

                    if (
                        isset($model) &&
                        config('plugin.eriodesign.scout.app.soft_delete', false) &&
                        in_array(SoftDeletes::class, class_uses_recursive($model))
                    ) {
                        $settings['filterableAttributes'][] = '__soft_deleted';
                    }

                    $engine->updateIndexSettings($indexName = $this->indexName($name), $settings);

                    $output->writeln('[' . $indexName . '] 索引设置已同步成功');
                }
            } else {
                $output->writeln('未找到 "' . $driver . '" 搜索引擎');
            }
        } catch (Exception $exception) {
            $output->writeln($exception->getMessage());
        }
        return 1;
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

        return !Str::startsWith($name, $prefix) ? $prefix . $name : $name;
    }
}
