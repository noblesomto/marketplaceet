<?php

namespace App\Support;

use App\Models\Brands;
use App\Models\VehicleModel;
use Illuminate\Validation\Rule;

/**
 * Model handling for Mobile Phones (phone_details.model) and Laptops &
 * Computers (computer_details.model), both → models.id.
 *
 * A brand with models (e.g. Apple → iPhone 12 Pro Max, HP → EliteBook)
 * requires one on the web forms, with an "other" escape for devices not in
 * the list, which is stored as no model. Brands without models never ask for
 * one. The mobile app doesn't send a model yet, so the API infers it from the
 * title instead.
 */
class DeviceModels
{
    /** subcategory id => detail relation on Advert holding the model */
    public const SUBCATEGORIES = [
        6  => 'phone',
        27 => 'computer',
    ];

    public const PHONES_SUBCATEGORY_ID = 6;

    public const COMPUTERS_SUBCATEGORY_ID = 27;

    public const OTHER = 'other';

    /** Series word sellers often leave out, per subcategory and brand: "Samsung A14" is the Galaxy A14. */
    private const SERIES_PREFIX = [
        6 => [
            'apple'   => 'iPhone',
            'samsung' => 'Galaxy',
        ],
    ];

    /** Other ways sellers write a brand's model names (regex => replacement), per subcategory. */
    private const ALIASES = [
        6 => [
            'samsung' => ['/\bZ (Fold|Flip)\b/i' => '$1'], // "Galaxy Fold 4" for the Galaxy Z Fold 4
        ],
        27 => [
            'hp' => [
                '/^EliteBook$/'   => 'Elite x360', // "HP Elite x360 1040 G9" is an EliteBook
                '/^Elite(Book)$/' => 'Elite $1',   // "Hp elite book"
                '/^Pavilion$/'    => 'Pavillion',
            ],
            'apple' => ['/^MacBook/' => 'Mac Book'],
            'acer'  => ['/^TravelMate$/' => 'Travel Mate'],
        ],
    ];

    /**
     * Words that make a different model, so "iPhone 12 Pro" never counts as an
     * iPhone 12. Laptop models are whole product lines (EliteBook covers every
     * EliteBook), so nothing after the name changes the model.
     */
    private const VARIANT_WORDS = [
        27 => '',
    ];

    /** @var array<int, array<int, string>> brand id => [model id => name] */
    private static array $cache = [];

    public static function isDeviceSubcategory($subcategoryId): bool
    {
        return isset(self::SUBCATEGORIES[(int) $subcategoryId]);
    }

    /**
     * @return array<int, string> model id => model name
     */
    public static function forBrand($brandId): array
    {
        if (!is_numeric($brandId)) {
            return [];
        }

        return self::$cache[(int) $brandId] ??= VehicleModel::where('brand_id', (int) $brandId)
            ->orderBy('id')
            ->pluck('model', 'id')
            ->all();
    }

    public static function rule($brandId, bool $required = true): array|string
    {
        $ids = array_keys(self::forBrand($brandId));

        if (!$ids) {
            return 'nullable';
        }

        return [
            $required ? 'required' : 'nullable',
            Rule::in(array_merge(array_map('strval', $ids), [self::OTHER])),
        ];
    }

    /**
     * The model id to store: the submitted one if it belongs to the brand,
     * otherwise (when a title is given and the seller didn't pick "other")
     * a model inferred from the title, otherwise none.
     */
    public static function resolve($brandId, $input, ?string $title = null): ?int
    {
        $models = self::forBrand($brandId);

        if (is_numeric($input) && isset($models[(int) $input])) {
            return (int) $input;
        }

        if ($title !== null && $input !== self::OTHER && $models) {
            return self::matcher($brandId)?->match($title);
        }

        return null;
    }

    /**
     * A title matcher for a brand's models, or null when it has none.
     */
    public static function matcher($brandId): ?DeviceModelMatcher
    {
        $models = self::forBrand($brandId);
        $brand = $models ? Brands::find((int) $brandId) : null;

        return $brand ? self::matcherFor($models, (int) $brand->subcat_id, $brand->brand) : null;
    }

    /**
     * A title matcher for the given models, with the matching rules for that
     * subcategory and brand name (series word, aliases, variant words).
     *
     * @param array<int, string> $models model id => model name
     */
    public static function matcherFor(array $models, int $subcategoryId, string $brandName): DeviceModelMatcher
    {
        $key = strtolower($brandName);

        return new DeviceModelMatcher(
            $models,
            self::SERIES_PREFIX[$subcategoryId][$key] ?? null,
            [$key],
            self::ALIASES[$subcategoryId][$key] ?? [],
            self::VARIANT_WORDS[$subcategoryId] ?? null,
        );
    }

    /**
     * How a client should render the Model field for a brand — shown and
     * required whenever the brand has models; phones and computers also get
     * the "other" escape. (The API itself still accepts a missing model and
     * infers it from the title, so older app versions keep working.)
     */
    public static function fieldFor(?Brands $brand): array
    {
        $hasModels = $brand && self::forBrand($brand->id);

        return [
            'show'         => (bool) $hasModels,
            'required'     => (bool) $hasModels,
            'other_option' => $hasModels && self::isDeviceSubcategory($brand->subcat_id)
                ? ['value' => self::OTHER, 'label' => 'Other / not listed']
                : null,
        ];
    }

    public static function flush(): void
    {
        self::$cache = [];
    }
}
