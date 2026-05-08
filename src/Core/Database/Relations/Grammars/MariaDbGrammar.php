<?php
namespace LARAVEL\DatabaseCore\Relations\Grammars;

use LARAVEL\DatabaseCore\ConnectionInterface;
use LARAVEL\DatabaseCore\Query\Grammars\MariaDbGrammar as Base;
use LARAVEL\DatabaseCore\Relations\Grammars\Traits\CompilesMySqlJsonQueries;

class MariaDbGrammar extends Base implements JsonGrammar
{
    use CompilesMySqlJsonQueries;

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
}
