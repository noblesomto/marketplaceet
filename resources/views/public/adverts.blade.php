@include('public.layouts.header')
@include('public.layouts.nav')
@include('public.components.mobile.mobile-nav')
@include('public.layouts.search')

<section class="w-full max-w-[100rem] mx-auto mt-3">
  <div class="grid grid-cols-12 gap-3">
      <div class="col-span-2  hidden sm:block">
          @include('public.components.advert.side-advert')
      </div>
      <div class="col-span-12 md:col-span-8">
        <div class="grid grid-cols-12 gap-3">
           <div class="col-span-3 hidden sm:block bg-white p-2">
             @include('public.components.home.side-categories')
           </div>
           <div class="col-span-12 md:col-span-9">
              <div class=" my-5 hidden lg:block">
                 @include('public.components.advert.banner-advert')
              </div>

              <!-- Loading the Ads from Components -->
                <div id="advert-results" class="grid grid-cols-2 lg:grid-cols-4 xl:grid-cols-4 gap-2">
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
                <div class="mt-3 mb-4 px-2 flex justify-center">
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
      <div class="col-span-2 hidden sm:block">
        @include('public.components.advert.side-advert')
      </div>
  </div>
</section>
<div class="pb-20"></div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 2; // Start from page 2 since page 1 is already loaded
    const loadMoreBtn = document.getElementById('load-more-btn');
    const loadMoreText = document.getElementById('load-more-text');
    const loadMoreSpinner = document.getElementById('load-more-spinner');
    const adsContainer = document.getElementById('advert-results');

    // Get search parameters
    const filters = {
        @if(isset($searchParams['product']) && $searchParams['product'])
        product: '{{ $searchParams['product'] }}',
        @endif
        @if(isset($searchParams['category']) && $searchParams['category'])
        category: {{ $searchParams['category'] }},
        @endif
        @if(isset($searchParams['sub_category']) && $searchParams['sub_category'])
        sub_category: {{ $searchParams['sub_category'] }},
        @endif
        @if(isset($searchParams['brand']) && $searchParams['brand'])
        brand: {{ $searchParams['brand'] }},
        @endif
        @if(isset($searchParams['location']) && $searchParams['location'])
        location: '{{ $searchParams['location'] }}',
        @endif
        @if(isset($searchParams['buydirect']) && $searchParams['buydirect'])
        buydirect: '{{ $searchParams['buydirect'] }}',
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

@include('public.layouts.footer')


