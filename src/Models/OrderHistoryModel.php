<?php



namespace LARAVEL\Models;

use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;


class OrderHistoryModel extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'order_history';
}
