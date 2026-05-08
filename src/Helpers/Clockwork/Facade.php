<?php

 namespace LARAVEL\Helpers\Clockwork;

use LARAVEL\Core\Support\Facades\Facade as FacadesFacade;

// Clockwork facade
class Facade extends FacadesFacade
{
	protected static function getFacadeAccessor() { return 'clockwork'; }
}
