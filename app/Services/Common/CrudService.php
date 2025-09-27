<?php


namespace App\Services\Common;

abstract class CrudService
{
    protected $model;

    public function getAll()
    {
        return $this->model->paginate();
    }
    
}