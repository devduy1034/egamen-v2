<?php


namespace LARAVELPermission\Models;
use LARAVEL\DatabaseCore\Eloquent\Model;
use LARAVEL\Models\UserModel;

class Permission extends Model
{
    protected $fillable = [
        'name',
    ];
    public function users()
    {
        return $this->belongsToMany(UserModel::class,'user_has_permissions');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_has_permissions');
    }

    public function scopeAssignRole($role)
    {
        return $this->roles()->attach($role);
    }
}