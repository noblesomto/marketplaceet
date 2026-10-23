<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;

class Category extends Model
{
    use HasFactory;
    use Sluggable;
    protected $fillable = [
        'cat_id',
        'category',
        'seo_group',
        'icon',
        'keywords',
        'meta_title',
        'meta_description',
    ];

    public function sluggable(): array
    {
        return [
            'category_slug' => [
                'source' => 'category'
            ]
        ];
    }

    public function subCategories()
    {
        return $this->hasMany(SubCategory::class, 'cat_id');
    }
}
