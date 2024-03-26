<?php
/**
 *-------------------------------------------------------------------------s*
 *
 *-------------------------------------------------------------------------h*
 * @copyright  Copyright (c) 2015-2023 eriodesign Inc. (http://www.eriodesign.com)
 *-------------------------------------------------------------------------o*
 * @license    http://www.eriodesign.com        s h o p w w i . c o m
 *-------------------------------------------------------------------------p*
 * @link       http://www.eriodesign.com by 无锡豚豹科技
 *-------------------------------------------------------------------------w*
 * @since      eriodesign豚豹·PHP商城系统
 *-------------------------------------------------------------------------w*
 * @author      TycoonSong 8988354@qq.com
 *-------------------------------------------------------------------------i*
 */
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
