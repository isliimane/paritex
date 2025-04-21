<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturnRequest extends Model
{
    protected $fillable = [
        'user_id', 'order_id', 'reason', 'status', 
        'is_product_unused', 'purchase_date',
        'resolution_type', 'admin_notes', 'processed_at'
    ];
    protected $dates = ['processed_at'];

public function scopePending($query) {
    return $query->where('status', 'pending');
}
    
    public function user() {
        return $this->belongsTo(User::class);
    }
    public function order() {
        return $this->belongsTo(Order::class);
    }
    public function getStatusColorAttribute()
{
    return match($this->status) {
        'pending' => 'warning',
        'approved' => 'success',
        'rejected' => 'danger',
        default => 'secondary'
    };
}
}
