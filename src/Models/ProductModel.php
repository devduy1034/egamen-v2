<?php



namespace LARAVEL\Models;
use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;
use LARAVEL\Traits\TraitAttr;
use LARAVEL\Traits\FullTextSearch;

class ProductModel extends Model
{
    use HasFactory,TraitAttr, FullTextSearch;
    protected $guarded = [];
    protected $table = 'product';
    protected $searchable = [
        'namevi'
    ];
    public function getBrand(): \LARAVEL\DatabaseCore\Eloquent\Relations\belongsTo    {        return $this->belongsTo(ProductBrandModel::class,'id_brand','id');    }
    public function getNews(): \LARAVEL\DatabaseCore\Eloquent\Relations\belongsTo
    {
        return $this->belongsTo(NewsModel::class,'id','id_parent');
    }
    public function getCategoryList(): \LARAVEL\DatabaseCore\Eloquent\Relations\belongsTo    {        return $this->belongsTo(ProductListModel::class,'id_list','id');    }
    public function getCategoryCat(): \LARAVEL\DatabaseCore\Eloquent\Relations\belongsTo    {        return $this->belongsTo(ProductCatModel::class,'id_cat','id');    }
    public function getCategoryItem(): \LARAVEL\DatabaseCore\Eloquent\Relations\belongsTo    {        return $this->belongsTo(ProductItemModel::class,'id_item','id');    }
    public function getCategorySub(): \LARAVEL\DatabaseCore\Eloquent\Relations\belongsTo    {        return $this->belongsTo(ProductSubModel::class,'id_sub','id');    }
    public function tags(): \LARAVEL\DatabaseCore\Eloquent\Relations\BelongsToMany    {        return $this->belongsToMany(TagsModel::class, 'product_tags', 'id_parent', 'id_tags');    }

    public function getComment(): \LARAVEL\DatabaseCore\Eloquent\Relations\hasMany
    {
        return $this->hasMany(CommentModel::class, 'id_variant', 'id')->where("id_parent", 0)->whereRaw("FIND_IN_SET(?,status)", ['hienthi']);
    }


}