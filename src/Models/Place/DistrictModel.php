<?php



namespace LARAVEL\Models\Place;
use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;

class DistrictModel extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $guarded = [];
    protected $table = 'district';
    public function getWard(): \LARAVEL\DatabaseCore\Eloquent\Relations\HasMany
    {
        return $this->hasMany('LARAVEL\Models\Place\WardModel','id_district');
    }
    public function getCity(): \LARAVEL\DatabaseCore\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('LARAVEL\Models\Place\CityModel', 'id_city');
    }
}