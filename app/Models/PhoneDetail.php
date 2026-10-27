<?php

namespace App\Models;

use App\Support\PhoneSpecs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhoneDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'ad_id',
        'cat_id',
        'brand_id',
        'phone_id',
        'model',
        'color',
        'device',
        'condition',
        'ram',
        'storage',
    ];

    public function advert()
    {
        return $this->belongsTo(Advert::class, 'advert_id');
    }

    /** Request field for each spec — prefixed like phone_color, since laptops already post ram/storage */
    public const SPEC_INPUTS = ['ram' => 'phone_ram', 'storage' => 'phone_storage'];

    /**
     * RAM and storage to save: the seller's choices from $input (phone_ram,
     * phone_storage), with any left blank read from the listing's title and
     * description (PhoneSpecs). A spec not sent at all (older app) keeps
     * $current's value.
     *
     * @return array{ram: ?string, storage: ?string}
     */
    public static function specsFrom(array $input, ?string $title, ?string $description, ?self $current = null): array
    {
        $chosen = [];
        foreach (self::SPEC_INPUTS as $field => $name) {
            $chosen[$field] = array_key_exists($name, $input) ? $input[$name] : $current?->{$field};
        }

        return PhoneSpecs::resolve($chosen, (string) $title, (string) $description);
    }

    /** Spec rows to show on the listing, label => value (only the ones set). */
    public function specLines(): array
    {
        return array_filter([
            'RAM'     => $this->ram,
            'Storage' => $this->storage,
        ]);
    }
}
