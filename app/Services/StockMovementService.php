<?php

namespace App\Services;

use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Order;
use App\Models\WarehouseTransfer;
use Illuminate\Support\Facades\Auth;

class StockMovementService
{
    public static function recordMovement(
        $warehouse_id,
        $product_id,
        $quantity,
        $movementType,
        $movementReason,
        $relatedId = null,
        $product_stock_id = null
    ): StockMovement {
        return StockMovement::create([
            'warehouse_id' => $warehouse_id,
            'product_id' => $product_id,
            'product_stock_id' => $product_stock_id,
            'quantity' => $quantity,
            'movement_type' => $movementType,
            'movement_reason' => $movementReason,
            'related_id' => $relatedId,
            'user_id' => authId()
        ]);
    }

    public static function recordManualStockAddition(
        $warehouse_id,
        $product_id,
        $quantity,
        $product_stock_id = null
    ): StockMovement {
        return self::recordMovement(
            $warehouse_id,
            $product_id,
            $quantity,
            'in',
            'manual',
            null,
            $product_stock_id
        );
    }

    public static function recordOrderStockMovement(
        $order,
        $movementType,
        $product_stock_id = null
    ): StockMovement {
        if (!$order->warehouse_id) {
            throw new \Exception('Order must have a warehouse selected');
        }

        return self::recordMovement(
            $order->warehouse_id,
            $order->product_id,
            $order->quantity,
            $movementType,
            $movementType === 'in' ? 'cancel' : 'order',
            $order->id,
            $product_stock_id
        );
    }

    public static function recordTransferStockMovement(
        $transfer,
        $movementType,
        $product_stock_id = null
    ): StockMovement {
        $warehouse_id = $movementType === 'in' ? $transfer->toWarehouse_id : $transfer->fromWarehouse_id;
        
        return self::recordMovement(
            $warehouse_id,
            $transfer->product_id,
            $transfer->quantity,
            $movementType,
            'transfer',
            $transfer->id,
            $product_stock_id
        );
    }
} 