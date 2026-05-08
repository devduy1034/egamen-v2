<?php



namespace LARAVEL\Models;

use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;

class NewsTagsModel extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'news_tags';
}