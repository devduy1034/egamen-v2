<?php
namespace LARAVEL\DatabaseCore\Eloquent\Relations;

use LARAVEL\DatabaseCore\Eloquent\Model;
use LARAVEL\DatabaseCore\Eloquent\Relations\Concerns\AsPivot;

class Pivot extends Model
{
    use AsPivot;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [];
}
