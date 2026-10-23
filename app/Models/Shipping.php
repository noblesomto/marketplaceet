<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shipping extends Model
{
    use HasFactory;
    protected $fillable = [
        'ship_id',
        'username',
        'password',
        'company',
        'weight',
        'description',
        'price',
        'logo',
    ];

    protected $hidden = [
        'username',
        'password',
        'show_password',
    ];

    public function adverts()
    {
        return $this->belongsToMany(Advert::class, 'advert_shipping');
    }

}
