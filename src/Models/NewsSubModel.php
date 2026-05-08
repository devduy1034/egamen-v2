<?php



namespace LARAVEL\Models;

use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;
use LARAVEL\Traits\TraitAttr;


class NewsSubModel extends Model
{
    use HasFactory,TraitAttr;
    protected $guarded = [];
    protected $table = 'news_sub';
    public function getItems($select = ['*'])
    {
        return $this->hasMany(NewsModel::class,'id_sub')
            ->select($select)
            ->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])
            ->orderBy('numb', 'desc')
            ->orderBy('id', 'desc');
    }
    public function getCategoryList(): \LARAVEL\DatabaseCore\Eloquent\Relations\belongsTo
    {
        return $this->belongsTo(NewsListModel::class,'id_list','id');
    }
    public function getCategoryCat(): \LARAVEL\DatabaseCore\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(NewsCatModel::class,'id_cat','id');
    }
    public function getCategoryItem(): \LARAVEL\DatabaseCore\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(NewsItemModel::class,'id_item','id');
    }
}
