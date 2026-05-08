<?php



namespace LARAVEL\Models;

use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;
use LARAVEL\Traits\TraitAttr;


class NewsCatModel extends Model
{
    use HasFactory,TraitAttr;
    protected $guarded = [];
    protected $table = 'news_cat';
    public function getItems($select = ['*'])
    {
        return $this->hasMany(NewsModel::class,'id_cat')
            ->select($select)
            ->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])
            ->orderBy('numb', 'desc')
            ->orderBy('id', 'desc');
    }
    public function getCategoryList(): \LARAVEL\DatabaseCore\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(NewsListModel::class,'id_list','id');
    }
    public function getCategoryItems(): \LARAVEL\DatabaseCore\Eloquent\Relations\hasMany
    {
        return $this->hasMany(NewsItemModel::class,'id_cat');
    }
    public function getCategorySubs(): \LARAVEL\DatabaseCore\Eloquent\Relations\hasMany
    {
        return $this->hasMany(NewsSubModel::class,'id_cat');
    }
}
