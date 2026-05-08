<?php
namespace LARAVEL\DatabaseCore\Eloquent;

interface Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \LARAVEL\DatabaseCore\Eloquent\Builder  $builder
     * @param  \LARAVEL\DatabaseCore\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model);
}
