@include('public.layouts.header-location')
@include('public.layouts.nav')
@include('public.components.mobile.mobile-nav')
@include('public.layouts.search')

@include('public.components.seo.intro-block')

<section class="w-full max-w-[95rem] mx-auto mt-3">
  <div class="grid grid-cols-12 gap-2">
      <div class="col-span-2  hidden sm:block">
          @include('public.components.advert.side-advert')
      </div>
      <div class="col-span-12 xl:col-span-8">
        <div class="grid grid-cols-12 gap-3">
           <div class="col-span-3 hidden sm:block p-2 ">
             @include('public.components.home.side-categories')
           </div>
           <div class="col-span-12 md:col-span-9">
              <div class=" my-5 hidden lg:block">
                 @include('public.components.advert.banner-advert')
              </div>

              <!-- Loading the Ads from Components -->
            <div id="ads-container" class="grid grid-cols-2 lg:grid-cols-4 xl:grid-cols-4 gap-2">
                    @foreach ($ads as $row)
                        @if($isMobile)
                            @include('public.components.advert.advert-location-mobile', ['row' => $row])
                        @else
                            @include('public.components.advert.advert-location', ['ads' => $row])
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
                            class="bg-dark_green hover:bg-secondary_dark text-white font-semibold py-3 px-8 rounded-lg transition duration-200 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 w-full">
                        <span id="load-more-text">See More</span>
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

@include('public.components.seo.faq-tips-block')

<div class="pb-20"></div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentPage = 2;
    const loadMoreBtn = document.getElementById('load-more-btn');
    const loadMoreText = document.getElementById('load-more-text');
    const loadMoreSpinner = document.getElementById('load-more-spinner');
    const adsContainer = document.getElementById('ads-container');

    // Build filters properly
    const filters = {};
    @if(isset($filterType) && isset($filterId))
        @if(isset($filterIsString) && $filterIsString)
            filters.{{ $filterType }} = "{{ $filterId }}"; // String value
        @else
            filters.{{ $filterType }} = {{ $filterId }}; // Numeric value
        @endif
    @endif

    if (loadMoreBtn) {
        loadMoreBtn.addEventListener('click', function() {
            loadMoreBtn.disabled = true;
            loadMoreText.classList.add('hidden');
            loadMoreSpinner.classList.remove('hidden');

            const params = new URLSearchParams({
                page: currentPage,
                ...filters
            });

            fetch(`{{ route('location.loadMore') }}?${params}`)
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

@include('public.layouts.footer')


