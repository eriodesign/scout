<?php

namespace Eriodesign\Scout;

use Illuminate\Container\Container;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Scope;
use Psr\EventDispatcher\EventDispatcherInterface;
use Eriodesign\Scout\Events\ModelsFlushed;
use Eriodesign\Scout\Events\ModelsImported;

class SearchableScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(EloquentBuilder $builder, Model $model)
    {
        //
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function extend(EloquentBuilder $builder)
    {
        $builder->macro('searchable', function (EloquentBuilder $builder, $chunk = null) {
            $builder->chunkById($chunk ?: config('plugin.eriodesign.scout.app.chunk.searchable', 500), function ($models) {
                $models->filter->shouldBeSearchable()->searchable();
                $mode = new ModelsImported($models);
                event($mode);

            });
        });

        $builder->macro('unsearchable', function (EloquentBuilder $builder, $chunk = null) {
            $builder->chunkById($chunk ?: config('plugin.eriodesign.scout.app.chunk.unsearchable', 500), function ($models) {
                $models->unsearchable();
                event(new ModelsFlushed($models));
            });
        });

        HasManyThrough::macro('searchable', function ($chunk = null) {
            $this->chunkById($chunk ?: config('plugin.eriodesign.scout.app.chunk.searchable', 500), function ($models) {
                $models->filter->shouldBeSearchable()->searchable();
                event(new ModelsImported($models));
            });
        });

        HasManyThrough::macro('unsearchable', function ($chunk = null) {
            $this->chunkById($chunk ?: config('plugin.eriodesign.scout.app.chunk.searchable', 500), function ($models) {
                $models->unsearchable();
                event(new ModelsFlushed($models));
            });
        });
    }
}
