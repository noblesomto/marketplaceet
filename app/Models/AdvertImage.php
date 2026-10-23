<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvertImage extends Model
{
    use HasFactory;
    protected $fillable = [
        'ad_id',
        'image',
        'position',
    ];

    public function advert()
    {
        return $this->belongsTo(Advert::class);
    }

/**
    public function getImageAttribute($value)
    {
        $fullPath = public_path('uploads/images/' . $value);

        if (file_exists($fullPath)) {
            return $value; // Stored filename works
        }

        // If original missing, try .webp
        $filenameWithoutExt = pathinfo($value, PATHINFO_FILENAME);
        $webpName = $filenameWithoutExt . '.webp';

        if (file_exists(public_path('uploads/images/' . $webpName))) {
            return $webpName;
        }

        // Fallback to default image
        return 'default.png';
    }
**/

    
}
