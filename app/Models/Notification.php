<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'seller_id',
        'advert_id',
        'type',
        'message',
        'is_read',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Seller who triggered notification
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    // Related advert
    public function advert()
    {
        return $this->belongsTo(Advert::class);
    }
}

