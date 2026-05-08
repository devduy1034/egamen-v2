<?php
namespace LARAVEL\DatabaseCore\Relations\Relations\Postgres;

use LARAVEL\DatabaseCore\Eloquent\Builder;
use LARAVEL\DatabaseCore\Eloquent\Relations\BelongsTo as Base;

/**
 * @template TRelatedModel of \LARAVEL\DatabaseCore\Eloquent\Model
 * @template TDeclaringModel of \LARAVEL\DatabaseCore\Eloquent\Model
 *
 * @extends \LARAVEL\DatabaseCore\Eloquent\Relations\BelongsTo<TRelatedModel, TDeclaringModel>
 */
class BelongsTo extends Base
{
    use IsPostgresRelation;

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
        if ($parentQuery->getQuery()->from == $query->getQuery()->from) {
            return $this->getRelationExistenceQueryForSelfRelation($query, $parentQuery, $columns);
        }

        $first = $this->jsonColumn($query, $this->related, $this->getQualifiedForeignKeyName(), $this->ownerKey);

        $query->select($columns)->whereColumn(
            $first,
            '=',
            $query->qualifyColumn($this->ownerKey)
        );

        return $query;
    }

    /**
     * Add the constraints for a relationship query on the same table.
     *
     * @param \LARAVEL\DatabaseCore\Eloquent\Builder<TRelatedModel> $query
     * @param \LARAVEL\DatabaseCore\Eloquent\Builder<TDeclaringModel> $parentQuery
     * @param list<string>|string $columns
     * @return \LARAVEL\DatabaseCore\Eloquent\Builder<TRelatedModel>
     */
    public function getRelationExistenceQueryForSelfRelation(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        $query->select($columns)->from(
            $query->getModel()->getTable().' as '.$hash = $this->getRelationCountHash()
        );

        $query->getModel()->setTable($hash);

        $first = $this->jsonColumn($query, $this->related, $this->getQualifiedForeignKeyName(), $this->ownerKey);

        $query->whereColumn(
            $first,
            $hash.'.'.$this->ownerKey
        );

        return $query;
    }
}
