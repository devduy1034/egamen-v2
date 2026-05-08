<?php


namespace LARAVELPermission\Repositories;
use LARAVEL\Core\Exceptions\LARAVELExceptions;
abstract class Repository
{
    public function findByName($name)
    {
        return $this->model->where('name', $name)->first();
    }
    protected function guardForUniqueName($name)
    {
        $item = $this->findByName($name);
        if( !is_null($item) ) {
            throw new LARAVELExceptions('The name '. $name .' is already exist.');
        }
    }

    /**
     * @throws LARAVELExceptions
     */
    public function create(string $name)
    {
        $this->guardForUniqueName($name);
        $this->model->name = $name;
        $this->model->save();
        return $this->model;
    }

    public function deleteByName(string $name)
    {
        $item = $this->model->where('name', $name)->first();
        return $item->delete();
    }

    public function deleteById(int $id)
    {
        $item = $this->model->find($id);
        return $item->delete();
    }

    public function update($name, $id)
    {
        $item = $this->model->findOrFail($id);
        $item->name = $name;
        $item->save();
        return $item;
    }

    public function fetchSet()
    {
        $this->setModel(
            $this->model->first()
        );
    }

    public function __call($method, $args)
    {
        return $this->setModel(call_user_func_array([$this->model, $method], $args));
    }

    public function setModel($model)
    {
        $this->model = $model;
        return $this;
    }

    public function getModel(){
        return $this->model;
    }
}