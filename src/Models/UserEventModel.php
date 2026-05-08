<?php

namespace LARAVEL\Models;

use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;

class UserEventModel extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'user_events';

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'json',
    ];
}
