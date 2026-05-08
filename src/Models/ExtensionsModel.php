<?php



namespace LARAVEL\Models;

use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;


class ExtensionsModel extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'extensions';
}
