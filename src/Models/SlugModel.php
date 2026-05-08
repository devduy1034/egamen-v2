<?php



namespace LARAVEL\Models;

use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;
use LARAVEL\Traits\TraitAttr;

class SlugModel extends Model
{
    use HasFactory,TraitAttr;
    public $timestamps = false;
    protected $guarded = [];
    protected $table = 'slug';
    public function getStatus($model) {
	    return $this->belongsTo($model,'id_parent','id')
            ->select('id')
            ->whereRaw("FIND_IN_SET(?, status)", ['hienthi']);
	}
}
