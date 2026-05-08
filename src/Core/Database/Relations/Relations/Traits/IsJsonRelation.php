<?php
namespace LARAVEL\DatabaseCore\Relations\Relations\Traits;

use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Contracts\Support\Arrayable;
use LARAVEL\DatabaseCore\Eloquent\Collection;
use LARAVEL\DatabaseCore\Eloquent\Model;
use LARAVEL\DatabaseCore\Eloquent\Relations\Pivot;
use RuntimeException;
use LARAVEL\DatabaseCore\Relations\Grammars\JsonGrammar;
use LARAVEL\DatabaseCore\Relations\Grammars\MariaDbGrammar;
use LARAVEL\DatabaseCore\Relations\Grammars\MySqlGrammar;
use LARAVEL\DatabaseCore\Relations\Grammars\PostgresGrammar;
use LARAVEL\DatabaseCore\Relations\Grammars\SQLiteGrammar;
use LARAVEL\DatabaseCore\Relations\Grammars\SqlServerGrammar;

/**
 * @template TRelatedModel of \LARAVEL\DatabaseCore\Eloquent\Model
 * @template TDeclaringModel of \LARAVEL\DatabaseCore\Eloquent\Model
 */
trait IsJsonRelation
{
    /**
     * The base path of the foreign key.
     *
     * @var string
     */
    protected $path;

    /**
     * The optional object key of the foreign key.
     *
     * @var string|null
     */
    protected $key;

    /**
     * Hydrate the pivot relationship on the models.
     *
     * @param \LARAVEL\DatabaseCore\Eloquent\Collection<int, TRelatedModel> $models
     * @param TDeclaringModel $parent
     * @param callable $callback
     * @return \LARAVEL\DatabaseCore\Eloquent\Collection<int, TRelatedModel>
     */
    public function hydratePivotRelation(Collection $models, Model $parent, callable $callback): Collection
    {
        foreach ($models as $i => $model) {
            $clone = clone $model;

            $models[$i] = $clone->setRelation(
                $this->getPivotAccessor(),
                $this->pivotRelation($clone, $parent, $callback)
            );
        }

        return $models;
    }

    /**
     * Get the pivot relationship from the query.
     *
     * @param TRelatedModel $model
     * @param TDeclaringModel $parent
     * @param callable $callback
     * @return TRelatedModel
     */
    protected function pivotRelation(Model $model, Model $parent, callable $callback)
    {
        /** @var list<array<string, mixed>>|\Illuminate\Contracts\Support\Arrayable<int, array<string, mixed>> $records */
        $records = $callback($model, $parent);

        if ($records instanceof Arrayable) {
            $records = $records->toArray();
        }

        $attributes = $this->pivotAttributes($model, $parent, $records);

        /** @var TRelatedModel $pivotModel */
        $pivotModel = Pivot::fromAttributes($model, $attributes, null, true); // @phpstan-ignore-line

        return $pivotModel;
    }

    /**
     * Get the pivot attributes from a model.
     *
     * @param TRelatedModel $model
     * @param TDeclaringModel $parent
     * @param array<int, array<string, mixed>> $records
     * @return array<string, mixed>
     */
    abstract public function pivotAttributes(Model $model, Model $parent, array $records);

    /**
     * Execute the query and get the first related model.
     *
     * @param list<string> $columns
     * @return TRelatedModel|null
     */
    public function first($columns = ['*'])
    {
        /** @var \LARAVEL\DatabaseCore\Eloquent\Collection<int, TRelatedModel> $models */
        $models = $this->take(1)->get($columns);

        return $models->first();
    }

    /**
     * Get the fully qualified path of the relationship.
     *
     * @return string
     */
    public function getQualifiedPath()
    {
        return $this->parent->qualifyColumn($this->path);
    }

    /**
     * Add a “where JSON contains” or "member of" clause to the query.
     *
     * @param \Illuminate\Contracts\Database\Query\Builder $query
     * @param string $column
     * @param mixed $value
     * @param callable|null $objectValueCallback
     * @param string $boolean
     * @return void
     */
    protected function whereJsonContainsOrMemberOf(
        Builder $query,
        string $column,
        mixed $value,
        ?callable $objectValueCallback = null,
        string $boolean = 'and'
    ): void {
        $grammar = $this->getJsonGrammar($query);
        $connection = $query->getConnection();

        if ($grammar->supportsMemberOf($connection)) {
            $query->whereRaw(
                $grammar->compileMemberOf($column, $this->key, $value),
                $grammar->prepareBindingsForMemberOf($value),
                $boolean
            );
        } else {
            $value = $this->key && $objectValueCallback ? $objectValueCallback($value) : $value;

            $query->whereJsonContains($column, $value, $boolean);
        }
    }

    /**
     * Get the JSON grammar.
     *
     * @param \Illuminate\Contracts\Database\Query\Builder $query
     * @return \LARAVEL\DatabaseCore\Relations\Grammars\JsonGrammar
     */
    protected function getJsonGrammar(Builder $query): JsonGrammar
    {
        
        $connection = $query->getConnection();
        $grammar = $connection->withTablePrefix(
            match ($connection->getDriverName()) {
                'mysql' => new MySqlGrammar(),
                'mariadb' => new MariaDbGrammar(),
                'pgsql' => new PostgresGrammar(),
                'sqlite' => new SQLiteGrammar(),
                'sqlsrv' => new SqlServerGrammar(),
                default => throw new RuntimeException('This database is not supported.')
            }
        );

        return $grammar;
    }

    /**
     * Get the name of the pivot accessor for this relationship.
     *
     * @return string
     */
    public function getPivotAccessor(): string
    {
        return 'pivot';
    }

    /**
     * Get the base path of the foreign key.
     *
     * @return string
     */
    public function getForeignKeyPath(): string
    {
        return $this->path;
    }
}
