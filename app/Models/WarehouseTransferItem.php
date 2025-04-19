<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WarehouseTransferItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transfer_id',
        'product_id',
        'product_stock_id',
        'quantity'
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function transfer()
    {
        return $this->belongsTo(WarehouseTransfer::class,'transfer_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productStock()
    {
        return $this->belongsTo(ProductStock::class);
    }
} 