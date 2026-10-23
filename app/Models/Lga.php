<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lga extends Model
{
    protected $fillable = ['state_id', 'name', 'slug'];

    public function state()
    {
        return $this->belongsTo(State::class);
    }
}
