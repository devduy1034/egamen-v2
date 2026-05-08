<?php
namespace LARAVEL\DatabaseCore;

use LARAVEL\DatabaseCore\Query\Grammars\MariaDbGrammar as QueryGrammar;
use LARAVEL\DatabaseCore\Query\Processors\MariaDbProcessor;
use LARAVEL\DatabaseCore\Schema\Grammars\MariaDbGrammar as SchemaGrammar;
use LARAVEL\DatabaseCore\Schema\MariaDbBuilder;
use LARAVEL\DatabaseCore\Schema\MariaDbSchemaState;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MariaDbConnection extends MySqlConnection
{
    /**
     * Determine if the connected database is a MariaDB database.
     *
     * @return bool
     */
    public function isMaria()
    {
        return true;
    }

    /**
     * Get the server version for the connection.
     *
     * @return string
     */
    public function getServerVersion(): string
    {
        return Str::between(parent::getServerVersion(), '5.5.5-', '-MariaDB');
    }

    /**
     * Get the default query grammar instance.
     *
     * @return \LARAVEL\DatabaseCore\Query\Grammars\MariaDbGrammar
     */
    protected function getDefaultQueryGrammar()
    {
        ($grammar = new QueryGrammar)->setConnection($this);

        return $this->withTablePrefix($grammar);
    }

    /**
     * Get a schema builder instance for the connection.
     *
     * @return \LARAVEL\DatabaseCore\Schema\MariaDbBuilder
     */
    public function getSchemaBuilder()
    {
        if (is_null($this->schemaGrammar)) {
            $this->useDefaultSchemaGrammar();
        }

        return new MariaDbBuilder($this);
    }

    /**
     * Get the default schema grammar instance.
     *
     * @return \LARAVEL\DatabaseCore\Schema\Grammars\MariaDbGrammar
     */
    protected function getDefaultSchemaGrammar()
    {
        ($grammar = new SchemaGrammar)->setConnection($this);

        return $this->withTablePrefix($grammar);
    }

    /**
     * Get the schema state for the connection.
     *
     * @param  \Illuminate\Filesystem\Filesystem|null  $files
     * @param  callable|null  $processFactory
     * @return \LARAVEL\DatabaseCore\Schema\MariaDbSchemaState
     */
    public function getSchemaState(Filesystem $files = null, callable $processFactory = null)
    {
        return new MariaDbSchemaState($this, $files, $processFactory);
    }

    /**
     * Get the default post processor instance.
     *
     * @return \LARAVEL\DatabaseCore\Query\Processors\MariaDbProcessor
     */
    protected function getDefaultPostProcessor()
    {
        return new MariaDbProcessor;
    }
}
