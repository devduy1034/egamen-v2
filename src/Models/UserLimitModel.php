<?php



namespace LARAVEL\Models;
use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;

class UserLimitModel extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $table = 'user_limit';
    protected $guarded = [];
}