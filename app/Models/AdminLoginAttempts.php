<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminLoginAttempts extends Model
{
    protected $fillable = ['email', 'ip_address', 'user_agent', 'successful'];
}
