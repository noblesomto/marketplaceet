<?php

namespace App\Models;

use App\Support\ComputerSpecs;
use App\Support\DeviceModels;
use Illuminate\Database\Eloquent\Model;

/**
 * Laptops & Computers details (subcategory 27) — the model plus specs
 * (processor, RAM, storage, storage type; see ComputerSpecs).
 */
class ComputerDetail extends Model
{
    protected $fillable = [
        'advert_id',
        'brand_id',
        'model',
        'processor',
        'ram',
        'storage',
        'storage_type',
    ];

    public function advert()
    {
        return $this->belongsTo(Advert::class, 'advert_id');
    }

    /**
     * Create or update an advert's computer details. $modelInput is what the
     * form/app sent (a model id, "other", or nothing); with a $title, a
     * missing model is inferred from it (see DeviceModels::resolve). $specs
     * are the submitted spec fields; any left out are read from the title
     * and description (see ComputerSpecs::resolve).
     */
    public static function syncFor(Advert $advert, $brandId, $modelInput, ?string $title = null, array $specs = []): self
    {
        $text = trim(($title ?? $advert->ad_title) . "\n" . $advert->description);

        return self::updateOrCreate(
            ['advert_id' => $advert->id],
            ['brand_id' => $brandId, 'model' => DeviceModels::resolve($brandId, $modelInput, $title)]
                + ComputerSpecs::resolve($specs, $text),
        );
    }

    /** Spec rows to show on the listing, label => value (only the ones set). */
    public function specLines(): array
    {
        return array_filter([
            'Processor' => $this->processor,
            'RAM'       => $this->ram,
            'Storage'   => ComputerSpecs::storageLabel($this->storage, $this->storage_type),
        ]);
    }
}
