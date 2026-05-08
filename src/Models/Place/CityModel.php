<?php



namespace LARAVEL\Models\Place;
use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;

class CityModel extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $guarded = [];
    protected $table = 'city';

    public function getDistrict(): \LARAVEL\DatabaseCore\Eloquent\Relations\HasMany
    {
        return $this->hasMany('LARAVEL\Models\Place\DistrictModel','id_city');
    }
    public function getWard(): \LARAVEL\DatabaseCore\Eloquent\Relations\HasMany
    {
        return $this->hasMany('LARAVEL\Models\Place\WardModel','id_city');
    }
}