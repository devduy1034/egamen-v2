<?php



namespace LARAVEL\Models;

use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;
use LARAVEL\Traits\TraitAttr;

class ProductBrandModel extends Model
{
    use HasFactory,TraitAttr;
    protected $guarded = [];
    protected $table = 'product_brand';
    public function getItems($select = ['*'])
    {
        return $this->hasMany(ProductModel::class,'id_brand')
            ->select($select)
            ->whereRaw("FIND_IN_SET(?,status)", ['hienthi'])
            ->orderBy('numb', 'desc')
            ->orderBy('id', 'desc');
    }
}