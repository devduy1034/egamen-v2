<?php



namespace LARAVEL\Models;
use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;
use LARAVEL\Traits\TraitAttr;

class ProductSubModel extends Model
{
    use HasFactory,TraitAttr;
    protected $guarded = [];
    protected $table = 'product_sub';

    protected $lang;

    public function __construct()
    {
        parent::__construct();
        $this->lang = session()->get('locale') ?? config('app.lang_default');
    }
    public function getItems($select = [])
    {
        return $this->hasMany(ProductModel::class,'id_sub')
            ->select(['id','id_list','name'.$this->lang,'regular_price', 'sale_price', 'discount', 'photo',...$select])
            ->whereRaw("FIND_IN_SET(?,status)", ['hienthi']);
    }
    public function getCategoryList(): \LARAVEL\DatabaseCore\Eloquent\Relations\belongsTo
    {
        return $this->belongsTo(ProductListModel::class,'id_list','id');
    }
    public function getCategoryCat(): \LARAVEL\DatabaseCore\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductCatModel::class,'id_cat','id');
    }
    public function getCategoryItem(): \LARAVEL\DatabaseCore\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductItemModel::class,'id_item','id');
    }
}