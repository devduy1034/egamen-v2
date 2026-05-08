<?php
namespace LARAVEL\DatabaseCore\Relations\Grammars;

use LARAVEL\DatabaseCore\ConnectionInterface;
use LARAVEL\DatabaseCore\Query\Expression;
use LARAVEL\DatabaseCore\Query\Grammars\PostgresGrammar as Base;
use RuntimeException;

class PostgresGrammar extends Base implements JsonGrammar
{
    /**
     * Compile a "JSON array" statement into SQL.
     *
     * @param string|\LARAVEL\DatabaseCore\Query\Expression<*> $column
     * @return string
     */
    public function compileJsonArray($column)
    {
        return 'jsonb_build_array('.$this->wrap($column).')';
    }

    /**
     * Compile a "JSON object" statement into SQL.
     *
     * @param string $column
     * @param int $levels
     * @return string
     */
    public function compileJsonObject($column, $levels)
    {
        $sql = str_repeat('jsonb_build_object(?::text, ', $levels)
                .$this->wrap($column)
                .str_repeat(')', $levels);

        return $this->compileJsonArray(new Expression($sql));
    }

    /**
     * Compile a "JSON value select" statement into SQL.
     *
     * @param string $column
     * @return string
     */
    public function compileJsonValueSelect(string $column): string
    {
        return $this->wrap($column);
    }

    /**
     * Determine whether the database supports the "member of" operator.
     *
     * @param \LARAVEL\DatabaseCore\ConnectionInterface $connection
     * @return bool
     */
    public function supportsMemberOf(ConnectionInterface $connection): bool
    {
        return false;
    }

    /**
     * Compile a "member of" statement into SQL.
     *
     * @param string $column
     * @param string|null $objectKey
     * @param mixed $value
     * @return string
     */
    public function compileMemberOf(string $column, ?string $objectKey, mixed $value): string
    {
        throw new RuntimeException('This database is not supported.'); // @codeCoverageIgnore
    }

    /**
     * Prepare the bindings for a "member of" statement.
     *
     * @param mixed $value
     * @return list<mixed>
     */
    public function prepareBindingsForMemberOf(mixed $value): array
    {
        throw new RuntimeException('This database is not supported.'); // @codeCoverageIgnore
    }
}
