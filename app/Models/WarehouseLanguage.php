<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseLanguage extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_id',
        'name',
        'address',
        'lang'
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }
} 