<?php



namespace LARAVEL\Models\Place;
use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;

class WardModel extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $guarded = [];
    protected $table = 'ward';
    public function getCity(): \LARAVEL\DatabaseCore\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('LARAVEL\Models\Place\CityModel', 'id_city');
    }
    public function getDistrict(): \LARAVEL\DatabaseCore\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('LARAVEL\Models\Place\CityModel', 'id_district');
    }
}