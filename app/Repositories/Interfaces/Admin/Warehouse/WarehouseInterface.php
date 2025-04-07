<?php
namespace App\Repositories\Interfaces\Admin\Warehouse;

interface WarehouseInterface
{
    public function store($request);

    public function all();

    public function paginate($limit);

    public function statusChange($request);

    public function get($id);

    public function getByLang($id, $lang);

    public function update($request);

    public function activeWarehouses();

    public function search($query);
} 