<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'user_id',
        'phone',
        'status'
    ];

    protected $appends = ['address', 'name'];

    public function incharge()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function users()
    {
        return $this->hasMany(User::class, 'user_id', 'id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function warehouseLanguages()
    {
        return $this->hasMany(WarehouseLanguage::class);
    }

    public function getTranslation($field, $lang = 'en')
    {
        $warehouse_translation = $this->hasMany(WarehouseLanguage::class)->where('lang', $lang)->first();

        if (blank($warehouse_translation)):
            $warehouse_translation = $this->hasMany(WarehouseLanguage::class)->where('lang', 'en')->first();
        endif;
        return $warehouse_translation->$field;
    }

    public function getTranslateAttribute()
    {
        $warehouse = $this->warehouseLanguages()->where('warehouse_id', $this->id)->where('lang', app()->getLocale())->first();
        if (!$warehouse)
            $warehouse = $this->warehouseLanguages()->where('warehouse_id', $this->id)->where('lang', 'en')->first();

        return $warehouse;
    }

    public function getAddressAttribute()
    {
        return $this->translate->address;
    }

    public function getNameAttribute()
    {
        return $this->translate->name;
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'warehouse_products')
            ->withPivot('quantity', 'shelf_number', 'column_number')
            ->withTimestamps();
    }

    public function warehouseProducts()
    {
        return $this->hasMany(WarehouseProduct::class);
    }
} 