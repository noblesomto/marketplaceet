<?php

namespace App\Console\Commands;

use App\Models\Brands;
use App\Models\VehicleModel;
use App\Services\SlugRedirectResolver;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * Creates one model per phone variant under the big Mobile Phones brands, so
 * each gets its own /category/.../{brand}/{model} page and "Shop by Model"
 * entry. Safe to re-run: models that already exist (by name) are left alone,
 * so new releases can be appended to MODELS and the command run again — or
 * added from the admin Models screen.
 *
 * Names leave out the brand ("Galaxy S21 Ultra", not "Samsung Galaxy S21
 * Ultra") — pages show "{brand} {model}". Names are what DeviceModelMatcher
 * looks for in advert titles, so they follow how sellers write them.
 */
class SeedPhoneModels extends Command
{
    protected $signature = 'phones:seed-models {--brand=* : Only these brands (e.g. --brand=samsung)} {--dry-run}';

    protected $description = 'Create the per-variant phone models for Apple, Samsung, Tecno, Infinix, Xiaomi and Itel';

    private const PHONES_SUBCAT_ID = 6;

    /** brand name => model names, oldest first (the order "Show all models" lists them in). */
    private const MODELS = [
        'Apple' => [
            'iPhone 6', 'iPhone 6 Plus', 'iPhone 6s', 'iPhone 6s Plus', 'iPhone SE',
            'iPhone 7', 'iPhone 7 Plus', 'iPhone 8', 'iPhone 8 Plus',
            'iPhone X', 'iPhone XR', 'iPhone XS', 'iPhone XS Max',
            'iPhone 11', 'iPhone 11 Pro', 'iPhone 11 Pro Max',
            'iPhone 12', 'iPhone 12 mini', 'iPhone 12 Pro', 'iPhone 12 Pro Max',
            'iPhone 13', 'iPhone 13 mini', 'iPhone 13 Pro', 'iPhone 13 Pro Max',
            'iPhone 14', 'iPhone 14 Plus', 'iPhone 14 Pro', 'iPhone 14 Pro Max',
            'iPhone 15', 'iPhone 15 Plus', 'iPhone 15 Pro', 'iPhone 15 Pro Max',
            'iPhone 16', 'iPhone 16 Plus', 'iPhone 16 Pro', 'iPhone 16 Pro Max', 'iPhone 16e',
            'iPhone 17', 'iPhone Air', 'iPhone 17 Pro', 'iPhone 17 Pro Max',
        ],
        'Samsung' => [
            'Galaxy S7', 'Galaxy S7 Edge', 'Galaxy S8', 'Galaxy S8 Plus', 'Galaxy S9', 'Galaxy S9 Plus',
            'Galaxy S10e', 'Galaxy S10', 'Galaxy S10 Plus',
            'Galaxy S20', 'Galaxy S20 FE', 'Galaxy S20 Plus', 'Galaxy S20 Ultra',
            'Galaxy S21', 'Galaxy S21 FE', 'Galaxy S21 Plus', 'Galaxy S21 Ultra',
            'Galaxy S22', 'Galaxy S22 Plus', 'Galaxy S22 Ultra',
            'Galaxy S23', 'Galaxy S23 FE', 'Galaxy S23 Plus', 'Galaxy S23 Ultra',
            'Galaxy S24', 'Galaxy S24 FE', 'Galaxy S24 Plus', 'Galaxy S24 Ultra',
            'Galaxy S25', 'Galaxy S25 Edge', 'Galaxy S25 Plus', 'Galaxy S25 Ultra',
            'Galaxy Note 8', 'Galaxy Note 9', 'Galaxy Note 10', 'Galaxy Note 10 Lite', 'Galaxy Note 10 Plus',
            'Galaxy Note 20', 'Galaxy Note 20 Ultra',
            'Galaxy Z Fold 2', 'Galaxy Z Fold 3', 'Galaxy Z Fold 4', 'Galaxy Z Fold 5', 'Galaxy Z Fold 6', 'Galaxy Z Fold 7',
            'Galaxy Z Flip 3', 'Galaxy Z Flip 4', 'Galaxy Z Flip 5', 'Galaxy Z Flip 6', 'Galaxy Z Flip 7',
            'Galaxy A02', 'Galaxy A03', 'Galaxy A04', 'Galaxy A05', 'Galaxy A05s', 'Galaxy A06', 'Galaxy A07',
            'Galaxy A10', 'Galaxy A10s', 'Galaxy A11', 'Galaxy A12', 'Galaxy A13', 'Galaxy A14', 'Galaxy A15',
            'Galaxy A16', 'Galaxy A17', 'Galaxy A20', 'Galaxy A20s', 'Galaxy A21s', 'Galaxy A22', 'Galaxy A23',
            'Galaxy A24', 'Galaxy A25', 'Galaxy A26', 'Galaxy A30', 'Galaxy A30s', 'Galaxy A31', 'Galaxy A32',
            'Galaxy A33', 'Galaxy A34', 'Galaxy A35', 'Galaxy A36', 'Galaxy A50', 'Galaxy A51', 'Galaxy A52',
            'Galaxy A52s', 'Galaxy A53', 'Galaxy A54', 'Galaxy A55', 'Galaxy A56', 'Galaxy A70', 'Galaxy A71',
            'Galaxy A72', 'Galaxy A73', 'Galaxy A80',
        ],
        'Tecno' => [
            'Camon 12', 'Camon 15', 'Camon 16', 'Camon 16 Premier', 'Camon 17', 'Camon 17 Pro',
            'Camon 18', 'Camon 18P', 'Camon 18 Premier', 'Camon 19', 'Camon 19 Pro',
            'Camon 20', 'Camon 20 Pro', 'Camon 20 Premier', 'Camon 30', 'Camon 30 Pro', 'Camon 30 Premier',
            'Camon 40', 'Camon 40 Pro', 'Camon 40 Premier', 'Camon 50', 'Camon 50 Ultra',
            'Spark 5 Pro', 'Spark 6', 'Spark 7', 'Spark 7 Pro', 'Spark 8', 'Spark 8C', 'Spark 9',
            'Spark 10', 'Spark 10C', 'Spark 10 Pro', 'Spark 20', 'Spark 20C', 'Spark 20 Pro', 'Spark 20 Pro Plus',
            'Spark 30', 'Spark 30C', 'Spark 30 Pro', 'Spark 40', 'Spark 40C', 'Spark 40 Pro', 'Spark 40 Pro Plus',
            'Spark 50', 'Spark Slim',
            'Pop 5', 'Pop 6', 'Pop 7', 'Pop 7 Pro', 'Pop 8', 'Pop 9', 'Pop 10', 'Pop 10 Pro',
            'Pova 5', 'Pova 6 Pro', 'Pova 7', 'Pova Neo', 'Pouvoir 3', 'Pouvoir 4',
            'Phantom X', 'Phantom X2', 'Phantom V Fold', 'Phantom V Flip',
        ],
        'Infinix' => [
            'Hot 8', 'Hot 9', 'Hot 9 Play', 'Hot 10', 'Hot 10i', 'Hot 10 Play', 'Hot 11', 'Hot 11 Play',
            'Hot 12', 'Hot 12 Play', 'Hot 20', 'Hot 20i', 'Hot 30', 'Hot 30i', 'Hot 30 Play',
            'Hot 40', 'Hot 40i', 'Hot 40 Pro', 'Hot 50', 'Hot 50i', 'Hot 50 Pro', 'Hot 50 Pro Plus',
            'Hot 60', 'Hot 60i', 'Hot 60 Pro', 'Hot 60 Pro Plus',
            'Smart 5', 'Smart 6', 'Smart 7', 'Smart 7 HD', 'Smart 7 Plus', 'Smart 8', 'Smart 8 HD',
            'Smart 9', 'Smart 9 HD', 'Smart 10', 'Smart 10 Plus',
            'Note 8', 'Note 8i', 'Note 10', 'Note 11', 'Note 12', 'Note 30', 'Note 30 Pro',
            'Note 40', 'Note 40 Pro', 'Note 50', 'Note 50 Pro', 'Note 60', 'Note 60 Pro',
            'Zero 30', 'Zero 40', 'GT 20 Pro', 'GT 30 Pro',
        ],
        'Xiaomi' => [
            'Redmi 9', 'Redmi 9A', 'Redmi 9AT', 'Redmi 9C', 'Redmi 10', 'Redmi 10A', 'Redmi 10C',
            'Redmi 12', 'Redmi 12C', 'Redmi 13', 'Redmi 13C', 'Redmi 14C', 'Redmi 15', 'Redmi 15C',
            'Redmi A3', 'Redmi A3x', 'Redmi A5', 'Redmi K20 Pro',
            'Redmi Note 8', 'Redmi Note 9', 'Redmi Note 10', 'Redmi Note 10 Pro', 'Redmi Note 11', 'Redmi Note 11 Pro',
            'Redmi Note 12', 'Redmi Note 12 Pro', 'Redmi Note 13', 'Redmi Note 13 Pro', 'Redmi Note 14', 'Redmi Note 14 Pro',
            'Xiaomi 12', 'Xiaomi 12 Pro', 'Xiaomi 13', 'Xiaomi 13T', 'Xiaomi 13T Pro', 'Xiaomi 14', 'Xiaomi 14T Pro',
            'Poco C75', 'Poco M6 Pro', 'Poco F6', 'Poco X6 Pro', 'Poco X7 Pro',
        ],
        'Itel' => [
            'A70', 'A80', 'P55', 'P55 Plus', 'P65', 'P65C', 'P70', 'S23', 'S24', 'S25', 'S25 Ultra', 'City 100',
        ],
    ];

    /** Models created before the naming scheme, renamed (and re-slugged with a redirect) to fit it. */
    private const RENAMES = [
        'Samsung' => ['S20' => 'Galaxy S20', 'S24' => 'Galaxy S24'],
    ];

    public function handle(SlugRedirectResolver $redirects): int
    {
        $only = array_map('strtolower', $this->option('brand'));
        $dryRun = (bool) $this->option('dry-run');

        foreach (self::MODELS as $brandName => $names) {
            if ($only && !in_array(strtolower($brandName), $only, true)) {
                continue;
            }

            $brand = Brands::where('subcat_id', self::PHONES_SUBCAT_ID)->where('brand', $brandName)->first();

            if (!$brand) {
                $this->error("{$brandName} brand not found under Mobile Phones.");
                continue;
            }

            $this->info("{$brandName} (brand {$brand->id})");
            $renamed = $this->rename($brand, self::RENAMES[$brandName] ?? [], $redirects, $dryRun);

            $existing = VehicleModel::where('brand_id', $brand->id)
                ->pluck('model')
                ->merge($renamed)
                ->map(fn ($name) => strtolower($name))
                ->all();

            $created = 0;
            foreach ($names as $name) {
                if (in_array(strtolower($name), $existing, true)) {
                    continue;
                }

                if (!$dryRun) {
                    $model = VehicleModel::create([
                        'cat_id'    => $brand->subCategory->cat_id,
                        'subcat_id' => $brand->subcat_id,
                        'brand_id'  => $brand->id,
                        'model'     => $name,
                    ]);
                    $this->line("  {$name} → {$model->model_slug}");
                } else {
                    $this->line("  {$name}");
                }

                $created++;
            }

            $verb = $dryRun ? 'Would create' : 'Created';
            $this->info("  {$verb} {$created} {$brandName} models (" . (count($names) - $created) . ' already existed).');
        }

        return self::SUCCESS;
    }

    /**
     * @return string[] the new names
     */
    private function rename(Brands $brand, array $renames, SlugRedirectResolver $redirects, bool $dryRun): array
    {
        $renamed = [];

        foreach ($renames as $from => $to) {
            $model = VehicleModel::where('brand_id', $brand->id)->where('model', $from)->first();

            if (!$model) {
                continue;
            }

            $slug = Str::slug($to);
            if (VehicleModel::where('model_slug', $slug)->where('id', '!=', $model->id)->exists()) {
                $slug .= '-' . $brand->brand_slug;
            }

            $this->line("  rename {$from} ({$model->model_slug}) → {$to} ({$slug})");

            if (!$dryRun) {
                $oldSlug = $model->model_slug;
                $model->forceFill(['model' => $to, 'model_slug' => $slug])->save();
                $redirects->record('model', $oldSlug, $slug);
            }

            $renamed[] = $to;
        }

        return $renamed;
    }
}
