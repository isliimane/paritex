<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CategoryRange extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'min_price',
        'max_price',
        'multiplier',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
