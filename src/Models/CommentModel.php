<?php



namespace LARAVEL\Models;
use LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;
use LARAVEL\DatabaseCore\Eloquent\Model;
use LARAVEL\Traits\TraitAttr;

class CommentModel extends Model
{
    use HasFactory,TraitAttr;
    protected $guarded = [];
    protected $table = 'comment';

    public function replies(): \LARAVEL\DatabaseCore\Eloquent\Relations\hasMany
    {
        return $this->hasMany(CommentModel::class, 'id_parent', 'id');
    }

    public function getReplies(): \LARAVEL\DatabaseCore\Eloquent\Relations\hasMany
    {
        return $this->hasMany(CommentModel::class, 'id_parent', 'id')->where("id_parent", '<>', 0)->whereRaw("FIND_IN_SET(?,status)", ['hienthi']);
    }

}