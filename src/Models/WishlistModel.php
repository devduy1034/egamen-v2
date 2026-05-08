<?php

namespace LARAVEL\Models;

use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;

class WishlistModel extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $table = 'wishlists';
    protected $guarded = [];
}
