<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    protected $fillable = [
        'warehouse_id',
        'product_id',
        'product_stock_id',
        'quantity',
        'movement_type',
        'movement_reason',
        'related_id',
        'user_id'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'created_at' => 'datetime'
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function productStock(): BelongsTo
    {
        return $this->belongsTo(ProductStock::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getRelatedModelAttribute()
    {
        if (!$this->related_id) {
            return null;
        }

        return match ($this->movement_reason) {
            'order', 'cancel' => Order::find($this->related_id),
            'transfer' => WarehouseTransfer::find($this->related_id),
            default => null,
        };
    }

    public function getRelatedUrlAttribute()
    {
        if (!$this->related_id) {
            return null;
        }

        return match ($this->movement_reason) {
            'order', 'cancel' => route('order.view', $this->related_id),
            // 'transfer' => route('transfer.view', $this->related_id),
            default => null,
        };
    }

    public function getMovementTypeLabelAttribute()
    {
        return match ($this->movement_type) {
            'in' => __('In'),
            'out' => __('Out'),
            default => $this->movement_type,
        };
    }

    public function getMovementReasonLabelAttribute()
    {
        return match ($this->movement_reason) {
            'manual' => __('Manual'),
            'order' => __('Order'),
            'transfer' => __('Transfer'),
            'cancel' => __('Cancel'),
            'refund' => __('Refund'),
            default => $this->movement_reason,
        };
    }
} 