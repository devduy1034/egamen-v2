<?php



namespace LARAVELPermission\Repositories;
use LARAVELPermission\Models\Role;

class RoleRepository extends Repository
{
    /**
     * @var Role
     */
    protected $model;

    public function __construct()
    {
        $this->model = new Role();
    }

    public function grant($permission)
    {
        $this->fetchSet();
        $this->getModel()->permissions()->attach($permission);
        return $this->getModel();
    }

    public function assignRoleTo($user)
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

    public function terminateToPermission($permission)
    {
        $this->fetchSet();
        $this->getModel()->permissions()->detach($permission);
        return $this->getModel();
    }
}