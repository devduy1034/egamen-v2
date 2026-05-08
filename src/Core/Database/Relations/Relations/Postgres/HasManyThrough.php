<?php
namespace LARAVEL\DatabaseCore\Relations\Relations\Postgres;

use LARAVEL\DatabaseCore\Eloquent\Relations\HasManyThrough as Base;

/**
 * @template TRelatedModel of \LARAVEL\DatabaseCore\Eloquent\Model
 * @template TIntermediateModel of \LARAVEL\DatabaseCore\Eloquent\Model
 * @template TDeclaringModel of \LARAVEL\DatabaseCore\Eloquent\Model
 *
 * @extends \LARAVEL\DatabaseCore\Eloquent\Relations\HasManyThrough<TRelatedModel, TIntermediateModel, TDeclaringModel>
 */
class HasManyThrough extends Base
{
    /** @use \LARAVEL\DatabaseCore\Relations\Relations\Postgres\HasOneOrManyThrough<TRelatedModel, TIntermediateModel, TDeclaringModel> */
    use HasOneOrManyThrough;
}
