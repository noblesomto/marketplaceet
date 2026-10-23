<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhoneDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'ad_id',
        'cat_id',
        'brand_id',
        'phone_id',
        'model',
        'color',
        'device',
        'condition',
    ];

    public function advert()
    {
        return $this->belongsTo(Advert::class, 'advert_id');
    }
}
