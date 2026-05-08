<?php


namespace LARAVEL\Models;
use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;
use LARAVEL\Traits\TraitAttr;

class NewsModel extends Model
{
    use HasFactory,TraitAttr;
    protected $guarded = [];
    protected $table = 'news';
    public function getCategoryList(): \LARAVEL\DatabaseCore\Eloquent\Relations\belongsTo
    {
        return $this->belongsTo(NewsListModel::class,'id_list','id');
    }
    public function getCategoryCat(): \LARAVEL\DatabaseCore\Eloquent\Relations\belongsTo
    {
        return $this->belongsTo(NewsCatModel::class,'id_cat','id');
    }
    public function getCategoryItem(): \LARAVEL\DatabaseCore\Eloquent\Relations\belongsTo
    {
        return $this->belongsTo(NewsItemModel::class,'id_item','id');
    }
    public function getCategorySub(): \LARAVEL\DatabaseCore\Eloquent\Relations\belongsTo
    {
        return $this->belongsTo(NewsSubModel::class,'id_sub','id');
    }
    public function tags(): \LARAVEL\DatabaseCore\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(TagsModel::class, 'news_tags', 'id_parent', 'id_tags');
    }
}
