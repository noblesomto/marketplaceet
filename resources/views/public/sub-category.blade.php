{{-- Dynamic header based on context --}}
@if(isset($location))
    @include('public.layouts.header')
@else
    @include('public.layouts.header-subcategory')
@endif

@include('public.layouts.nav')
@include('public.components.mobile.mobile-category-header-nav', [
    'backUrl'   => url('/category/' . $cat->category_slug),
    'pageTitle' => $subcat->sub_category,
])
@include('public.layouts.search')

@include('public.components.seo.intro-block')

<section class="w-full lg:max-w-[95rem] mx-auto mt-3">
  <div class="grid grid-cols-12 gap-3">
      <div class="col-span-2 hidden lg:block">
        @include('public.components.advert.side-advert')
      </div>
      <div class="col-span-12 lg:col-span-8">
        <div class="grid grid-cols-12 gap-3">
           <div class="col-span-3 hidden lg:block space-y-4 pb-20">
            <!-- Header Title -->
            <div class="flex items-center gap-2 px-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <h4 class="font-bold text-gray-800 text-lg tracking-tight">Refine Search</h4>
            </div>

            <!-- Sub-Category & Brands Card -->
            <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
                <div class="p-4 border-b border-gray-50 bg-gray-50/50">
                    <h4 class="font-bold text-gray-900 text-sm uppercase tracking-wider">Categories</h4>
                </div>

                <div class="p-3">
                    <!-- Breadcrumb for SEO -->
                    <nav class="mb-4" aria-label="Breadcrumb">
                        <a href="/all-categories" class="inline-flex items-center text-xs font-medium text-secondary_dark hover:text-dark_green transition-colors">
                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                            All Categories
                        </a>
                    </nav>

                    <!-- Active Sub-Category State -->
                    <div class="flex items-center justify-between p-2.5 mb-3 rounded-lg bg-emerald-50 border border-emerald-100">
                        <span class="font-bold text-emerald-900 text-[15px]">{{ $subcat->sub_category }}</span>
                        <span class="text-xs font-bold bg-white text-emerald-700 px-2 py-1 rounded-full shadow-sm">
                            {{ $count_subcat }}
                        </span>
                    </div>

                    @if(!isset($location) && isset($brands))
                    <!-- Brands within this Sub-Category (Only show when NOT location-based) -->
                    @php $brandLimit = 15; @endphp
                    <ul class="space-y-0.5">
                        @foreach($brands->take($brandLimit) as $brand)
                        <li>
                            <a href="{{ url('/category/' . $subcat->category->category_slug . '/' . $subcat->sub_cat_slug . '/' . $brand->brand_slug) }}"
                               class="group flex items-center justify-between p-2 rounded-md hover:bg-gray-50 transition-all duration-200">
                                <span class="text-gray-600 group-hover:text-secondary_dark text-[14px] leading-tight transition-colors font-bold">
                                    {{ $brand->brand }}
                                </span>
                                <span class="text-[11px] text-gray-500 font-medium group-hover:text-dark_green transition-colors">
                                     ({{ $brand->advert_count }})
                                </span>
                            </a>
                        </li>
                        @endforeach
                    </ul>

                    @if($brands->count() > $brandLimit)
                    <div class="mt-4 pt-3 border-t border-gray-100">
                        <a href="{{ url('/category/' . $subcat->category->category_slug . '/' . $subcat->sub_cat_slug . '/all-'.$subcat->sub_cat_slug) }}"
                           class="flex items-center justify-center text-emerald-600 text-xs font-bold hover:text-emerald-700 transition-colors uppercase tracking-wide">
                            See all {{ $subcat->sub_category }}
                        </a>
                    </div>
                    @endif
                    @endif
                </div>
            </div>

            <!-- Filters Group -->
            <div class="space-y-3">
                <!-- Location Section -->
                <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
                    <h4 class="font-semibold text-gray-900 text-sm mb-3">Location</h4>
                    <button id="locationButton" class="w-full flex items-center justify-between px-3 py-2 text-sm font-semibold text-gray-600 border border-gray-200 rounded-lg hover:border-emerald-500 hover:text-emerald-600 transition-all focus:ring-2 focus:ring-emerald-100 outline-none">
                        <span>Select Location</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-dark_green" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </button>
                </div>

                <!-- Price Section -->
                <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
                    <h4 class="font-bold text-gray-900 text-sm mb-3">Price Range</h4>
                    @include('public.components.advert.price-filter')
                </div>

                @if(!isset($location))
                <!-- Purchase Type Section (Only show when NOT location-based) -->
                <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
                    <h4 class="font-bold text-gray-900 text-sm mb-3">Buying Options</h4>
                    @include('public.components.filter.buydirect-subcategory')
                </div>

                <!-- Verified Sellers Section (Only show when NOT location-based) -->
                <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
                    <h4 class="font-bold text-gray-900 text-sm mb-3">Trust & Safety</h4>
                    @include('public.components.advert.sellers-subcategory')
                </div>
                @endif

                @if($subcat->cat_id == 1)
                <!-- Vehicle Condition -->
                <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
                    <h4 class="font-bold text-gray-900 text-sm mb-3">Vehicle Condition</h4>
                    @include('public.components.filter.condition-car')
                </div>

                <!-- Registration -->
                <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
                    <h4 class="font-bold text-gray-900 text-sm mb-3">Registration</h4>
                    @include('public.components.filter.registration')
                </div>

                <!-- Fuel Type -->
                <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
                    <h4 class="font-bold text-gray-900 text-sm mb-3">Fuel Type</h4>
                    @include('public.components.filter.fuel-type')
                </div>

                <!-- Transmission -->
                <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
                    <h4 class="font-bold text-gray-900 text-sm mb-3">Transmission</h4>
                    @include('public.components.filter.transmission')
                </div>
                @elseif($subcat->cat_id == 4)
                <!-- Phone Condition -->
                <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
                    <h4 class="font-bold text-gray-900 text-sm mb-3">Condition</h4>
                    @include('public.components.filter.condition-phone')
                </div>

                <!-- Device Type -->
                <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
                    <h4 class="font-bold text-gray-900 text-sm mb-3">Device Type</h4>
                    @include('public.components.filter.device-type')
                </div>
                @endif

                <!-- Brands Selection Section -->
                <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
                    <h4 class="font-bold text-gray-900 text-sm mb-3">Specific Brands</h4>
                    <button id="brandsButton" class="w-full flex items-center justify-between px-3 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:border-emerald-500 hover:text-emerald-600 transition-all focus:ring-2 focus:ring-emerald-100 outline-none">
                        <span>Select Brand</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                    </button>
                </div>
            </div>
        </div>
           <div class="col-span-12 lg:col-span-9">
              <div class=" my-5 hidden lg:block">
                 @include('public.components.advert.banner-advert')
              </div>
              <div class="block lg:hidden">
                    @include('public.components.mobile.filter-subcategory')
              </div>
                <div id="ads-container" class="grid grid-cols-2 lg:grid-cols-4 xl:grid-cols-4 gap-2">
                    @foreach ($ads as $row)
                        @if($isMobile)
                            @include('public.components.advert.advert-card-mobile', ['row' => $row])
                        @else
                            @include('public.components.advert.advert-card', ['row' => $row])
                        @endif
                    @endforeach
                </div>

            @if($ads->isEmpty())
                <div class="flex flex-col h-screen items-center bg-white p-10">
                    <span>
                        <img width="100" height="100" src="https://img.icons8.com/external-outline-andi-nur-abdillah/100/external-Empty-empty-state-(outline)-outline-andi-nur-abdillah.png" alt="No Adverts Currently"/>
                    </span>
                    <span>No Item here yet...</span>
                </div>
            @endif

            @if(isset($hasMore) && $hasMore)
                <div class="mt-3 mb-4 px-2 flex justify-center pb-20">
                    <button id="load-more-btn"
                            class="bg-dark_green hover:bg-secondary_dark text-white font-semibold py-3 px-20 rounded-lg transition duration-200 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        <span id="load-more-text">Show More</span>
                        <span id="load-more-spinner" class="hidden">
                            <svg class="animate-spin h-5 w-5 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Loading...
                        </span>
                    </button>
                </div>
            @endif

           </div>
        </div>
      </div>
      <div class="col-span-2  hidden lg:block">
        @include('public.components.advert.side-advert')
      </div>
  </div>
</section>

@include('public.components.seo.faq-tips-block')

<div class="pb-20"></div>


@include('public.components.advert.modal-subcat-locations')
@include('public.components.advert.modal-filter-brands')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 2;
    const loadMoreBtn = document.getElementById('load-more-btn');
    const loadMoreText = document.getElementById('load-more-text');
    const loadMoreSpinner = document.getElementById('load-more-spinner');
    const adsContainer = document.getElementById('ads-container');

    const filters = {
        @if(isset($subcat))
        sub_category: {{ $subcat->id }},
        @endif
        @if(isset($location))
        location: '{{ $location }}',
        @endif
    };

    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            loadMoreBtn.disabled = true;
            loadMoreText.classList.add('hidden');
            loadMoreSpinner.classList.remove('hidden');

            const params = new URLSearchParams({
                page: currentPage,
                ...filters
            });

            fetch(`{{ route('search.loadMore') }}?${params}`)
                .then(response => response.json())
                .then(data => {
                    if (data.html) {
                        adsContainer.insertAdjacentHTML('beforeend', data.html);
                        currentPage++;
                    }

                    if (!data.hasMore) {
                        loadMoreBtn.style.display = 'none';
                    }

                    loadMoreBtn.disabled = false;
                    loadMoreText.classList.remove('hidden');
                    loadMoreSpinner.classList.add('hidden');
                })
                .catch(error => {
                    console.error('Error loading more ads:', error);
                    alert('Failed to load more ads. Please try again.');

                    loadMoreBtn.disabled = false;
                    loadMoreText.classList.remove('hidden');
                    loadMoreSpinner.classList.add('hidden');
                });
        });
    }
});
</script>

<!-- Filter Manager Context -->
<script>
    // Set context variables for filter manager
    const currentCategory = {{ $subcat->cat_id }};
    const currentSubCategory = {{ $subcat->id }};
    const currentBrand = null;
    const currentLocation = {{ isset($location) ? "'".$location."'" : 'null' }};
</script>
<script src="{{ asset('frontend/js/filter-manager.js') }}"></script>

@include('public.layouts.footer')
