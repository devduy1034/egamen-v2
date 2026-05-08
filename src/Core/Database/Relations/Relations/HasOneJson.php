<?php
namespace LARAVEL\DatabaseCore\Relations\Relations;

use LARAVEL\DatabaseCore\Eloquent\Collection;
use LARAVEL\DatabaseCore\Eloquent\Model;
use LARAVEL\DatabaseCore\Eloquent\Relations\Concerns\SupportsDefaultModels;
use LARAVEL\DatabaseCore\Eloquent\Relations\HasOneOrMany;

/**
 * @template TRelatedModel of \LARAVEL\DatabaseCore\Eloquent\Model
 * @template TDeclaringModel of \LARAVEL\DatabaseCore\Eloquent\Model
 *
 * @extends \Staudenmeir\EloquentJsonRelations\Relations\HasManyJson<TRelatedModel, TDeclaringModel>
 */
class HasOneJson extends HasManyJson
{
    use SupportsDefaultModels;

    /** @inheritDoc */
    public function getResults()
    {
        if (is_null($this->getParentKey())) {
            return $this->getDefaultFor($this->parent);
        }

        return $this->first() ?: $this->getDefaultFor($this->parent);
    }

    /** @inheritDoc */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->getDefaultFor($model));
        }

        return $models;
    }

    /** @inheritDoc */
    public function match(array $models, Collection $results, $relation)
    {
        return $this->matchOne($models, $results, $relation);
    }

    /** @inheritDoc */
    public function matchOne(array $models, Collection $results, $relation)
    {
        if ($this->hasCompositeKey()) {
            $this->matchWithCompositeKey($models, $results, $relation, 'one');
        } else {
            HasOneOrMany::matchOneOrMany($models, $results, $relation, 'one');
        }

        if ($this->key) {
            foreach ($models as $model) {
                /** @var TRelatedModel|null $relatedModel */
                $relatedModel = $model->$relation;

                /** @var \LARAVEL\DatabaseCore\Eloquent\Collection<int, TRelatedModel> $relatedModels */
                $relatedModels = new Collection(
                    array_filter([$relatedModel])
                );

                $model->setRelation(
                    $relation,
                    $this->hydratePivotRelation(
                        $relatedModels,
                        $model,
                        fn (Model $model) => $model->{$this->getPathName()}
                    )->first()
                );
            }
        }

        return $models;
    }

    /**
     * Make a new related instance for the given model.
     *
     * @param TDeclaringModel $parent
     * @return TRelatedModel
     */
    public function newRelatedInstanceFor(Model $parent)
    {
        return $this->related->newInstance();
    }
}
