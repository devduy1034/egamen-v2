<?php



namespace LARAVEL\Models;
use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;
use LARAVEL\Models\CityModel;
class OrdersModel extends Model
{
    use HasFactory;
    use \LARAVEL\DatabaseCore\Relations\HasJsonRelationships;
    public $timestamps = false;
    protected $guarded = [];
    protected $table = 'orders';
    protected $casts = ['info_user' => 'json','order_detail'=>'json'];

    public function getStatus(): \LARAVEL\DatabaseCore\Eloquent\Relations\BelongsTo {
        return $this->belongsTo('LARAVEL\Models\OrderStatusModel', 'order_status');
    }
    public function getPayment(): \LARAVEL\DatabaseCore\Eloquent\Relations\BelongsTo {
        return $this->belongsTo('LARAVEL\Models\NewsModel', 'order_payment');
    }
    public function getMember() {
        //return $this->belongsTo('\LARAVEL\Models\Member', 'id_user');
    }
    public function city()
    {
        return $this->belongsTo(CityModel::class, 'info_user->city');
    }
}