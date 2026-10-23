<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportUser extends Model
{
    protected $fillable = [
        'reported',
        'reporter',
        'subject',
        'message'
    ];

    public function reported()
    {
        return $this->belongsTo(User::class, 'reported');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter');
    }
}
