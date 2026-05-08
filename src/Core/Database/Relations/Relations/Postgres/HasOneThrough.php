<?php
namespace LARAVEL\DatabaseCore\Relations\Relations\Postgres;

use LARAVEL\DatabaseCore\Eloquent\Relations\HasOneThrough as Base;

/**
 * @template TRelatedModel of \LARAVEL\DatabaseCore\Eloquent\Model
 * @template TIntermediateModel of \LARAVEL\DatabaseCore\Eloquent\Model
 * @template TDeclaringModel of \LARAVEL\DatabaseCore\Eloquent\Model
 *
 * @extends \LARAVEL\DatabaseCore\Eloquent\Relations\HasOneThrough<TRelatedModel, TIntermediateModel, TDeclaringModel>
 */
class HasOneThrough extends Base
{
    /** @use \Staudenmeir\EloquentJsonRelations\Relations\Postgres\HasOneOrManyThrough<TRelatedModel, TIntermediateModel, TDeclaringModel> */
    use HasOneOrManyThrough;
}
