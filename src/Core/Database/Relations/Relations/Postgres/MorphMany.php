<?php
namespace LARAVEL\DatabaseCore\Relations\Relations\Postgres;

use LARAVEL\DatabaseCore\Eloquent\Relations\MorphMany as Base;

/**
 * @template TRelatedModel of \LARAVEL\DatabaseCore\Eloquent\Model
 * @template TDeclaringModel of \LARAVEL\DatabaseCore\Eloquent\Model
 *
 * @extends \LARAVEL\DatabaseCore\Eloquent\Relations\MorphMany<TRelatedModel, TDeclaringModel>
 */
class MorphMany extends Base
{
    /** @use \Staudenmeir\EloquentJsonRelations\Relations\Postgres\MorphOneOrMany<TRelatedModel, TDeclaringModel> */
    use MorphOneOrMany;
}
