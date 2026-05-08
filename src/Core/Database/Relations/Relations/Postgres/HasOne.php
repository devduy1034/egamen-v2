<?php
namespace LARAVEL\DatabaseCore\Relations\Relations\Postgres;

use LARAVEL\DatabaseCore\Eloquent\Relations\HasOne as Base;

/**
 * @template TRelatedModel of \LARAVEL\DatabaseCore\Eloquent\Model
 * @template TDeclaringModel of \LARAVEL\DatabaseCore\Eloquent\Model
 *
 * @extends \LARAVEL\DatabaseCore\Eloquent\Relations\HasOne<TRelatedModel, TDeclaringModel>
 */
class HasOne extends Base
{
    /** @use \LARAVEL\DatabaseCore\Relations\Relations\Postgres\HasOneOrMany<TRelatedModel, TDeclaringModel> */
    use HasOneOrMany;
}
