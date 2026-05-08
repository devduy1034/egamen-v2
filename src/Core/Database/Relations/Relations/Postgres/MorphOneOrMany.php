<?php
namespace LARAVEL\DatabaseCore\Relations\Relations\Postgres;

use LARAVEL\DatabaseCore\Eloquent\Builder;

/**
 * @template TRelatedModel of \LARAVEL\DatabaseCore\Eloquent\Model
 * @template TDeclaringModel of \LARAVEL\DatabaseCore\Eloquent\Model
 */
trait MorphOneOrMany
{
    /** @use \Staudenmeir\EloquentJsonRelations\Relations\Postgres\HasOneOrMany<TRelatedModel, TDeclaringModel> */
    use HasOneOrMany {
        getRelationExistenceQuery as getRelationExistenceQueryParent;
    }

    /**
     * Add the constraints for an internal relationship existence query.
     *
     * @param \LARAVEL\DatabaseCore\Eloquent\Builder<TRelatedModel> $query
     * @param \LARAVEL\DatabaseCore\Eloquent\Builder<TDeclaringModel> $parentQuery
     * @param list<string>|string $columns
     * @return \LARAVEL\DatabaseCore\Eloquent\Builder<TRelatedModel>
     */
    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        return $this->getRelationExistenceQueryParent($query, $parentQuery, $columns)
            ->where($this->morphType, $this->morphClass);
    }
}
