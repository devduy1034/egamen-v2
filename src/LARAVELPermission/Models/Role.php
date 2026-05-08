<?php



namespace LARAVELPermission\Models;
use LARAVEL\DatabaseCore\Eloquent\Model;
use LARAVEL\Models\UserModel;

/**
 * @method static create(array $data)
 */
class Role extends Model
{
    protected $fillable = [
        'name',
        'numb',
        'status'
    ];
    public function users(): \LARAVEL\DatabaseCore\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(UserModel::class,'user_has_roles');
    }

    public function permissions(): \LARAVEL\DatabaseCore\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Permission::class,'role_has_permissions');
    }
}