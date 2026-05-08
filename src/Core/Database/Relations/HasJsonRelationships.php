<?php
namespace LARAVEL\DatabaseCore\Relations;

use LARAVEL\DatabaseCore\Eloquent\Builder;
use LARAVEL\DatabaseCore\Eloquent\Collection;
use LARAVEL\DatabaseCore\Eloquent\Model;
use LARAVEL\DatabaseCore\Eloquent\Relations\BelongsTo;
use LARAVEL\DatabaseCore\Eloquent\Relations\HasMany;
use LARAVEL\DatabaseCore\Eloquent\Relations\HasManyThrough;
use LARAVEL\DatabaseCore\Eloquent\Relations\HasOne;
use LARAVEL\DatabaseCore\Eloquent\Relations\HasOneThrough;
use LARAVEL\DatabaseCore\Eloquent\Relations\MorphMany;
use LARAVEL\DatabaseCore\Eloquent\Relations\MorphOne;
use RuntimeException;
use LARAVEL\DatabaseCore\Relations\Relations\BelongsToJson;
use LARAVEL\DatabaseCore\Relations\Relations\HasManyJson;
use LARAVEL\DatabaseCore\Relations\Relations\HasOneJson;
use LARAVEL\DatabaseCore\Relations\Relations\Postgres\BelongsTo as BelongsToPostgres;
use LARAVEL\DatabaseCore\Relations\Relations\Postgres\HasMany as HasManyPostgres;
use LARAVEL\DatabaseCore\Relations\Relations\Postgres\HasManyThrough as HasManyThroughPostgres;
use LARAVEL\DatabaseCore\Relations\Relations\Postgres\HasOne as HasOnePostgres;
use LARAVEL\DatabaseCore\Relations\Relations\Postgres\HasOneThrough as HasOneThroughPostgres;
use LARAVEL\DatabaseCore\Relations\Relations\Postgres\MorphMany as MorphManyPostgres;
use LARAVEL\DatabaseCore\Relations\Relations\Postgres\MorphOne as MorphOnePostgres;

/**
 * @phpstan-ignore trait.unused
 */
trait HasJsonRelationships
{
    /** @inheritDoc */
    public function getAttribute($key)
    {
        $attribute = preg_split('/(->|\[])/', $key)[0];

        if (array_key_exists($attribute, $this->attributes)) {
            return $this->getAttributeValue($key);
        }

        return parent::getAttribute($key);
    }

    /** @inheritDoc */
    public function getAttributeFromArray($key)
    {
        if (str_contains($key, '->')) {
            return $this->getAttributeValue($key);
        }

        return parent::getAttributeFromArray($key);
    }

    /** @inheritDoc */
    public function getAttributeValue($key)
    {
        if (str_contains($key, '->')) {
            [$key, $path] = explode('->', $key, 2);

            if (substr($key, -2) === '[]') {
                $key = substr($key, 0, -2);

                $path = '*.'.$path;
            }

            $path = str_replace(['->', '[]'], ['.', '.*'], $path);

            return data_get($this->getAttributeValue($key), $path);
        }

        return parent::getAttributeValue($key);
    }

    /** @inheritDoc */
    protected function newHasOne(Builder $query, Model $parent, $foreignKey, $localKey)
    {
        /** @var \LARAVEL\DatabaseCore\Connection $connection */
        $connection = $query->getConnection();

        if ($connection->getDriverName() === 'pgsql') {
            return new HasOnePostgres($query, $parent, $foreignKey, $localKey);
        }

        return new HasOne($query, $parent, $foreignKey, $localKey);
    }

    /** @inheritDoc */
    protected function newHasOneThrough(Builder $query, Model $farParent, Model $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey)
    {
        /** @var \LARAVEL\DatabaseCore\Connection $connection */
        $connection = $query->getConnection();

        if ($connection->getDriverName() === 'pgsql') {
            return new HasOneThroughPostgres($query, $farParent, $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey);
        }

        return new HasOneThrough($query, $farParent, $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey);
    }

    /** @inheritDoc */
    protected function newMorphOne(Builder $query, Model $parent, $type, $id, $localKey)
    {
        /** @var \LARAVEL\DatabaseCore\Connection $connection */
        $connection = $query->getConnection();

        if ($connection->getDriverName() === 'pgsql') {
            return new MorphOnePostgres($query, $parent, $type, $id, $localKey);
        }

        return new MorphOne($query, $parent, $type, $id, $localKey);
    }

    /** @inheritDoc */
    protected function newBelongsTo(Builder $query, Model $child, $foreignKey, $ownerKey, $relation)
    {
        /** @var \LARAVEL\DatabaseCore\Connection $connection */
        $connection = $query->getConnection();

        if ($connection->getDriverName() === 'pgsql') {
            return new BelongsToPostgres($query, $child, $foreignKey, $ownerKey, $relation);
        }

        return new BelongsTo($query, $child, $foreignKey, $ownerKey, $relation);
    }

    /** @inheritDoc */
    protected function newHasMany(Builder $query, Model $parent, $foreignKey, $localKey)
    {
        /** @var \LARAVEL\DatabaseCore\Connection $connection */
        $connection = $query->getConnection();

        if ($connection->getDriverName() === 'pgsql') {
            return new HasManyPostgres($query, $parent, $foreignKey, $localKey);
        }

        return new HasMany($query, $parent, $foreignKey, $localKey);
    }

    /** @inheritDoc */
    protected function newHasManyThrough(Builder $query, Model $farParent, Model $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey)
    {
        /** @var \LARAVEL\DatabaseCore\Connection $connection */
        $connection = $query->getConnection();

        if ($connection->getDriverName() === 'pgsql') {
            return new HasManyThroughPostgres($query, $farParent, $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey);
        }

        return new HasManyThrough($query, $farParent, $throughParent, $firstKey, $secondKey, $localKey, $secondLocalKey);
    }

    /** @inheritDoc */
    protected function newMorphMany(Builder $query, Model $parent, $type, $id, $localKey)
    {
        /** @var \LARAVEL\DatabaseCore\Connection $connection */
        $connection = $query->getConnection();

        if ($connection->getDriverName() === 'pgsql') {
            return new MorphManyPostgres($query, $parent, $type, $id, $localKey);
        }

        return new MorphMany($query, $parent, $type, $id, $localKey);
    }

    /**
     * Define an inverse one-to-one or many JSON relationship.
     *
     * @template TRelatedModel of \LARAVEL\DatabaseCore\Eloquent\Model
     *
     * @param class-string<TRelatedModel> $related
     * @param string|array $foreignKey
     * @param string|array $ownerKey
     * @param string $relation
     * @return \LARAVEL\DatabaseCore\Relations\Relations\BelongsToJson<TRelatedModel, $this>
     */
    public function belongsToJson($related, $foreignKey, $ownerKey = null, $relation = null)
    {
        if (is_null($relation)) {
            $relation = $this->guessBelongsToRelation();
        }

        /** @var \LARAVEL\DatabaseCore\Eloquent\Model $instance */
        $instance = $this->newRelatedInstance($related);

        $ownerKey = $ownerKey ?: $instance->getKeyName();

        return $this->newBelongsToJson(
            $instance->newQuery(),
            $this,
            $foreignKey,
            $ownerKey,
            $relation
        );
    }

    /**
     * Instantiate a new BelongsToJson relationship.
     *
     * @template TRelatedModel of \LARAVEL\DatabaseCore\Eloquent\Model
     * @template TDeclaringModel of \LARAVEL\DatabaseCore\Eloquent\Model
     *
     * @param \LARAVEL\DatabaseCore\Eloquent\Builder<TRelatedModel> $query
     * @param TDeclaringModel $child
     * @param string|array $foreignKey
     * @param string|array $ownerKey
     * @param string $relation
     * @return \LARAVEL\DatabaseCore\Relations\Relations\BelongsToJson<TRelatedModel, TDeclaringModel>
     */
    protected function newBelongsToJson(Builder $query, Model $child, $foreignKey, $ownerKey, $relation)
    {
        return new BelongsToJson($query, $child, $foreignKey, $ownerKey, $relation);
    }

    /**
     * Define a one-to-many JSON relationship.
     *
     * @template TRelatedModel of \LARAVEL\DatabaseCore\Eloquent\Model
     *
     * @param class-string<TRelatedModel> $related
     * @param string|array $foreignKey
     * @param string|array $localKey
     * @return \LARAVEL\DatabaseCore\Relations\Relations\HasManyJson<TRelatedModel, $this>
     */
    public function hasManyJson($related, $foreignKey, $localKey = null)
    {
        /** @var \LARAVEL\DatabaseCore\Eloquent\Model $instance */
        $instance = $this->newRelatedInstance($related);

        if (is_array($foreignKey)) {
            $foreignKey = array_map(
                fn (string $key) => "{$instance->getTable()}.$key",
                (array) $foreignKey
            );
        } else {
            $foreignKey = "{$instance->getTable()}.$foreignKey";
        }

        $localKey = $localKey ?: $this->getKeyName();

        return $this->newHasManyJson(
            $instance->newQuery(),
            $this,
            $foreignKey,
            $localKey
        );
    }

    /**
     * Instantiate a new HasManyJson relationship.
     *
     * @template TRelatedModel of \LARAVEL\DatabaseCore\Eloquent\Model
     * @template TDeclaringModel of \LARAVEL\DatabaseCore\Eloquent\Model
     *
     * @param \LARAVEL\DatabaseCore\Eloquent\Builder<TRelatedModel> $query
     * @param TDeclaringModel $parent
     * @param string|array $foreignKey
     * @param string|array $localKey
     * @return \LARAVEL\DatabaseCore\Relations\Relations\HasManyJson<TRelatedModel, TDeclaringModel>
     */
    protected function newHasManyJson(Builder $query, Model $parent, $foreignKey, $localKey)
    {
        return new HasManyJson($query, $parent, $foreignKey, $localKey);
    }

    /**
     * Define a one-to-one JSON relationship.
     *
     * @template TRelatedModel of \LARAVEL\DatabaseCore\Eloquent\Model
     *
     * @param class-string<TRelatedModel> $related
     * @param string|array $foreignKey
     * @param string|array|null $localKey
     * @return \LARAVEL\DatabaseCore\Relations\Relations\HasOneJson<TRelatedModel, $this>
     */
    public function hasOneJson(string $related, string|array $foreignKey, string|array|null $localKey = null): HasOneJson
    {
        /** @var \LARAVEL\DatabaseCore\Eloquent\Model $instance */
        $instance = $this->newRelatedInstance($related);

        if (is_array($foreignKey)) {
            $foreignKey = array_map(
                fn (string $key) => "{$instance->getTable()}.$key",
                $foreignKey
            );
        } else {
            $foreignKey = "{$instance->getTable()}.$foreignKey";
        }

        $localKey = $localKey ?: $this->getKeyName();

        return $this->newHasOneJson(
            $instance->newQuery(),
            $this,
            $foreignKey,
            $localKey
        );
    }

    /**
     * Instantiate a new HasOneJson relationship.
     *
     * @template TRelatedModel of \LARAVEL\DatabaseCore\Eloquent\Model
     * @template TDeclaringModel of \LARAVEL\DatabaseCore\Eloquent\Model
     *
     * @param \LARAVEL\DatabaseCore\Eloquent\Builder<TRelatedModel> $query
     * @param TDeclaringModel $parent
     * @param string|array $foreignKey
     * @param string|array $localKey
     * @return \LARAVEL\DatabaseCore\Relations\Relations\HasOneJson<TRelatedModel, TDeclaringModel>
     */
    protected function newHasOneJson(Builder $query, Model $parent, string|array $foreignKey, string|array $localKey): HasOneJson
    {
        return new HasOneJson($query, $parent, $foreignKey, $localKey);
    }

    /**
     * Define has-many-through JSON relationship.
     *
     * @template TRelatedModel of \LARAVEL\DatabaseCore\Eloquent\Model
     *
     * @param class-string<TRelatedModel> $related
     * @param string $through
     * @param string|\LARAVEL\DatabaseCore\Relations\JsonKey $firstKey
     * @param string|null $secondKey
     * @param string|null $localKey
     * @param string|\LARAVEL\DatabaseCore\Relations\JsonKey|null $secondLocalKey
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<TRelatedModel, $this>
     */
    public function hasManyThroughJson(
        string $related,
        string $through,
        string|JsonKey $firstKey,
        ?string $secondKey = null,
        ?string $localKey = null,
        string|JsonKey|null $secondLocalKey = null
    ) {
        $relationships = [];

        $through = new $through();

        if ($firstKey instanceof JsonKey) {
            $relationships[] = $this->hasManyJson($through, $firstKey, $localKey);

            $relationships[] = $through->hasMany($related, $secondKey, $secondLocalKey);
        } else {
            if (!method_exists($through, 'belongsToJson')) {
                //@codeCoverageIgnoreStart
                $message = 'Please add the HasJsonRelationships trait to the ' . $through::class . ' model.';

                throw new RuntimeException($message);
                // @codeCoverageIgnoreEnd
            }

            $relationships[] = $this->hasMany($through, $firstKey, $localKey);

            $relationships[] = $through->belongsToJson($related, $secondLocalKey, $secondKey);
        }

        $hasManyThroughJson = $this->newHasManyThroughJson($relationships);

        $jsonKey = $firstKey instanceof JsonKey ? $firstKey : $secondLocalKey;

        if (str_contains($jsonKey, '[]->')) {
            $this->addHasManyThroughJsonPivotRelationship($hasManyThroughJson, $relationships, $through);
        }

        return $hasManyThroughJson;
    }

    /**
     * Add the pivot relationship to the has-many-through JSON relationship.
     *
     * @param \Staudenmeir\EloquentHasManyDeep\HasManyDeep $hasManyThroughJson
     * @param non-empty-list<\LARAVEL\DatabaseCore\Eloquent\Relations\Relation> $relationships
     * @param \LARAVEL\DatabaseCore\Eloquent\Model $through
     * @return void
     */
    protected function addHasManyThroughJsonPivotRelationship(
        $hasManyThroughJson,
        array $relationships,
        Model $through
    ): void {
        if ($relationships[0] instanceof HasManyJson) {
            /** @var \LARAVEL\DatabaseCore\Relations\Relations\HasManyJson $hasManyJson */
            $hasManyJson = $relationships[0];

            $postGetCallback = function (Collection $models) use ($hasManyJson) {
                if (isset($models[0]->laravel_through_key)) {
                    $hasManyJson->hydratePivotRelation(
                        $models,
                        $this,
                        fn (Model $model) => json_decode($model->laravel_through_key ?? '[]', true)
                    );
                }
            };

            $localKey = $this->{$hasManyJson->getLocalKeyName()};

            if (!is_null($localKey)) {
                $hasManyThroughJson->withPostGetCallbacks([$postGetCallback]);
            }

            $hasManyThroughJson->withCustomEagerMatchingCallback(
                function (array $models, Collection $results, string $relation) use ($hasManyJson) {
                    foreach ($models as $model) {
                        $hasManyJson->hydratePivotRelation(
                            $model->$relation,
                            $model,
                            fn (Model $model) => json_decode($model->laravel_through_key ?? '[]', true)
                        );
                    }

                    return $models;
                }
            );
        } else {
            /** @var \LARAVEL\DatabaseCore\Relations\Relations\BelongsToJson $belongsToJson */
            $belongsToJson = $relationships[1];

            $path = $belongsToJson->getForeignKeyPath();

            $postProcessor = function (Model $model, array $attributes) use ($belongsToJson, $path) {
                $records = json_decode($attributes[$path], true);

                return $belongsToJson->pivotAttributes($model, $model, $records);
            };

            $hasManyThroughJson->withPivot(
                $through->getTable(),
                [$path],
                accessor: 'pivot',
                postProcessor: $postProcessor
            );
        }
    }

    /**
     * Instantiate a new HasManyThroughJson relationship.
     *
     * @param non-empty-list<\LARAVEL\DatabaseCore\Eloquent\Relations\Relation> $relationships
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep
     */
    protected function newHasManyThroughJson(array $relationships)
    {
        if (!method_exists($this, 'hasManyDeepFromRelations')) {
            //@codeCoverageIgnoreStart
            $message = 'Please install staudenmeir/eloquent-has-many-deep and add the HasRelationships trait to this model.';

            throw new RuntimeException($message);
            // @codeCoverageIgnoreEnd
        }

        return $this->hasManyDeepFromRelations($relationships);
    }
}
