<?php


namespace LARAVELPermission\Repositories;
use LARAVELPermission\Models\Permission;

class PermissionRepository extends Repository
{
    /**
     * @var Permission
     */
    protected $model;
    public function __construct()
    {
        $this->model = new Permission();
    }
    public function assignRole($role)
    {
        $this->fetchSet();
        $this->getModel()->roles()->attach($role);
        return $this->getModel();
    }

    public function allowTo($user)
    {
        $this->fetchSet();
        $this->getModel()->users()->attach($user);
        return $this->getModel();
    }

    public function terminateToUser($user)
    {
        $this->fetchSet();
        $this->getModel()->users()->detach($user);
        return $this->getModel();
    }

    public function terminateToRole($role)
    {
        $this->fetchSet();
        $this->getModel()->roles()->detach($role);
        return $this->getModel();
    }

    public function getAllUsers()
    {
        $this->fetchSet();
        return $this->getModel()->users;
    }
}