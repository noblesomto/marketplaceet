<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Cviebrock\EloquentSluggable\Sluggable;
use App\Support\TaxonomySlugDisambiguator;

class VehicleModel extends Model
{
    use HasFactory;
    use Sluggable;

    protected $table = 'models';

    protected $fillable = [
        'cat_id',
        'subcat_id',
        'brand_id',
        'model_id',
        'model',
        'keywords',
        'meta_title',
        'meta_description',
    ];

    public function sluggable(): array
    {
        return [
            'model_slug' => [
                'source' => 'model',
                'uniqueSuffix' => fn ($slug, $separator, $list, $firstSuffix) =>
                    TaxonomySlugDisambiguator::suffix($slug, $separator, $list, $firstSuffix, $this->brand?->brand_slug),
            ],
        ];
    }

    public function brand()
    {
        return $this->belongsTo(Brands::class, 'brand_id');
    }
}
