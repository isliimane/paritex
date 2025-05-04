<?php

namespace App\Repositories\Admin\Warehouse;

use App\Models\WarehouseProduct;
use App\Repositories\Interfaces\Admin\Warehouse\WarehouseProductInterface;

class WarehouseProductRepository implements WarehouseProductInterface
{
    public function all()
    {
        return WarehouseProduct::all();
    }

    public function get($id)
    {
        return WarehouseProduct::find($id);
    }

    public function store($request)
    {
        return WarehouseProduct::create($request);
    }

    public function update($request, $id)
    {
        $warehouseProduct = WarehouseProduct::find($id);
        if ($warehouseProduct) {
            $warehouseProduct->update($request);
            return $warehouseProduct;
        }
        return null;
    }

    public function delete($id)
    {
        $warehouseProduct = WarehouseProduct::find($id);
        if ($warehouseProduct) {
            $warehouseProduct->delete();
            return true;
        }
        return false;
    }
} 