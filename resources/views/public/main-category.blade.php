{{-- Dynamic header based on context --}}
@if(isset($location))
    @include('public.layouts.header')
@else
    @include('public.layouts.header-category')
@endif

@include('public.layouts.nav')
@include('public.components.mobile.mobile-category-header-nav', [
    'backUrl'   => url('/all-categories'),
    'pageTitle' => $cat->category,
])
@include('public.layouts.search')

@include('public.components.seo.intro-block')

<section class="w-full max-w-[95rem] mx-auto mt-3">
  <div class="grid grid-cols-12 gap-2">
      <div class="col-span-2 hidden xl:block">
        @include('public.components.advert.side-advert')
      </div>
      <div class="col-span-12 xl:col-span-8">
        <div class="my-5 hidden md:block">
                 @include('public.components.advert.banner-advert')
              </div>
        <div class="grid grid-cols-12 gap-3">
           <div class="col-span-3 hidden lg:block space-y-4">
    <!-- Header Title -->
    <div class="flex items-center gap-2 px-1">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
        </svg>
        <h4 class="font-bold text-gray-800 text-lg tracking-tight">Filter Results</h4>
    </div>

    <!-- Categories Card -->
    <div class="bg-white rounded-xl shadow-md border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-50 bg-gray-50/50">
            <h4 class="font-bold text-gray-900 text-sm uppercase tracking-wider">Categories</h4>
        </div>

        <div class="p-3">
            <!-- Breadcrumb Navigation for SEO -->
            <nav class="mb-4" aria-label="Breadcrumb">
                <a href="{{ url('/all-categories') }}" class="inline-flex items-center text-xs font-medium text-secondary_dark hover:text-dark_green transition-colors">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                    All Categories
                </a>
            </nav>

            <!-- Active Category State -->
            <div class="flex items-center justify-between p-2.5 mb-3 rounded-lg bg-emerald-50 border border-emerald-100 group">
                <span class="font-bold text-emerald-900 text-[15px]">{{ $cat->category }}</span>
                <span class="text-xs font-bold bg-white text-emerald-700 px-2 py-1 rounded-full shadow-sm">{{ $count_cat }}</span>
            </div>

            @if(!isset($location))
            <!-- Subcategories List (Only show when NOT location-based) -->
            @php $catLimit = 15; @endphp
            <ul class="space-y-0.5">
                @foreach($categories->take($catLimit) as $subCategory)
                <li>
                    <a href="{{ url('/category/' . $cat->category_slug . '/' . $subCategory->sub_cat_slug) }}"
                       class="group flex items-center justify-between p-2 rounded-md hover:bg-gray-50 transition-all duration-200">
                        <span class="text-gray-600 group-hover:text-secondary_dark text-[14px] leading-tight transition-colors font-bold">
                            {{ $subCategory->sub_category }}
                        </span>
                        <span class="text-[11px] text-gray-500 font-medium group-hover:text-secondary_dark transition-colors">
                             ({{ $subCategory->advert_count }})
                        </span>
                    </a>
                </li>
                @endforeach
            </ul>

            @if($categories->count() > $catLimit)
            <div class="mt-4 pt-3 border-t border-gray-100">
                <a href="{{ url('/category/'.$cat->category_slug) }}"
                   class="flex items-center justify-center text-emerald-600 text-xs font-bold hover:text-emerald-700 transition-colors uppercase tracking-wide">
                    See all in {{ $cat->category }}
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
            <h4 class="font-bold text-gray-900 text-sm mb-3">Location</h4>
            <button id="locationButton" class="w-full flex items-center justify-between px-3 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:border-emerald-500 hover:text-emerald-600 transition-all focus:ring-2 focus:ring-emerald-100 outline-none font-bold">
                <span>Select Location</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            </button>
        </div>

        <!-- Price Section -->
        <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
            <h4 class="font-bold text-gray-900 text-sm mb-3">Price Range</h4>
            <div class="custom-filter-wrapper">
                @include('public.components.advert.price-filter')
            </div>
        </div>

        @if(!isset($location))
        <!-- Purchase Type Section (Only show when NOT location-based) -->
        <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
            <h4 class="font-bold text-gray-900 text-sm mb-3">Buying Options</h4>
            @include('public.components.filter.buydirect-category')
        </div>

        <!-- Verified Sellers (Only show when NOT location-based) -->
        <div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
            <h4 class="font-bold text-gray-900 text-sm mb-3">Trust Safety</h4>
            @include('public.components.advert.sellers-category')
        </div>
        @endif

        @if($cat->id == 1)
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
        @elseif($cat->id == 4)
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
    </div>
</div>

           <div class="col-span-12 lg:col-span-9">

              {{-- ── MOBILE: subcategory list ───────────────────── --}}
              <div class="block lg:hidden">
                <div class="bg-white divide-y divide-gray-100 rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-4">
                  @foreach($categories as $subcat)
                  <a href="{{ url('/category/' . $cat->category_slug . '/' . $subcat->sub_cat_slug) }}"
                     class="flex items-center gap-3 px-4 py-3 active:bg-gray-50">

                    {{-- Icon: uploaded admin icon > ad thumbnail > default SVG --}}
                    <div class="w-[60px] h-[60px] rounded-lg overflow-hidden bg-gray-100 flex-shrink-0">
                      @if($subcat->icon)
                        <img src="{{ asset('frontend/images/subcategory-icons/' . $subcat->icon) }}"
                             alt="{{ $subcat->sub_category }}"
                             loading="lazy"
                             class="w-full h-full object-cover">
                      @elseif(isset($subcatImages[$subcat->id]))
                        <img src="{{ $subcatImages[$subcat->id] }}"
                             alt="{{ $subcat->sub_category }}"
                             loading="lazy"
                             class="w-full h-full object-cover">
                      @else
                        <div class="w-full h-full flex items-center justify-center">
                          <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                          </svg>
                        </div>
                      @endif
                    </div>

                    {{-- Name + count --}}
                    <div class="flex-1 min-w-0">
                      <div class="font-semibold text-gray-900 text-[15px]">{{ $subcat->sub_category }}</div>
                      <div class="text-sm text-gray-500 mt-0.5">{{ number_format($subcat->advert_count) }} ads</div>
                    </div>

                    {{-- Chevron --}}
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                  </a>
                  @endforeach
                </div>
              </div>

              {{-- ── DESKTOP: ads grid (unchanged) ────────────────── --}}
              <div class="hidden lg:block">
                <div id="ads-container" class="grid grid-cols-4 xl:grid-cols-4 gap-2 px-1">
                    @foreach ($ads as $row)
                        @include('public.components.advert.advert-card', ['row' => $row])
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
                                class="bg-dark_green hover:bg-secondary_dark text-white font-semibold py-3 px-20 rounded-lg transition duration-200 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 ">
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
      </div>
      <div class="col-span-2 hidden xl:block">
        @include('public.components.advert.side-advert')
      </div>
  </div>
</section>

@include('public.components.seo.faq-tips-block')

<div class="pb-10"></div>
@include('public.components.advert.modal-locations')
@include('public.components.advert.modal-filter-brands')

<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 2;
    const loadMoreBtn = document.getElementById('load-more-btn');
    const loadMoreText = document.getElementById('load-more-text');
    const loadMoreSpinner = document.getElementById('load-more-spinner');
    const adsContainer = document.getElementById('ads-container');

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
            loadMoreBtn.disabled = true;
            loadMoreText.classList.add('hidden');
            loadMoreSpinner.classList.remove('hidden');

            const params = new URLSearchParams({ page: currentPage, ...filters });

            fetch(`{{ route('search.loadMore') }}?${params}`)
                .then(response => response.json())
                .then(data => {
                    adsContainer.insertAdjacentHTML('beforeend', data.html);
                    currentPage++;
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

<script>
    const currentCategory = {{ $cat->id }};
    const currentSubCategory = null;
    const currentBrand = null;
    const currentLocation = {{ isset($location) ? "'".$location."'" : 'null' }};
</script>
<script src="{{ asset('frontend/js/filter-manager.js') }}"></script>

@include('public.layouts.footer')
