<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Claim extends Model
{
    protected $fillable = [
        'user_id',
        'order_id',
        'subject',
        'description',
        'status', // 'pending', 'in_progress', 'resolved', 'rejected'
        'admin_response',
        'response_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}