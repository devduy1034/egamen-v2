<?php

 namespace LARAVEL\Helpers\Clockwork\Eloquent;

use LARAVEL\DatabaseCore\Eloquent\Builder;
use LARAVEL\DatabaseCore\Eloquent\Model;
use LARAVEL\DatabaseCore\Eloquent\Scope;
use LARAVEL\Helpers\Clockwork\Datasource\EloquentDataSource;

class ResolveModelScope implements Scope
{
	protected $dataSource;

	public function __construct(EloquentDataSource $dataSource)
	{
		$this->dataSource = $dataSource;
	}

	public function apply(Builder $builder, Model $model)
	{
		$this->dataSource->nextQueryModel = get_class($model);
	}
}
