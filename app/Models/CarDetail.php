<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'car_id',
        'cat_id',
        'ad_id',
        'brand_id',
        'model',
        'mileage',
        'condition',
        'registration',
        'registration_year',
        'fuel',
        'transmission',
        'vehicle_type',
        'doors',
        'exterior_color',
        'material_interior',
        'exterior_equipment',
        'interior',
        'security',
    ];

    public function advert()
    {
        return $this->belongsTo(Advert::class, 'advert_id');
    }
}
