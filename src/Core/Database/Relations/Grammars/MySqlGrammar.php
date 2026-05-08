<?php
namespace LARAVEL\DatabaseCore\Relations\Grammars;

use LARAVEL\DatabaseCore\Query\Grammars\MySqlGrammar as Base;
use LARAVEL\DatabaseCore\Relations\Grammars\Traits\CompilesMySqlJsonQueries;

class MySqlGrammar extends Base implements JsonGrammar
{
    use CompilesMySqlJsonQueries;
}
