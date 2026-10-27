<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Url;
use Spatie\Sitemap\Tags\Sitemap as SitemapTag;
use App\Models\Advert;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\Brands;
use App\Models\Blog;

class GenerateSitemap extends Command
{
    protected $signature   = 'sitemap:generate';
    protected $description = 'Generate sitemap index with child sitemaps';

    private string $baseUrl;
    private string $outputDir;  // filesystem directory to write files into
    private string $publicUrl;  // public-facing URL prefix for sitemap index entries

    /**
     * Generic catch-all brand/model values (e.g. a seller who couldn't find
     * their real brand or model in the list) — thin, non-specific content
     * that shouldn't be indexed regardless of how many ads pile up under
     * them, mirroring the exclusion writeCategories() already applies to
     * catch-all subcategories. Checked against both brands.brand_slug and
     * models.model_slug.
     */
    private const EXCLUDED_CATCHALL_SLUGS = ['other', 'no-name'];

    public function handle(): void
    {
        $this->baseUrl = rtrim(config('app.url'), '/');

        if (app()->environment('production')) {
            $this->outputDir = dirname(base_path()) . '/public_html/';
        } elseif (app()->environment('testing')) {
            // Never overwrite the tracked public/sitemap-*.xml files with
            // whatever transient data a test run happens to seed.
            $this->outputDir = storage_path('framework/testing/sitemap') . '/';
            if (!is_dir($this->outputDir)) {
                mkdir($this->outputDir, 0755, true);
            }
        } else {
            $this->outputDir = public_path() . '/';
        }
        $this->publicUrl = $this->baseUrl;

        $index = SitemapIndex::create();

        $this->writeStatic($index);
        $this->writeCategories($index);
        $this->writeLocations($index);
        $this->writeBrands($index);
        $this->writeAdverts($index);
        $this->writeBlog($index);

        $indexPath = $this->outputDir . 'sitemap.xml';
        $index->writeToFile($indexPath);

        $this->info("✔ Sitemap index written to: {$indexPath}");
    }

    // -------------------------------------------------------------------------
    // Child sitemap writers
    // -------------------------------------------------------------------------

    private function writeStatic(SitemapIndex $index): void
    {
        $sitemap = Sitemap::create();

        $pages = [
            ['/',                  1.0,  Url::CHANGE_FREQUENCY_DAILY],
            ['/about-us',         0.8,  Url::CHANGE_FREQUENCY_MONTHLY],
            ['/contact-us',       0.8,  Url::CHANGE_FREQUENCY_MONTHLY],
            ['/faq',              0.7,  Url::CHANGE_FREQUENCY_MONTHLY],
            ['/how-it-works',     0.9,  Url::CHANGE_FREQUENCY_WEEKLY],
            ['/safety-tips',      0.7,  Url::CHANGE_FREQUENCY_MONTHLY],
            ['/career',           0.6,  Url::CHANGE_FREQUENCY_MONTHLY],
            ['/our-terms',        0.5,  Url::CHANGE_FREQUENCY_YEARLY],
            ['/privacy-policy',   0.5,  Url::CHANGE_FREQUENCY_YEARLY],
            ['/cookie-policy',    0.5,  Url::CHANGE_FREQUENCY_YEARLY],
            ['/billing-policy',   0.5,  Url::CHANGE_FREQUENCY_YEARLY],
            ['/copyright-policy', 0.5,  Url::CHANGE_FREQUENCY_YEARLY],
            ['/dmca-policy',      0.5,  Url::CHANGE_FREQUENCY_YEARLY],
            ['/payments-refunds', 0.6,  Url::CHANGE_FREQUENCY_MONTHLY],
            ['/sell-online',      0.7,  Url::CHANGE_FREQUENCY_MONTHLY],
            ['/shipping',         0.6,  Url::CHANGE_FREQUENCY_MONTHLY],
            ['/advertise-with-us',0.6,  Url::CHANGE_FREQUENCY_MONTHLY],
        ];

        foreach ($pages as [$path, $priority, $freq]) {
            $sitemap->add(
                Url::create($path)
                    ->setLastModificationDate(now())
                    ->setChangeFrequency($freq)
                    ->setPriority($priority)
            );
        }

        $this->write($sitemap, 'sitemap-static.xml', $index);
        $this->info('  → sitemap-static.xml (' . count($pages) . ' URLs)');
    }

    private function writeCategories(SitemapIndex $index): void
    {
        $sitemap = Sitemap::create();
        $count   = 0;

        // Top-level categories
        Category::orderBy('updated_at', 'desc')->chunk(200, function ($categories) use ($sitemap, &$count) {
            foreach ($categories as $cat) {
                $sitemap->add(
                    Url::create("{$this->baseUrl}/category/{$cat->category_slug}")
                        ->setLastModificationDate($cat->updated_at ?? now())
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                        ->setPriority(0.9)
                );
                $count++;
            }
        });

        // Subcategories — only those with at least one live ad, so empty/unused
        // catch-all facets (e.g. "Other") don't get an indexable page.
        $activeSubCategoryIds = DB::table('adverts')
            ->where('ad_status', 1)
            ->whereNotNull('sub_category')
            ->distinct()
            ->pluck('sub_category');

        SubCategory::with('category')
            ->whereIn('id', $activeSubCategoryIds)
            ->orderBy('updated_at', 'desc')
            ->chunk(500, function ($subcats) use ($sitemap, &$count) {
                foreach ($subcats as $sub) {
                    if (!$sub->category) {
                        continue;
                    }
                    $sitemap->add(
                        Url::create("{$this->baseUrl}/category/{$sub->category->category_slug}/{$sub->sub_cat_slug}")
                            ->setLastModificationDate($sub->updated_at ?? now())
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.8)
                    );
                    $count++;
                }
            });

        $this->write($sitemap, 'sitemap-categories.xml', $index);
        $this->info("  → sitemap-categories.xml ({$count} URLs)");
    }

    private function writeLocations(SitemapIndex $index): void
    {
        $sitemap = Sitemap::create();
        $count   = 0;

        // ── State-only pages ──────────────────────────────────────────────────
        DB::table('adverts')
            ->select('state_slug', DB::raw('MAX(updated_at) as last_updated'))
            ->where('ad_status', 1)
            ->whereNotNull('state_slug')
            ->where('state_slug', '!=', '')
            ->groupBy('state_slug')
            ->get()
            ->each(function ($row) use ($sitemap, &$count) {
                $sitemap->add(
                    Url::create("{$this->baseUrl}/{$row->state_slug}")
                        ->setLastModificationDate($this->toDate($row->last_updated))
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                        ->setPriority(0.8)
                );
                $count++;
            });

        // ── State + Category ──────────────────────────────────────────────────
        DB::table('adverts')
            ->join('categories', 'adverts.category', '=', 'categories.id')
            ->select(
                'adverts.state_slug',
                'categories.category_slug',
                DB::raw('MAX(adverts.updated_at) as last_updated')
            )
            ->where('adverts.ad_status', 1)
            ->whereNotNull('adverts.state_slug')
            ->where('adverts.state_slug', '!=', '')
            ->groupBy('adverts.state_slug', 'categories.category_slug')
            ->orderBy('last_updated', 'desc')
            ->chunk(500, function ($rows) use ($sitemap, &$count) {
                foreach ($rows as $row) {
                    $sitemap->add(
                        Url::create("{$this->baseUrl}/{$row->state_slug}/{$row->category_slug}")
                            ->setLastModificationDate($this->toDate($row->last_updated))
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.8)
                    );
                    $count++;
                }
            });

        // ── State + SubCategory ───────────────────────────────────────────────
        // Same >=5 noindex threshold as the Category+Brand combo below —
        // avoids indexing thin single-ad combos, especially generic
        // "Other"/catch-all subcategories.
        DB::table('adverts')
            ->join('sub_categories', 'adverts.sub_category', '=', 'sub_categories.id')
            ->select(
                'adverts.state_slug',
                'sub_categories.sub_cat_slug',
                DB::raw('MAX(adverts.updated_at) as last_updated'),
                DB::raw('COUNT(*) as ad_count')
            )
            ->where('adverts.ad_status', 1)
            ->whereNotNull('adverts.state_slug')
            ->where('adverts.state_slug', '!=', '')
            ->groupBy('adverts.state_slug', 'sub_categories.sub_cat_slug')
            ->having('ad_count', '>=', 5)
            ->orderBy('last_updated', 'desc')
            ->chunk(500, function ($rows) use ($sitemap, &$count) {
                foreach ($rows as $row) {
                    $sitemap->add(
                        Url::create("{$this->baseUrl}/{$row->state_slug}/{$row->sub_cat_slug}")
                            ->setLastModificationDate($this->toDate($row->last_updated))
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.8)
                    );
                    $count++;
                }
            });

        // ── State + Brand ─────────────────────────────────────────────────────
        // Same >=5 noindex threshold as the Category+Brand combo below.
        DB::table('adverts')
            ->join('brands', 'adverts.brand', '=', 'brands.id')
            ->select(
                'adverts.state_slug',
                'brands.brand_slug',
                DB::raw('MAX(adverts.updated_at) as last_updated'),
                DB::raw('COUNT(*) as ad_count')
            )
            ->where('adverts.ad_status', 1)
            ->whereNotNull('adverts.state_slug')
            ->where('adverts.state_slug', '!=', '')
            ->whereNotNull('adverts.brand')
            ->whereNotIn('brands.brand_slug', self::EXCLUDED_CATCHALL_SLUGS)
            ->groupBy('adverts.state_slug', 'brands.brand_slug')
            ->having('ad_count', '>=', 5)
            ->orderBy('last_updated', 'desc')
            ->chunk(500, function ($rows) use ($sitemap, &$count) {
                foreach ($rows as $row) {
                    $sitemap->add(
                        Url::create("{$this->baseUrl}/{$row->state_slug}/{$row->brand_slug}")
                            ->setLastModificationDate($this->toDate($row->last_updated))
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.7)
                    );
                    $count++;
                }
            });

        // ── State + Category + Brand ──────────────────────────────────────────
        // Only include combos with enough inventory to be worth indexing —
        // mirrors the noindex threshold used on the pages themselves.
        DB::table('adverts')
            ->join('categories', 'adverts.category', '=', 'categories.id')
            ->join('brands', 'adverts.brand', '=', 'brands.id')
            ->select(
                'adverts.state_slug',
                'categories.category_slug',
                'brands.brand_slug',
                DB::raw('MAX(adverts.updated_at) as last_updated'),
                DB::raw('COUNT(*) as ad_count')
            )
            ->where('adverts.ad_status', 1)
            ->whereNotNull('adverts.state_slug')
            ->where('adverts.state_slug', '!=', '')
            ->whereNotNull('adverts.brand')
            ->whereNotIn('brands.brand_slug', self::EXCLUDED_CATCHALL_SLUGS)
            ->groupBy('adverts.state_slug', 'categories.category_slug', 'brands.brand_slug')
            ->having('ad_count', '>=', 5)
            ->orderBy('last_updated', 'desc')
            ->chunk(500, function ($rows) use ($sitemap, &$count) {
                foreach ($rows as $row) {
                    $sitemap->add(
                        Url::create("{$this->baseUrl}/{$row->state_slug}/{$row->category_slug}/{$row->brand_slug}")
                            ->setLastModificationDate($this->toDate($row->last_updated))
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.6)
                    );
                    $count++;
                }
            });

        // ── State + Category + Model (categories with models only) ────────────
        // Mirrors SearchFilter::MODEL_CATEGORY_DETAIL_RELATIONS — the only
        // categories with model-level pages, each backed by its own detail
        // table (see Advert::MODEL_DETAIL_TABLES).
        foreach (array_unique(Advert::MODEL_DETAIL_TABLES) as $detailTable) {
            DB::table('adverts')
                ->join($detailTable, "{$detailTable}.advert_id", '=', 'adverts.id')
                ->join('models', 'models.id', '=', "{$detailTable}.model")
                ->join('categories', 'adverts.category', '=', 'categories.id')
                ->select(
                    'adverts.state_slug',
                    'categories.category_slug',
                    'models.model_slug',
                    DB::raw('MAX(adverts.updated_at) as last_updated'),
                    DB::raw('COUNT(*) as ad_count')
                )
                ->where('adverts.ad_status', 1)
                ->whereNotNull('adverts.state_slug')
                ->where('adverts.state_slug', '!=', '')
                ->whereNotIn('models.model_slug', self::EXCLUDED_CATCHALL_SLUGS)
                ->groupBy('adverts.state_slug', 'categories.category_slug', 'models.model_slug')
                ->having('ad_count', '>=', 5)
                ->orderBy('last_updated', 'desc')
                ->chunk(500, function ($rows) use ($sitemap, &$count) {
                    foreach ($rows as $row) {
                        $sitemap->add(
                            Url::create("{$this->baseUrl}/{$row->state_slug}/{$row->category_slug}/{$row->model_slug}")
                                ->setLastModificationDate($this->toDate($row->last_updated))
                                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                                ->setPriority(0.6)
                        );
                        $count++;
                    }
                });
        }

        $this->writeStateLocations($sitemap, $count);

        $this->write($sitemap, 'sitemap-locations.xml', $index);
        $this->info("  → sitemap-locations.xml ({$count} URLs)");
    }

    /**
     * State-level browsing pages (e.g. /lagos, /lagos/samsung) — a separate
     * tier from the LGA-level pages above. adverts.state_slug is LGA-granular,
     * but adverts.state holds the full state name and is matched by
     * SearchFilter::applyLocationFilter() / FeaturedAdPaginator::applyFilters()
     * via the states table. These pages are real and fully indexable (verified
     * live: /lagos/samsung renders "index, follow" with real listings) but were
     * previously absent from every sitemap since the LGA-only groupBy above
     * never produces them — Google had no path to discover them.
     */
    private function writeStateLocations(Sitemap $sitemap, int &$count): void
    {
        // ── State-only pages ────────────────────────────────────────────────
        DB::table('adverts')
            ->join('states', DB::raw('LOWER(states.name)'), '=', DB::raw('LOWER(adverts.state)'))
            ->select('states.slug', DB::raw('MAX(adverts.updated_at) as last_updated'))
            ->where('adverts.ad_status', 1)
            ->whereNotNull('adverts.state')
            ->where('adverts.state', '!=', '')
            ->groupBy('states.slug')
            ->get()
            ->each(function ($row) use ($sitemap, &$count) {
                $sitemap->add(
                    Url::create("{$this->baseUrl}/{$row->slug}")
                        ->setLastModificationDate($this->toDate($row->last_updated))
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
                        ->setPriority(0.8)
                );
                $count++;
            });

        // ── State + Category ─────────────────────────────────────────────────
        DB::table('adverts')
            ->join('states', DB::raw('LOWER(states.name)'), '=', DB::raw('LOWER(adverts.state)'))
            ->join('categories', 'adverts.category', '=', 'categories.id')
            ->select(
                'states.slug',
                'categories.category_slug',
                DB::raw('MAX(adverts.updated_at) as last_updated')
            )
            ->where('adverts.ad_status', 1)
            ->whereNotNull('adverts.state')
            ->where('adverts.state', '!=', '')
            ->groupBy('states.slug', 'categories.category_slug')
            ->orderBy('last_updated', 'desc')
            ->chunk(500, function ($rows) use ($sitemap, &$count) {
                foreach ($rows as $row) {
                    $sitemap->add(
                        Url::create("{$this->baseUrl}/{$row->slug}/{$row->category_slug}")
                            ->setLastModificationDate($this->toDate($row->last_updated))
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.8)
                    );
                    $count++;
                }
            });

        // ── State + SubCategory ──────────────────────────────────────────────
        // Same >=5 noindex threshold as the Category+Brand combo below.
        DB::table('adverts')
            ->join('states', DB::raw('LOWER(states.name)'), '=', DB::raw('LOWER(adverts.state)'))
            ->join('sub_categories', 'adverts.sub_category', '=', 'sub_categories.id')
            ->select(
                'states.slug',
                'sub_categories.sub_cat_slug',
                DB::raw('MAX(adverts.updated_at) as last_updated'),
                DB::raw('COUNT(*) as ad_count')
            )
            ->where('adverts.ad_status', 1)
            ->whereNotNull('adverts.state')
            ->where('adverts.state', '!=', '')
            ->groupBy('states.slug', 'sub_categories.sub_cat_slug')
            ->having('ad_count', '>=', 5)
            ->orderBy('last_updated', 'desc')
            ->chunk(500, function ($rows) use ($sitemap, &$count) {
                foreach ($rows as $row) {
                    $sitemap->add(
                        Url::create("{$this->baseUrl}/{$row->slug}/{$row->sub_cat_slug}")
                            ->setLastModificationDate($this->toDate($row->last_updated))
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.8)
                    );
                    $count++;
                }
            });

        // ── State + Brand ────────────────────────────────────────────────────
        // Same >=5 noindex threshold as the Category+Brand combo below.
        DB::table('adverts')
            ->join('states', DB::raw('LOWER(states.name)'), '=', DB::raw('LOWER(adverts.state)'))
            ->join('brands', 'adverts.brand', '=', 'brands.id')
            ->select(
                'states.slug',
                'brands.brand_slug',
                DB::raw('MAX(adverts.updated_at) as last_updated'),
                DB::raw('COUNT(*) as ad_count')
            )
            ->where('adverts.ad_status', 1)
            ->whereNotNull('adverts.state')
            ->where('adverts.state', '!=', '')
            ->whereNotNull('adverts.brand')
            ->whereNotIn('brands.brand_slug', self::EXCLUDED_CATCHALL_SLUGS)
            ->groupBy('states.slug', 'brands.brand_slug')
            ->having('ad_count', '>=', 5)
            ->orderBy('last_updated', 'desc')
            ->chunk(500, function ($rows) use ($sitemap, &$count) {
                foreach ($rows as $row) {
                    $sitemap->add(
                        Url::create("{$this->baseUrl}/{$row->slug}/{$row->brand_slug}")
                            ->setLastModificationDate($this->toDate($row->last_updated))
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.7)
                    );
                    $count++;
                }
            });

        // ── State + Category + Brand ─────────────────────────────────────────
        // Same >=5 noindex threshold as the LGA-level combo pages above.
        DB::table('adverts')
            ->join('states', DB::raw('LOWER(states.name)'), '=', DB::raw('LOWER(adverts.state)'))
            ->join('categories', 'adverts.category', '=', 'categories.id')
            ->join('brands', 'adverts.brand', '=', 'brands.id')
            ->select(
                'states.slug',
                'categories.category_slug',
                'brands.brand_slug',
                DB::raw('MAX(adverts.updated_at) as last_updated'),
                DB::raw('COUNT(*) as ad_count')
            )
            ->where('adverts.ad_status', 1)
            ->whereNotNull('adverts.state')
            ->where('adverts.state', '!=', '')
            ->whereNotNull('adverts.brand')
            ->whereNotIn('brands.brand_slug', self::EXCLUDED_CATCHALL_SLUGS)
            ->groupBy('states.slug', 'categories.category_slug', 'brands.brand_slug')
            ->having('ad_count', '>=', 5)
            ->orderBy('last_updated', 'desc')
            ->chunk(500, function ($rows) use ($sitemap, &$count) {
                foreach ($rows as $row) {
                    $sitemap->add(
                        Url::create("{$this->baseUrl}/{$row->slug}/{$row->category_slug}/{$row->brand_slug}")
                            ->setLastModificationDate($this->toDate($row->last_updated))
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.6)
                    );
                    $count++;
                }
            });

        // ── State + Category + Model (categories with models only) ───────────
        foreach (array_unique(Advert::MODEL_DETAIL_TABLES) as $detailTable) {
            DB::table('adverts')
                ->join('states', DB::raw('LOWER(states.name)'), '=', DB::raw('LOWER(adverts.state)'))
                ->join($detailTable, "{$detailTable}.advert_id", '=', 'adverts.id')
                ->join('models', 'models.id', '=', "{$detailTable}.model")
                ->join('categories', 'adverts.category', '=', 'categories.id')
                ->select(
                    'states.slug',
                    'categories.category_slug',
                    'models.model_slug',
                    DB::raw('MAX(adverts.updated_at) as last_updated'),
                    DB::raw('COUNT(*) as ad_count')
                )
                ->where('adverts.ad_status', 1)
                ->whereNotNull('adverts.state')
                ->where('adverts.state', '!=', '')
                ->whereNotIn('models.model_slug', self::EXCLUDED_CATCHALL_SLUGS)
                ->groupBy('states.slug', 'categories.category_slug', 'models.model_slug')
                ->having('ad_count', '>=', 5)
                ->orderBy('last_updated', 'desc')
                ->chunk(500, function ($rows) use ($sitemap, &$count) {
                    foreach ($rows as $row) {
                        $sitemap->add(
                            Url::create("{$this->baseUrl}/{$row->slug}/{$row->category_slug}/{$row->model_slug}")
                                ->setLastModificationDate($this->toDate($row->last_updated))
                                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                                ->setPriority(0.6)
                        );
                        $count++;
                    }
                });
        }
    }

    private function writeBrands(SitemapIndex $index): void
    {
        $sitemap = Sitemap::create();
        $count   = 0;

        // Brand pages live at /category/{category_slug}/{subcat_slug}/{brand_slug}
        // (see AdvertController::brand). Resolve category via sub_categories.cat_id,
        // not brands.cat_id directly — that column is unreliably populated, unlike
        // brands.subcat_id which the controller actually keys off of. Only index
        // brands with enough active inventory — mirrors the noindex threshold used
        // on the page itself.
        DB::table('brands')
            ->join('adverts', 'brands.id', '=', 'adverts.brand')
            ->join('sub_categories', 'brands.subcat_id', '=', 'sub_categories.id')
            ->join('categories', 'sub_categories.cat_id', '=', 'categories.id')
            ->select(
                'categories.category_slug',
                'sub_categories.sub_cat_slug',
                'brands.brand_slug',
                DB::raw('MAX(adverts.updated_at) as last_updated'),
                DB::raw('COUNT(*) as ad_count')
            )
            ->where('adverts.ad_status', 1)
            ->whereNotIn('brands.brand_slug', self::EXCLUDED_CATCHALL_SLUGS)
            ->groupBy('brands.id', 'categories.category_slug', 'sub_categories.sub_cat_slug', 'brands.brand_slug')
            ->having('ad_count', '>=', 5)
            ->orderBy('last_updated', 'desc')
            ->chunk(500, function ($rows) use ($sitemap, &$count) {
                foreach ($rows as $row) {
                    $sitemap->add(
                        Url::create("{$this->baseUrl}/category/{$row->category_slug}/{$row->sub_cat_slug}/{$row->brand_slug}")
                            ->setLastModificationDate($this->toDate($row->last_updated))
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.7)
                    );
                    $count++;
                }
            });

        // Brand model pages — /category/{category_slug}/{subcat_slug}/{brand_slug}/{model_slug}
        // (see AdvertController::brandModel), e.g. Apple → iPhone 12 Pro Max.
        // Models hang off the detail tables (see Advert::MODEL_DETAIL_TABLES).
        foreach (array_unique(Advert::MODEL_DETAIL_TABLES) as $detailTable) {
            DB::table('adverts')
                ->join($detailTable, "{$detailTable}.advert_id", '=', 'adverts.id')
                ->join('models', 'models.id', '=', "{$detailTable}.model")
                ->join('brands', 'brands.id', '=', 'models.brand_id')
                ->join('sub_categories', 'brands.subcat_id', '=', 'sub_categories.id')
                ->join('categories', 'sub_categories.cat_id', '=', 'categories.id')
                ->select(
                    'categories.category_slug',
                    'sub_categories.sub_cat_slug',
                    'brands.brand_slug',
                    'models.model_slug',
                    DB::raw('MAX(adverts.updated_at) as last_updated'),
                    DB::raw('COUNT(*) as ad_count')
                )
                ->where('adverts.ad_status', 1)
                ->whereColumn('adverts.brand', 'brands.id')
                ->whereNotIn('brands.brand_slug', self::EXCLUDED_CATCHALL_SLUGS)
                ->groupBy('models.id', 'categories.category_slug', 'sub_categories.sub_cat_slug', 'brands.brand_slug', 'models.model_slug')
                ->having('ad_count', '>=', 5)
                ->orderBy('last_updated', 'desc')
                ->chunk(500, function ($rows) use ($sitemap, &$count) {
                    foreach ($rows as $row) {
                        $sitemap->add(
                            Url::create("{$this->baseUrl}/category/{$row->category_slug}/{$row->sub_cat_slug}/{$row->brand_slug}/{$row->model_slug}")
                                ->setLastModificationDate($this->toDate($row->last_updated))
                                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                                ->setPriority(0.6)
                        );
                        $count++;
                    }
                });
        }

        $this->write($sitemap, 'sitemap-brands.xml', $index);
        $this->info("  → sitemap-brands.xml ({$count} URLs)");
    }

    private function writeAdverts(SitemapIndex $index): void
    {
        $sitemap = Sitemap::create();
        $count   = 0;

        Advert::where('ad_status', 1)
            ->whereNotNull('state_slug')
            ->where('state_slug', '!=', '')
            ->orderBy('updated_at', 'desc')
            ->chunk(500, function ($adverts) use ($sitemap, &$count) {
                foreach ($adverts as $ad) {
                    $sitemap->add(
                        Url::create("{$this->baseUrl}/{$ad->state_slug}/{$ad->title_slug}/{$ad->ad_id}")
                            ->setLastModificationDate($ad->updated_at)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                            ->setPriority(0.8)
                    );
                    $count++;
                }
            });

        $this->write($sitemap, 'sitemap-adverts.xml', $index);
        $this->info("  → sitemap-adverts.xml ({$count} URLs)");
    }

    private function writeBlog(SitemapIndex $index): void
    {
        $sitemap = Sitemap::create();
        $count   = 0;

        $sitemap->add(
            Url::create("{$this->baseUrl}/blog")
                ->setLastModificationDate(now())
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                ->setPriority(0.8)
        );
        $count++;

        Blog::where('status', 'published')
            ->orderBy('updated_at', 'desc')
            ->chunk(500, function ($blogs) use ($sitemap, &$count) {
                foreach ($blogs as $blog) {
                    $sitemap->add(
                        Url::create("{$this->baseUrl}/blog/{$blog->slug}")
                            ->setLastModificationDate($blog->updated_at)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY)
                            ->setPriority(0.7)
                    );
                    $count++;
                }
            });

        $this->write($sitemap, 'sitemap-blog.xml', $index);
        $this->info("  → sitemap-blog.xml ({$count} URLs)");
    }

    // -------------------------------------------------------------------------

    private function write(Sitemap $sitemap, string $filename, SitemapIndex $index): void
    {
        $sitemap->writeToFile($this->outputDir . $filename);

        $index->add(
            SitemapTag::create("{$this->publicUrl}/{$filename}")
                ->setLastModificationDate(now())
        );
    }

    /** Safely cast a raw DB date string or Carbon to DateTimeInterface. */
    private function toDate(mixed $value): \DateTimeInterface
    {
        if ($value instanceof \DateTimeInterface) {
            return $value;
        }

        return $value ? \Carbon\Carbon::parse($value) : now();
    }
}
