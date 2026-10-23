<?php
namespace App\Services;

use App\Models\Advert;
use App\Models\State;
use Illuminate\Support\Collection;
use Jenssegers\Agent\Agent;

class FeaturedAdPaginator
{
    protected int $perPage;
    protected int $featuredLimit;
    protected int $columns; // Track columns for layout
    protected array $filters = [];
    protected bool $isMobile;

    public function __construct(protected int $page = 1)
    {
        $this->page = max(1, $page);

        // Detect device type
        $agent = new Agent();
        $this->isMobile = $agent->isMobile();

        // Set columns and calculate limits as multiples of columns
        $this->columns = $this->isMobile ? 2 : 3;
        $this->perPage = $this->isMobile ? 16 : 24; // Already multiples of columns
        $this->featuredLimit = $this->isMobile ? 4 : 6; // 2 rows mobile, 2 rows desktop
    }

    public function filters(array $filters): self
    {
        $this->filters = $filters;
        return $this;
    }

    public function get(): array
    {
        $featured = collect();
        $firstPageAds = collect();

        if ($this->page === 1) {
            $featured = $this->getFeaturedAds();
            $featuredCount = $featured->count();

            // Calculate how many ads we need to complete the row
            $remainder = $featuredCount % $this->columns;

            if ($remainder > 0) {
                // We have an incomplete row, need to fill it
                $adsNeeded = $this->columns - $remainder;

                // Get regular ads to fill the incomplete row
                $fillerAds = $this->getRegularAds(0, $adsNeeded);

                // Merge featured with filler ads
                $firstPageAds = $featured->merge($fillerAds);

                // Get remaining regular ads for the rest of the page
                $remainingCount = $this->perPage - $firstPageAds->count();
                if ($remainingCount > 0) {
                    $remainingAds = $this->getRegularAds($adsNeeded, $remainingCount);
                    $firstPageAds = $firstPageAds->merge($remainingAds);
                }
            } else {
                // Perfect rows, just get regular ads normally
                $remainingCount = $this->perPage - $featuredCount;
                if ($remainingCount > 0) {
                    $regularAds = $this->getRegularAds(0, $remainingCount);
                    $firstPageAds = $featured->merge($regularAds);
                } else {
                    $firstPageAds = $featured;
                }
            }

            $ads = $firstPageAds;
        } else {
            $ads = $this->getRegularAds();
        }

        $total   = $this->getTotalCount();
        $hasMore = $total > $this->page * $this->perPage;

        return [
            'ads'          => $ads,
            'hasMore'      => $hasMore,
            'nextPage'     => $this->page + 1,
            'current_page' => $this->page,
            'per_page'     => $this->perPage,
            'total'        => $total,
            'last_page'    => $this->perPage > 0 ? (int) ceil($total / $this->perPage) : 1,
            'featuredCount' => $this->page === 1 ? $featured->count() : 0,
        ];
    }

    protected function getFeaturedAds(): Collection
    {
        $query = Advert::with(['firstImage', 'car', 'brands', 'owner', 'boost' => fn($q) => $q->where('boost_status', 'active')])
            ->where('featured', 'Yes')
            ->where('ad_status', 'active')
            ->where(function ($q) {
                $q->where('sold', '!=', 'Yes')
                  ->orWhere(function ($subQ) {
                      $subQ->where('sold', 'Yes')
                           ->whereNotNull('sold_date')
                           ->where('sold_date', '>=', now()->subDays(30));
                  });
            })
            ->whereHas('boost', fn($q) => $q->where('boost_status', 'active'));

        $this->applyFilters($query);

        return $query->inRandomOrder()
            ->limit($this->featuredLimit)
            ->get();
    }

    protected function getRegularAds(?int $offset = null, ?int $limit = null): Collection
    {
        $offset = $offset ?? (($this->page - 1) * $this->perPage);
        $limit = $limit ?? $this->perPage;

        $query = Advert::with('firstImage', 'car', 'brands', 'owner')
            ->where('ad_status', 'active')
            ->where(function ($q) {
                $q->where('sold', '!=', 'Yes')
                  ->orWhere(function ($subQ) {
                      $subQ->where('sold', 'Yes')
                           ->whereNotNull('sold_date')
                           ->where('sold_date', '>=', now()->subDays(30));
                  });
            })
            ->where(function ($q) {
                $q->where('featured', '!=', 'Yes')->orWhereNull('featured');
            });

        $this->applyFilters($query);

        return $query->orderBy('created_at', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get();
    }

    protected function getTotalCount(): int
    {
        $query = Advert::where('ad_status', 'active')
            ->where(function ($q) {
                $q->where('sold', '!=', 'Yes')
                  ->orWhere(function ($subQ) {
                      $subQ->where('sold', 'Yes')
                           ->whereNotNull('sold_date')
                           ->where('sold_date', '>=', now()->subDays(30));
                  });
            })
            ->where(function ($q) {
                $q->where('featured', '!=', 'Yes')->orWhereNull('featured');
            });

        $this->applyFilters($query);

        return $query->count();
    }

    protected function applyFilters($query)
    {
        foreach ($this->filters as $key => $value) {
            if (!$value) {
                continue;
            }

            // A {location} URL segment can be a state-level slug (matched
            // against adverts.state, e.g. "lagos") or an LGA-level slug
            // (matched against adverts.state_slug, e.g. "ikeja") — the two
            // columns hold different granularities, so a "state_slug" filter
            // must check both to work for either tier. Multi-word states
            // ("Akwa Ibom") can never equal their own hyphenated slug via
            // plain string comparison, so the slug is also resolved against
            // the states table to get the real name to match.
            if ($key === 'state_slug') {
                $stateName = State::nameForSlug($value);
                $query->where(function ($q) use ($value, $stateName) {
                    $q->where('state_slug', $value)->orWhere('state', $value);

                    if ($stateName) {
                        $q->orWhere('state', $stateName);
                    }
                });
            } elseif (in_array($key, ['category', 'sub_category', 'brand', 'model', 'state', 'city'])) {
                $query->where($key, $value);
            }
        }
    }
}
