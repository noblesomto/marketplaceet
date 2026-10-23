<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GigLogistic extends Model
{
    use HasFactory;
    protected $fillable = [
        'state_id',
        'city',
        'slug',
        'address'
    ];

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }
}
