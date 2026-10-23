@include('user.layouts.header')
@include('user.layouts.nav')
@include('user.layouts.back-nav')
@include('user.layouts.search')

<style>
/* ── Page wrapper ───────────────────────────────────── */
.myad-wrap {
    width: 100%;
    max-width: 900px;
    margin: 24px auto 100px;
    padding: 0 6px;
}

/* ── Page header ─────────────────────────────────────── */
.myad-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    padding: 14px 20px;
    margin-bottom: 16px;
}
.myad-page-title {
    font-size: 1rem;
    font-weight: 700;
    color: #1a1a2e;
    display: flex;
    align-items: center;
    gap: 8px;
}
.myad-page-title i { color: #326916; }
.myad-boosted-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.78rem;
    font-weight: 600;
    color: #326916;
    border: 1.5px solid #326916;
    padding: 7px 14px;
    border-radius: 8px;
    text-decoration: none;
    transition: all 0.15s;
}
.myad-boosted-link:hover { background: #326916; color: #fff; text-decoration: none; }

/* ── Ad card ─────────────────────────────────────────── */
.myad-card {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    margin-bottom: 14px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    transition: box-shadow 0.15s;
}
.myad-card:hover { box-shadow: 0 4px 14px rgba(0,0,0,0.08); }
.myad-card.is-sold { border-color: #fde8e8; background: #fffafa; }
.myad-card.is-disabled { opacity: 0.82; }

/* ── Card body ───────────────────────────────────────── */
.myad-body {
    display: flex;
    gap: 14px;
    padding: 14px;
    align-items: flex-start;
    position: relative;
}

/* Image */
.myad-img {
    flex-shrink: 0;
    width: 90px;
    height: 90px;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
    background: #f9fafb;
    position: relative;
    display: block;
}
.myad-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.myad-img-count {
    position: absolute;
    bottom: 5px;
    right: 5px;
    background: rgba(0,0,0,0.65);
    color: #fff;
    font-size: 0.6rem;
    font-weight: 700;
    padding: 2px 5px;
    border-radius: 4px;
    line-height: 1;
    display: flex;
    align-items: center;
    gap: 2px;
}
@media (min-width: 640px) {
    .myad-img { width: 110px; height: 110px; }
}

/* Details */
.myad-details { flex: 1; min-width: 0; overflow: hidden; }

.myad-meta {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.7rem;
    color: #9ca3af;
    margin-bottom: 5px;
    flex-wrap: wrap;
}
.myad-meta-item { display: flex; align-items: center; gap: 3px; }
.myad-meta-item i { color: #326916; font-size: 0.72rem; }

.myad-title {
    font-size: 0.92rem;
    font-weight: 700;
    color: #111827;
    text-decoration: none;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.35;
    margin-bottom: 4px;
}
.myad-title:hover { color: #326916; text-decoration: none; }
.is-sold .myad-title { color: #9ca3af; }

.myad-desc {
    font-size: 0.72rem;
    color: #9ca3af;
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
    word-break: break-word;
    margin-bottom: 6px;
}
@media (max-width: 539px) { .myad-desc { display: none; } }

.myad-price {
    font-size: 1rem;
    font-weight: 800;
    color: #326916;
    margin-top: 4px;
}
.is-sold .myad-price { color: #9ca3af; }

/* Three-dots button */
.myad-dots-btn {
    width: 32px; height: 32px;
    border-radius: 50%;
    border: none;
    background: transparent;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer;
    color: #9ca3af;
    flex-shrink: 0;
    transition: background 0.15s;
}
.myad-dots-btn:hover { background: #f3f4f6; color: #374151; }

/* ── Card footer (actions) ───────────────────────────── */
.myad-footer {
    display: grid;
    grid-template-columns: 1fr 1fr;   /* 2×2 for active (4 buttons) */
    gap: 6px;
    padding: 10px 14px 12px;
    border-top: 1px solid #f5f5f5;
}
.myad-footer--sold {
    grid-template-columns: repeat(3, 1fr);  /* 3-in-a-row for sold */
}
.myad-footer--shipping {
    grid-column: 1 / -1;  /* shipping button spans full width */
}
/* Desktop: revert to a single flex row */
@media (min-width: 640px) {
    .myad-footer,
    .myad-footer--sold {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
    }
}

.myad-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
    padding: 8px 11px;
    border-radius: 8px;
    font-size: 0.72rem;
    font-weight: 600;
    text-decoration: none;
    border: 1.5px solid transparent;
    cursor: pointer;
    transition: all 0.15s;
    white-space: nowrap;
    background: none;
    line-height: 1;
    width: 100%;         /* fill grid cell */
    box-sizing: border-box;
}
.myad-btn:hover { text-decoration: none; }
/* On desktop, buttons shrink back to content width */
@media (min-width: 640px) {
    .myad-btn { width: auto; }
}

/* Mark Sold */
.myad-btn-sell {
    border-color: #d1d5db;
    color: #374151;
    background: #fff;
}
.myad-btn-sell:hover { border-color: #326916; color: #326916; background: #f0faf0; }

/* Sold badge (not a button) */
.myad-btn-sold {
    border-color: #fca5a5;
    color: #dc2626;
    background: #fff5f5;
    cursor: default;
}

/* Edit Ad */
.myad-btn-edit {
    border-color: #d1d5db;
    color: #374151;
    background: #fff;
}
.myad-btn-edit:hover { border-color: #6366f1; color: #4f46e5; background: #eef2ff; }
.myad-btn-edit.disabled { color: #d1d5db; border-color: #f3f4f6; background: #fafafa; cursor: not-allowed; pointer-events: none; }

/* Boost Ad */
.myad-btn-boost {
    border-color: #326916;
    color: #326916;
    background: #fff;
}
.myad-btn-boost:hover { background: #326916; color: #fff; }

/* Ad Boosted */
.myad-btn-boosted {
    border-color: #326916;
    color: #fff;
    background: #326916;
}
.myad-btn-boosted:hover { background: #2a5812; }

/* Boost disabled */
.myad-btn-boost-off {
    border-color: #f3f4f6;
    color: #d1d5db;
    background: #fafafa;
    cursor: not-allowed;
    pointer-events: none;
}

/* Status pills */
.myad-status-active {
    border-color: #bbf7d0;
    color: #15803d;
    background: #f0fdf4;
}
.myad-status-active:hover { border-color: #ef4444; color: #dc2626; background: #fff5f5; }

.myad-status-disabled {
    border-color: #fde68a;
    color: #92400e;
    background: #fffbeb;
}
.myad-status-disabled:hover { border-color: #86efac; color: #15803d; background: #f0fdf4; }

.myad-status-banned {
    border-color: #fca5a5;
    color: #dc2626;
    background: #fff5f5;
    cursor: not-allowed;
    pointer-events: none;
}

/* Shipping link */
.myad-btn-shipping {
    border-color: #bfdbfe;
    color: #1d4ed8;
    background: #eff6ff;
}
.myad-btn-shipping:hover { background: #dbeafe; }

/* ── Empty state ─────────────────────────────────────── */
.myad-empty {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    padding: 60px 20px;
    text-align: center;
    color: #9ca3af;
}
.myad-empty i { font-size: 3rem; color: #d1d5db; display: block; margin-bottom: 12px; }

/* ── Load more button ────────────────────────────────── */
.myad-load-more {
    width: 100%;
    padding: 11px;
    border-radius: 10px;
    border: 1.5px solid #326916;
    background: #fff;
    color: #326916;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.15s;
    margin-top: 8px;
}
.myad-load-more:hover { background: #326916; color: #fff; }
.myad-load-more:disabled { opacity: 0.5; cursor: not-allowed; }
</style>

<div class="myad-wrap">

    {{-- Page header --}}
    <div class="myad-page-header">
        <div class="myad-page-title">
            <i class="bi bi-badge-ad-fill"></i>
            Manage Adverts
        </div>
        <a href="/user/boosted" class="myad-boosted-link">
            <i class="bi bi-rocket-takeoff"></i> Boosted Ads
        </a>
    </div>

    @include('public.components.flash-message')

    <div id="ads-container">
        @include('user.components.my-ads', ['ads' => $ads])
    </div>

    @if(isset($hasMore) && $hasMore)
        <button id="load-more-btn" class="myad-load-more">
            <span id="load-more-text">Load More Adverts</span>
            <span id="load-more-spinner" class="hidden">
                <svg class="animate-spin h-4 w-4 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Loading…
            </span>
        </button>
    @endif

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let currentPage = 2;
    const loadMoreBtn = document.getElementById('load-more-btn');
    const loadMoreText = document.getElementById('load-more-text');
    const loadMoreSpinner = document.getElementById('load-more-spinner');
    const adsContainer = document.getElementById('ads-container');

    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function () {
            loadMoreBtn.disabled = true;
            loadMoreText.classList.add('hidden');
            loadMoreSpinner.classList.remove('hidden');

            fetch(`{{ route('user.ads.loadMore') }}?page=${currentPage}`)
                .then(r => r.json())
                .then(data => {
                    if (data.error) { alert(data.error); return; }
                    adsContainer.insertAdjacentHTML('beforeend', data.html);
                    currentPage++;
                    if (!data.hasMore) loadMoreBtn.style.display = 'none';
                    loadMoreBtn.disabled = false;
                    loadMoreText.classList.remove('hidden');
                    loadMoreSpinner.classList.add('hidden');
                })
                .catch(() => {
                    alert('Failed to load more ads. Please try again.');
                    loadMoreBtn.disabled = false;
                    loadMoreText.classList.remove('hidden');
                    loadMoreSpinner.classList.add('hidden');
                });
        });
    }
});
</script>

@include('user.layouts.footer')
