<?php

namespace App\Console\Commands;

use App\Models\Advert;
use App\Models\Brands;
use App\Models\ComputerDetail;
use App\Models\SubCategory;
use App\Models\VehicleModel;
use App\Services\SlugRedirectResolver;
use App\Support\DeviceModels;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Laptops & Computers used product types as "brands" (Laptop, Desktop
 * Computer, Server), so the maker was only in the title. This makes the
 * makers the brands (HP, Dell, Lenovo, Apple, …) with their product lines as
 * models (EliteBook, Latitude, ThinkPad, MacBook Pro, …), the same way phones
 * work:
 *
 *  1. creates the maker brands and their models (by name — safe to re-run,
 *     and new lines can be appended to MAKERS);
 *  2. moves each listing filed under a type brand (or Other) to the maker its
 *     title names — none or several → Other;
 *  3. gives every listing a computer_details row, with the model its title
 *     names (existing models are kept);
 *  4. deletes the type brands, redirecting their old URLs to the
 *     subcategory page.
 */
class RestructureComputerBrands extends Command
{
    protected $signature = 'computers:restructure {--dry-run} {--show-titles : List each listing\'s new brand and model}';

    protected $description = 'Make makers (HP, Dell, …) the Laptops & Computers brands, with product lines as models';

    /** Type "brands" being replaced by makers. */
    private const TYPE_BRANDS = ['Laptop', 'Desktop Computer', 'Server'];

    private const OTHER_BRAND = 'Other';

    /**
     * maker => [title pattern, product lines (models)]. The pattern finds the
     * maker in a title, by name or by a product line only it makes.
     */
    private const MAKERS = [
        'HP' => [
            '/\bhp\b|hewlett|elite\s*book|elite\s*x360|probook|pavill?ion|\benvy\b|spectre|zbook|\bomen\b|victus|elitedesk|prodesk/i',
            ['EliteBook', 'ProBook', 'Pavilion', 'Envy', 'Spectre', 'ZBook', 'Omen', 'Victus', 'Stream', 'HP 250', 'Chromebook', 'EliteDesk', 'ProDesk'],
        ],
        'Dell' => [
            '/\bdell\b|latitude|inspiron|\bxps\b|vostro|precision|alienware|optiplex/i',
            ['Latitude', 'Inspiron', 'XPS', 'Vostro', 'Precision', 'Alienware', 'Chromebook', 'OptiPlex'],
        ],
        'Lenovo' => [
            '/lenovo|thinkpad|ideapad|\byoga\b|legion|thinkbook|thinkcentre|\bloq\b/i',
            ['ThinkPad', 'IdeaPad', 'Yoga', 'Legion', 'ThinkBook', 'LOQ', 'Chromebook', 'ThinkCentre'],
        ],
        'Apple' => [
            '/mac\s*book|\bimac\b|mac\s*mini|\bapple\b/i',
            ['MacBook', 'MacBook Air', 'MacBook Pro', 'iMac', 'Mac mini'],
        ],
        'Asus' => [
            '/\basus(tek)?\b|vivobook|zenbook|\brog\b|\btuf\b|expertbook/i',
            ['VivoBook', 'ZenBook', 'ROG', 'TUF', 'ExpertBook', 'Chromebook'],
        ],
        'Acer' => [
            '/\bacer\b|aspire|predator|travel\s*mate|extensa/i',
            ['Aspire', 'Swift', 'Nitro', 'Predator', 'TravelMate', 'Extensa', 'Chromebook'],
        ],
        'Microsoft' => [
            '/microsoft|\bsurface\b/i',
            ['Surface Pro', 'Surface Laptop', 'Surface Book', 'Surface Go'],
        ],
        'Toshiba' => [
            '/toshiba|dynabook|\bsatellite\b|\btecra\b|portege/i',
            ['Satellite', 'Tecra', 'Portege'],
        ],
        'Samsung' => [
            '/samsung|galaxy\s*book/i',
            ['Galaxy Book'],
        ],
        'MSI' => [
            '/\bmsi\b|\bkatana\b|\bgf63\b/i',
            ['Katana', 'Modern', 'Prestige', 'GF63', 'Stealth', 'Raider', 'Cyborg', 'Sword'],
        ],
        'Huawei' => [
            '/huawei|matebook/i',
            ['MateBook'],
        ],
    ];

    public function handle(SlugRedirectResolver $redirects): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $subcat = SubCategory::find(DeviceModels::COMPUTERS_SUBCATEGORY_ID);
        $other = $subcat ? Brands::where('subcat_id', $subcat->id)->where('brand', self::OTHER_BRAND)->first() : null;

        if (!$subcat || !$other) {
            $this->error('Laptops & Computers subcategory or its "Other" brand not found.');
            return self::FAILURE;
        }

        $typeBrands = Brands::where('subcat_id', $subcat->id)->whereIn('brand', self::TYPE_BRANDS)->get();
        $movable = $typeBrands->pluck('id')->push($other->id)->all();

        // 1. Plan: each listing's maker and model, matched from its title.
        $plan = [];
        foreach (Advert::where('sub_category', $subcat->id)->with('computer')->orderBy('id')->get() as $advert) {
            $maker = in_array((int) $advert->brand, $movable, true)
                ? $this->makerFor((string) $advert->ad_title)
                : optional(Brands::find($advert->brand))->brand;

            $plan[] = [
                'advert' => $advert,
                'maker'  => $maker ?? self::OTHER_BRAND,
                'line'   => $maker && isset(self::MAKERS[$maker]) ? $this->lineFor($maker, (string) $advert->ad_title) : null,
            ];
        }

        $this->report($plan);

        if ($dryRun) {
            $this->warn('Dry run — nothing changed.');
            return self::SUCCESS;
        }

        DB::transaction(function () use ($subcat, $other, $typeBrands, $plan, $redirects) {
            // 2. Maker brands and their models.
            $brandIds = [self::OTHER_BRAND => $other->id];
            $modelIds = [];
            foreach (self::MAKERS as $maker => [, $lines]) {
                $brand = Brands::firstOrCreate(
                    ['subcat_id' => $subcat->id, 'brand' => $maker],
                    [
                        'keywords'         => strtolower("{$maker} laptops ethiopia, buy {$maker} laptop, used {$maker} laptops, {$maker} computers for sale"),
                        'meta_title'       => "{$maker} Laptops for Sale in Ethiopia | Marketplace Ethiopia",
                        'meta_description' => "Buy and sell new and used {$maker} laptops and computers in Ethiopia. Compare prices and connect with verified sellers on Marketplace Ethiopia",
                    ]
                );
                $brandIds[$maker] = $brand->id;

                foreach ($lines as $line) {
                    $model = VehicleModel::firstOrCreate(
                        ['brand_id' => $brand->id, 'model' => $line],
                        ['cat_id' => $subcat->cat_id, 'subcat_id' => $subcat->id]
                    );
                    $this->slugLine($model, $maker, $line, $redirects);
                    $modelIds[$maker][$line] = $model->id;
                }
            }

            // 3. Listings → maker brand, plus computer_details with the model.
            foreach ($plan as ['advert' => $advert, 'maker' => $maker, 'line' => $line]) {
                $brandId = $brandIds[$maker] ?? (int) $advert->brand;
                if ((int) $advert->brand !== $brandId) {
                    // Query builder, not save(): no touched timestamps / observers for a re-file.
                    Advert::where('id', $advert->id)->update(['brand' => $brandId]);
                }

                $current = optional($advert->computer)->model;
                $keep = $current && VehicleModel::where('id', $current)->where('brand_id', $brandId)->exists();

                ComputerDetail::updateOrCreate(
                    ['advert_id' => $advert->id],
                    ['brand_id' => $brandId, 'model' => $keep ? $current : ($line ? $modelIds[$maker][$line] : null)]
                );
            }

            // 4. Retire the type brands; their pages now redirect to the subcategory.
            foreach ($typeBrands as $type) {
                $redirects->record('brand', $type->brand_slug, $subcat->sub_cat_slug);
                $type->delete();
            }
        });

        DeviceModels::flush();
        $this->info('Done. Regenerate the sitemap (sitemap:generate) so the new brand and model pages are listed.');

        return self::SUCCESS;
    }

    /**
     * Laptop model slugs carry the maker — "hp-elitebook", "dell-latitude" —
     * since many lines are shared (Chromebook), taken by car models (Latitude
     * is also a Renault) or plain words (MSI Modern), and model slugs are
     * site-wide (/{location}/electronics/{model}).
     */
    private function slugLine(VehicleModel $model, string $maker, string $line, SlugRedirectResolver $redirects): void
    {
        $slug = Str::slug(stripos($line, $maker) === 0 ? $line : "{$maker} {$line}");

        if ($model->model_slug === $slug
            || VehicleModel::where('model_slug', $slug)->where('id', '!=', $model->id)->exists()) {
            return;
        }

        if (!$model->wasRecentlyCreated) {
            $redirects->record('model', $model->model_slug, $slug);
        }
        $model->forceFill(['model_slug' => $slug])->save();
    }

    private function makerFor(string $title): ?string
    {
        $found = array_keys(array_filter(self::MAKERS, fn ($maker) => preg_match($maker[0], $title)));

        return count($found) === 1 ? $found[0] : null;
    }

    private function lineFor(string $maker, string $title): ?string
    {
        $lines = self::MAKERS[$maker][1];
        $models = array_combine(range(1, count($lines)), $lines);
        $id = DeviceModels::matcherFor($models, DeviceModels::COMPUTERS_SUBCATEGORY_ID, $maker)->match($title);

        return $id ? $models[$id] : null;
    }

    private function report(array $plan): void
    {
        $rows = [];
        foreach (collect($plan)->groupBy('maker') as $maker => $items) {
            $lines = $items->pluck('line')->filter()->countBy()->sortDesc()
                ->map(fn ($n, $line) => "{$line} {$n}")->implode(', ');
            $rows[] = [$maker, $items->count(), $items->whereNotNull('line')->count(), $lines];
        }
        usort($rows, fn ($a, $b) => $b[1] <=> $a[1]);

        $this->table(['Brand', 'Listings', 'With model', 'Models'], $rows);

        if ($this->option('show-titles')) {
            foreach ($plan as ['advert' => $advert, 'maker' => $maker, 'line' => $line]) {
                $this->line(sprintf('  %-10s %-14s %s', $maker, $line ?? '—', $advert->ad_title));
            }
        }
    }
}
