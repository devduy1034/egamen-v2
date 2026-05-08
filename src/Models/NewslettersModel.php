<?php



namespace LARAVEL\Models;

use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;


class NewslettersModel extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'newsletter';
}
