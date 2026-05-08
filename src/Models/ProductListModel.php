<?php



namespace LARAVEL\Models;

use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;
use LARAVEL\Traits\TraitAttr;

class ProductListModel extends Model
{
    use HasFactory,TraitAttr;
    protected $guarded = [];

    protected $lang;
    protected $table = 'product_list';

    public function __construct()
    {
        parent::__construct();
        $this->lang = session()->get('locale') ?? config('app.lang_default');
    }
    
    public function getItems($select = [])
    {
        return $this->hasMany(ProductModel::class,'id_list')->select(['id_list','name'.$this->lang,...$select])->whereRaw("FIND_IN_SET(?,status)", ['hienthi']); 
    }
    public function getCategoryCats(): \LARAVEL\DatabaseCore\Eloquent\Relations\hasMany
    {
        return $this->hasMany(ProductCatModel::class,'id_list');
    }
    public function getCategoryItems(): \LARAVEL\DatabaseCore\Eloquent\Relations\hasMany
    {
        return $this->hasMany(ProductItemModel::class,'id_list');
    }
    public function getCategorySubs(): \LARAVEL\DatabaseCore\Eloquent\Relations\hasMany
    {
        return $this->hasMany(ProductSubModel::class,'id_list');
    }
}