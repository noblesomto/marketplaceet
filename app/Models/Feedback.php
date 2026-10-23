<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'seller_id',
        'rating',
        'satisfaction',
        'reliable',
        'friendly',
        'message'
    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller');
    }
}
