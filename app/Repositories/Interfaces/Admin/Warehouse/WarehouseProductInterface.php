<?php

namespace App\Repositories\Interfaces\Admin\Warehouse;

interface WarehouseProductInterface
{
    public function all();
    public function get($id);
    public function store($request);
    public function update($request, $id);
    public function delete($id);
} 