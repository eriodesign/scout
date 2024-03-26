<?php

namespace app\queue\redis;

use Webman\RedisQueue\Consumer;

class MakeSearchable implements Consumer
{
    // 要消费的队列名
    public $queue = 'scout_make';

    // 连接名，对应 plugin/webman/redis-queue/redis.php 里的连接`
    public $connection = 'default';

    // 消费
    public function consume($models)
    {
        $models = unserialize($models);
        if (count($models) === 0) {
            return;
        }
        $models->first()->searchableUsing()->update($models);
    }
}