<?php



namespace LARAVEL\Models;
use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;
use LARAVEL\Traits\TraitAttr;

class LinkModel extends Model
{
    use HasFactory,TraitAttr;
    protected $guarded = [];
    protected $table = 'link';
}