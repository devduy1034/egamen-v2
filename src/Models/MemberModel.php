<?php

namespace LARAVEL\Models;

use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;

class MemberModel extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'member';
    protected $guarded = [];
    protected $hidden = [
        'password',
    ];
}
