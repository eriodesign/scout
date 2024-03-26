<?php

namespace app\queue\redis;


use Illuminate\Contracts\Queue\ShouldQueue;
use Eriodesign\Scout\Jobs\RemoveableScoutCollection;

class RemoveFromSearch implements ShouldQueue
{
    // 要消费的队列名
    public $queue = 'scout_remove';

    // 连接名，对应 plugin/webman/redis-queue/redis.php 里的连接`
    public $connection = 'default';

    // 消费
    public function consume($models)
    {
        $models = unserialize($models);
        $this->models = RemoveableScoutCollection::make($models);
        if ($this->models->isNotEmpty()) {
            $this->models->first()->searchableUsing()->delete($this->models);
        }
    }
}
