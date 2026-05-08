<?php


namespace LARAVEL\Cart\Model;
use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;

/**
 * @method static create(array $dataOrder)
 */
class CartModel extends Model
{
    use HasFactory;
    public $table = 'orders';
    protected $casts = [
        'info_user' => 'json',
        'order_detail' => 'json',
    ];
    protected $fillable = [
        'info_user',
        'id_user',
        'order_payment',
        'temp_price',
        'total_price',
        'code',
        'ship_price',
        'requirements',
        'notes',
        'numb',
        'order_status',
        'order_detail',
    ];

}
