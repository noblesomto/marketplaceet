<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Shipment extends Model
{
    use HasFactory;
    use Sluggable;
    protected $fillable = [
        'ship_id',
        'cargo_type',
        'company_name',
    ];

    public function sluggable(): array
    {
        return [
            'company_slug' => [
                'source' => 'company_name'
            ]
        ];
    }
}
