<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class BlockedUser extends Model
{
    protected $fillable = ['blocker_id', 'blocked_id', 'advert_id'];

    public function blocker()
    {
        return $this->belongsTo(User::class, 'blocker_id', 'user_id');
    }

    public function blocked()
    {
        return $this->belongsTo(User::class, 'blocked_id', 'user_id');
    }

    public function advert()
    {
        return $this->belongsTo(Advert::class);
    }
}
