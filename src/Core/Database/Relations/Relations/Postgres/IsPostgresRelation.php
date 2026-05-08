<?php
namespace LARAVEL\DatabaseCore\Relations\Relations\Postgres;

use LARAVEL\DatabaseCore\Eloquent\Builder;
use LARAVEL\DatabaseCore\Eloquent\Model;
use LARAVEL\DatabaseCore\Query\Expression;
use LARAVEL\DatabaseCore\Relations\Casts\Uuid;

trait IsPostgresRelation
{
    /**
     * Get the wrapped and cast JSON column.
     *
     * @param \LARAVEL\DatabaseCore\Eloquent\Builder<*> $query
     * @param \LARAVEL\DatabaseCore\Eloquent\Model $model
     * @param string $column
     * @param string $key
     * @return \LARAVEL\DatabaseCore\Query\Expression<*>
     */
    protected function jsonColumn(Builder $query, Model $model, $column, $key)
    {
        $sql = $query->getQuery()->getGrammar()->wrap($column);

        if ($model->getKeyName() === $key && in_array($model->getKeyType(), ['int', 'integer'])) {
            $sql = '('.$sql.')::bigint';
        }

        if ($model->hasCast($key) && $model->getCasts()[$key] === Uuid::class) {
            $sql = '('.$sql.')::uuid';
        }

        return new Expression($sql);
    }

    /**
     * Get the name of the "where in" method for eager loading.
     *
     * @param \LARAVEL\DatabaseCore\Eloquent\Model $model
     * @param string $key
     * @return string
     */
    protected function whereInMethod(Model $model, $key)
    {
        return 'whereIn';
    }
}
