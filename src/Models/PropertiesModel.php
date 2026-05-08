<?php



namespace LARAVEL\Models;

use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;

class PropertiesModel extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'properties';
    public function getListProperties(): \LARAVEL\DatabaseCore\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\LARAVEL\Models\PropertiesListModel::class,'id_list','id');
    }
}