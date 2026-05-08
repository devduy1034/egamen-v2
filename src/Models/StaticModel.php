<?php



namespace LARAVEL\Models;
use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;
use LARAVEL\Traits\TraitAttr;

class StaticModel extends Model
{
    use HasFactory,TraitAttr;
    protected $guarded = [];
    protected $table = 'static';
}