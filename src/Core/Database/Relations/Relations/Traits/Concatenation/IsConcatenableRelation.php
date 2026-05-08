<?php
namespace LARAVEL\DatabaseCore\Relations\Relations\Traits\Concatenation;

use LARAVEL\DatabaseCore\Eloquent\Builder;

/**
 * @template TRelatedModel of \LARAVEL\DatabaseCore\Eloquent\Model
 * @template TDeclaringModel of \LARAVEL\DatabaseCore\Eloquent\Model
 */
trait IsConcatenableRelation
{
    /**
     * Set the constraints for an eager load of the deep relation.
     *
     * @param \LARAVEL\DatabaseCore\Eloquent\Builder<TRelatedModel> $query
     * @param list<TDeclaringModel> $models
     * @return void
     */
    public function addEagerConstraintsToDeepRelationship(Builder $query, array $models): void
    {
        $this->addEagerConstraints($models);

        $this->mergeWhereConstraints($query, $this->query);
    }

    /**
     * Merge the where constraints from another query to the current query.
     *
     * @param \LARAVEL\DatabaseCore\Eloquent\Builder<*> $query
     * @param \LARAVEL\DatabaseCore\Eloquent\Builder<*> $from
     * @return \LARAVEL\DatabaseCore\Eloquent\Builder<*>
     */
    public function mergeWhereConstraints(Builder $query, Builder $from): Builder
    {
        /** @var array<int, mixed> $whereBindings */
        $whereBindings = $from->getQuery()->getRawBindings()['where'] ?? [];

        $wheres = $from->getQuery()->wheres;

        $query->withoutGlobalScopes(
            $from->removedScopes()
        )->mergeWheres($wheres, $whereBindings);

        return $query;
    }
}
