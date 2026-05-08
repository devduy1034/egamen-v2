<?php
namespace LARAVEL\DatabaseCore\Relations\Relations\Postgres;

use LARAVEL\DatabaseCore\Eloquent\Builder;

/**
 * @template TRelatedModel of \LARAVEL\DatabaseCore\Eloquent\Model
 * @template TDeclaringModel of \LARAVEL\DatabaseCore\Eloquent\Model
 */
trait HasOneOrMany
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
        if ($query->getQuery()->from == $parentQuery->getQuery()->from) {
            return $this->getRelationExistenceQueryForSelfRelation($query, $parentQuery, $columns);
        }

        $second = $this->jsonColumn($query, $this->parent, $this->getExistenceCompareKey(), $this->localKey);

        $query->select($columns)->whereColumn(
            $this->getQualifiedParentKeyName(),
            '=',
            $second // @phpstan-ignore-line
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
        $query->from($query->getModel()->getTable().' as '.$hash = $this->getRelationCountHash());

        $query->getModel()->setTable($hash);

        $second = $this->jsonColumn($query, $this->parent, $hash.'.'.$this->getForeignKeyName(), $this->localKey);

        $query->select($columns)->whereColumn(
            $this->getQualifiedParentKeyName(),
            '=',
            $second // @phpstan-ignore-line
        );

        return $query;
    }
}
