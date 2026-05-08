<?php



namespace LARAVEL\Models;

use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;
use LARAVEL\DatabaseCore\Eloquent\Relations\HasMany;

class PropertiesListModel extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'properties_list';

    public function getProperties(): HasMany
    {
        return $this->hasMany(PropertiesModel::class,'id_list','id');
    }

}