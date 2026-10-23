<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use App\Support\TaxonomySlugDisambiguator;

class Brands extends Model
{
    use HasFactory;
    use Sluggable;
    protected $fillable = [
        'cat_id',
        'subcat_id',
        'brand_id',
        'brand',
        'keywords',
        'meta_title',
        'meta_description'
    ];

    public function sluggable(): array
    {
        return [
            'brand_slug' => [
                'source' => 'brand',
                'uniqueSuffix' => fn ($slug, $separator, $list, $firstSuffix) =>
                    TaxonomySlugDisambiguator::suffix($slug, $separator, $list, $firstSuffix, $this->subCategory?->sub_cat_slug),
            ],
        ];
    }

    public function adverts()
    {
        return $this->hasMany(Advert::class, 'brand');
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class, 'subcat_id');
    }

    public function models()
    {
        return $this->hasMany(VehicleModel::class, 'brand_id');
    }
}
