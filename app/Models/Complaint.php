<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = ['name','email','subject','message','reply'];
    //a ajouter
    // 'user_id',
    // 'status', // 'pending', 'in_progress', 'resolved', 'rejected'
    // public function user()
    // {
    //     return $this->belongsTo(User::class);
    // }
}
