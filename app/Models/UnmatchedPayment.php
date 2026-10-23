<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnmatchedPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'transaction_id',
        'amount',
        'customer_email',
        'paid_at',
        'status',
        'resolved_boost_id',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function boost()
    {
        return $this->belongsTo(AdvertBoost::class, 'resolved_boost_id');
    }
}
