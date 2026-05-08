<?php



namespace LARAVEL\Models;
use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;
use LARAVEL\Traits\TraitAttr;

class TagsModel extends Model
{
    use HasFactory;
    use TraitAttr;
    protected $guarded = [];
    protected $table = 'tags';
    public function products(): \LARAVEL\DatabaseCore\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(ProductModel::class, 'product_tags', 'id_tags', 'id_parent');
    }
    public function news(): \LARAVEL\DatabaseCore\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(NewsModel::class, 'news_tags', 'id_tags', 'id_parent');
    }
}