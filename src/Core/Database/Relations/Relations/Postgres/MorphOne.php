<?php
namespace LARAVEL\DatabaseCore\Relations\Relations\Postgres;

use LARAVEL\DatabaseCore\Eloquent\Relations\MorphOne as Base;

/**
 * @template TRelatedModel of \LARAVEL\DatabaseCore\Eloquent\Model
 * @template TDeclaringModel of \LARAVEL\DatabaseCore\Eloquent\Model
 *
 * @extends \LARAVEL\DatabaseCore\Eloquent\Relations\MorphOne<TRelatedModel, TDeclaringModel>
 */
class MorphOne extends Base
{
    /** @use \Staudenmeir\EloquentJsonRelations\Relations\Postgres\MorphOneOrMany<TRelatedModel, TDeclaringModel> */
    use MorphOneOrMany;
}
