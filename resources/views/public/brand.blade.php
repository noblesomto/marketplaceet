{{-- Dynamic header based on context --}}
@if(isset($location))
    @include('public.layouts.header')
@else
    @include('public.layouts.header-brand')
@endif

@include('public.layouts.nav')
@include('public.components.mobile.mobile-nav')
@include('public.layouts.search')

@include('public.components.seo.intro-block')

<section class="w-full lg:max-w-[95rem] mx-auto mt-3">
  <div class="grid grid-cols-12 gap-3">
      <div class="col-span-2 hidden lg:block">
        @include('public.components.advert.side-advert')
      </div>
      <div class="col-span-12 lg:col-span-8">
        <div class="grid grid-cols-12 gap-3">
           <div class="col-span-3 hidden lg:block space-y-4">
            <!-- Main Sidebar Header -->
            <div class="flex items-center gap-2 px-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <h4 class="font-bold text-gray-800 text-lg tracking-tight">Filter Results</h4>
            </div>

            <!-- Locations Card -->
            <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
                <h4 class="font-semibold text-gray-900 text-sm mb-3 uppercase tracking-wider text-[11px]">Locations</h4>
                <button id="locationButton" class="w-full flex items-center justify-between px-3 py-2.5 text-sm text-gray-600 border border-gray-200 rounded-lg hover:border-dark_green hover:text-dark_green transition-all focus:ring-2 focus:ring-emerald-100 outline-none group">
                    <span class="flex items-center gap-2 font-bold">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 group-hover:text-dark_green transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Select Location
                    </span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 opacity-30 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            <!-- Price Card -->
            <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
                <h4 class="font-bold text-gray-900 text-sm mb-3 uppercase tracking-wider text-[11px]">Price Range</h4>
                <div class="custom-filter-content">
                    @include('public.components.advert.price-filter-brand')
                </div>
            </div>

            @if(isset($models) && $models->isNotEmpty())
            <!-- Shop by Model (location-based brand pages, vehicles/mobile-phones only) -->
            <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
                <h4 class="font-bold text-gray-900 text-sm mb-3 uppercase tracking-wider text-[11px]">Shop by Model</h4>
                @php $modelLimit = 15; @endphp
                <ul class="space-y-0.5">
                    @foreach($models->take($modelLimit) as $catModel)
                    <li>
                        <a href="{{ url('/' . $location . '/' . $cat->category_slug . '/' . $catModel->model_slug) }}"
                           class="group flex items-center justify-between p-2 rounded-md hover:bg-gray-50 transition-all duration-200">
                            <span class="text-gray-600 group-hover:text-secondary_dark text-[14px] leading-tight transition-colors font-bold">
                                {{ $catModel->model }}
                            </span>
                            <span class="text-[11px] text-gray-500 font-medium group-hover:text-secondary_dark transition-colors">
                                 ({{ $catModel->advert_count }})
                            </span>
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if(!isset($location))
            <!-- Buy Directly Card (Only show when NOT location-based) -->
            <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
                <h4 class="font-bold text-gray-900 text-sm mb-3 uppercase tracking-wider text-[11px]">Ordering</h4>
                <div class="custom-filter-content">
                    @include('public.components.filter.buydirect-brand')
                </div>
            </div>

            <!-- Verified Sellers Card (Only show when NOT location-based) -->
            <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
                <h4 class="font-bold text-gray-900 text-sm mb-3 flex items-center gap-2 uppercase tracking-wider text-[11px]">
                    Trust & Safety
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.64.304 1.24.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </h4>
                <div class="custom-filter-content">
                    @include('public.components.advert.sellers-brand')
                </div>
            </div>
            @endif

            @if(isset($subcat) && $subcat->cat_id == 1)
            <!-- Vehicle Condition -->
            <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
                <h4 class="font-bold text-gray-900 text-sm mb-3 uppercase tracking-wider text-[11px]">Vehicle Condition</h4>
                @include('public.components.filter.condition-car')
            </div>

            <!-- Registration -->
            <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
                <h4 class="font-bold text-gray-900 text-sm mb-3 uppercase tracking-wider text-[11px]">Registration</h4>
                @include('public.components.filter.registration')
            </div>

            <!-- Fuel Type -->
            <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
                <h4 class="font-bold text-gray-900 text-sm mb-3 uppercase tracking-wider text-[11px]">Fuel Type</h4>
                @include('public.components.filter.fuel-type')
            </div>

            <!-- Transmission -->
            <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
                <h4 class="font-bold text-gray-900 text-sm mb-3 uppercase tracking-wider text-[11px]">Transmission</h4>
                @include('public.components.filter.transmission')
            </div>
            @elseif(isset($subcat) && $subcat->cat_id == 4)
            <!-- Phone Condition -->
            <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
                <h4 class="font-bold text-gray-900 text-sm mb-3 uppercase tracking-wider text-[11px]">Condition</h4>
                @include('public.components.filter.condition-phone')
            </div>

            <!-- Device Type -->
            <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
                <h4 class="font-bold text-gray-900 text-sm mb-3 uppercase tracking-wider text-[11px]">Device Type</h4>
                @include('public.components.filter.device-type')
            </div>
            @endif
        </div>
           <div class="col-span-12 lg:col-span-9">
              <div class=" my-5 hidden lg:block">
                 @include('public.components.advert.banner-advert')
              </div>
              <div class="block lg:hidden">
                    @include('public.components.mobile.filter-brand')
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


@include('public.components.advert.modal-brand-locations')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 2; // Start from page 2 since page 1 is already loaded
    const loadMoreBtn = document.getElementById('load-more-btn');
    const loadMoreText = document.getElementById('load-more-text');
    const loadMoreSpinner = document.getElementById('load-more-spinner');
    const adsContainer = document.getElementById('ads-container');

    // Get current filters from URL or other source
    const filters = {
        @if(isset($cat))
        category: {{ $cat->id }},
        @endif
        @if(isset($subcat))
        sub_category: {{ $subcat->id }},
        @endif
        @if(isset($brand))
        brand: {{ $brand->id }},
        @endif
        @if(isset($location))
        location: '{{ $location }}',
        @endif
    };

    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            // Disable button and show spinner
            loadMoreBtn.disabled = true;
            loadMoreText.classList.add('hidden');
            loadMoreSpinner.classList.remove('hidden');

            // Build query string
            const params = new URLSearchParams({
                page: currentPage,
                ...filters
            });

            fetch(`{{ route('search.loadMore') }}?${params}`)
                .then(response => response.json())
                .then(data => {
                    // Append new ads
                    adsContainer.insertAdjacentHTML('beforeend', data.html);

                    // Increment page
                    currentPage++;

                    // Hide button if no more ads
                    if (!data.hasMore) {
                        loadMoreBtn.style.display = 'none';
                    }

                    // Re-enable button
                    loadMoreBtn.disabled = false;
                    loadMoreText.classList.remove('hidden');
                    loadMoreSpinner.classList.add('hidden');
                })
                .catch(error => {
                    console.error('Error loading more ads:', error);
                    alert('Failed to load more ads. Please try again.');

                    // Re-enable button
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
    const currentCategory = {{ $brand->category_id }};
    const currentSubCategory = {{ $brand->sub_cat_id ?? 'null' }};
    const currentBrand = {{ $brand->id }};
    const currentLocation = {{ isset($location) ? "'".$location."'" : 'null' }};
</script>
<script src="{{ asset('frontend/js/filter-manager.js') }}"></script>

@include('public.layouts.footer')
