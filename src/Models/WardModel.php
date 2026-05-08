<?php



namespace LARAVEL\Models;
use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;

class WardModel extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'ward';
}