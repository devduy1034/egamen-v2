<?php



namespace LARAVELPermission\Traits;

use LARAVELPermission\Models\Permission;
use LARAVELPermission\Models\Role;

trait HasPermission
{
    public function roles()
    {
        return $this->belongsToMany(Role::class,'user_has_roles','user_id','role_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class,'role_has_permissions');
    }
    public function hasRole($roleName)
    {
        return $this->roles->contains(function ($role) use ($roleName) {
            return $role->name == $roleName;
        });
    }
    public function ableTo($permissionName)
    {
        $rolePermissions = $this->roles->map(function ($role) {
            return $role->permissions;
        })->flatten();
        $permissions = $this->permissions->merge($rolePermissions);
        return $permissions->contains(function ($permission) use ($permissionName) {
            return $permission->name == $permissionName;
        });
    }
    public function allowTo($permission)
    {
        return $this->permissions()->attach($permission);
    }
    public function grantRole($role)
    {
        return $this->roles()->attach($role);
    }
    public function terminateToRole($role)
    {
        return $this->roles()->detach($role);
    }
    public function terminateToPermission($permission)
    {
        return $this->permissions()->detach($permission);
    }
}