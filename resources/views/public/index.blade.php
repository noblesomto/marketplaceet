@include('public.layouts.header')
@include('public.layouts.nav')
@include('public.components.mobile.mobile-nav')
@include('public.layouts.search')

<section class="w-full xl:w-4/6 mx-auto bg-white md:bg-body pb-20">

  @if($isMobile)
    {{-- MOBILE VERSION --}}
    <div class="bg-white pt-3 ml-2">
      <div>
        <span class="text-base"><strong>Marketplace Ethiopia</strong> - Buy. Sell. Discover Deals.</span>
      </div>
    </div>

    <div class="my-3 w-full overflow-hidden">
      @include('public.components.advert.banner-advert')
    </div>

    <!-- Important Notice Banner -->
    <div id="mobile-notice-banner" class="mx-3 my-3 bg-gray-100 border border-gray-300 rounded-2xl px-4 py-3 relative">
      <button onclick="document.getElementById('mobile-notice-banner').style.display='none'" class="absolute top-3 right-3 text-gray-500 hover:text-gray-800 text-lg leading-none">&times;</button>
      <p class="font-bold text-gray-900 text-sm mb-1 pr-5">Important Notice</p>
      <p class="text-gray-700 text-sm leading-snug pr-4">Do not make any advance payments, including for delivery or reservations. Use &ldquo;Buy Direct&rdquo; (where available) for full Buyer Protection and a 100% refund if issues arise.</p>
    </div>

    <!-- Mobile Category Icons -->
    <div class="bg-white shadow-sm border-t border-gray-100">
      <div class="flex justify-between items-center py-2 px-1">
        <!-- Vehicles -->
        <a href="/category/vehicles" class="flex-1 flex flex-col items-center group text-center px-1">
          <div class="bg-secondary_dark text-white w-12 h-12 rounded-full flex items-center justify-center group-hover:bg-secondary_dark text-white-dark transition-colors duration-200">
            <img class="w-6 h-6" width="24" height="24" src="{{ asset('frontend/images/icons/car-100.png') }}" alt="Category Vehicles">
          </div>
          <span class="text-[10px] sm:text-xs mt-1 text-gray-700 group-hover:text-primary transition-colors duration-200 whitespace-nowrap truncate">
            Vehicles
          </span>
        </a>

        <!-- Phones & Tablets -->
        <a href="/category/mobile-phones-and-tablets" class="flex-1 flex flex-col items-center group text-center px-1">
          <div class="bg-secondary_dark text-white w-12 h-12 rounded-full flex items-center justify-center group-hover:bg-primary-dark transition-colors duration-200">
            <img class="w-6 h-6" width="24" height="24" src="{{ asset('frontend/images/icons/mobile-100.png') }}" alt="Category Phones and Tablets">
          </div>
          <span class="text-[10px] sm:text-xs mt-1 text-gray-700 group-hover:text-primary transition-colors duration-200 whitespace-nowrap truncate">
            Phones &amp; Tablets
          </span>
        </a>

        <!-- Real Estate -->
        <a href="/category/real-estate" class="flex-1 flex flex-col items-center group text-center px-1">
          <div class="bg-secondary_dark text-white w-12 h-12 rounded-full flex items-center justify-center group-hover:bg-primary-dark transition-colors duration-200">
            <img class="w-6 h-6" width="24" height="24" src="{{ asset('frontend/images/icons/house-100.png') }}" alt="Category Real Estate">
          </div>
          <span class="text-[10px] sm:text-xs mt-1 text-gray-700 group-hover:text-primary transition-colors duration-200 whitespace-nowrap truncate">
            Real Estate
          </span>
        </a>

        <!-- Fashion & Beauty -->
        <a href="/category/fashion" class="flex-1 flex flex-col items-center group text-center px-1">
          <div class="bg-secondary_dark text-white w-12 h-12 rounded-full flex items-center justify-center group-hover:bg-primary-dark transition-colors duration-200">
            <img class="w-6 h-6" width="24" height="24" src="{{ asset('frontend/images/icons/fashion-100.png') }}" alt="Category Fashion and Beauty">
          </div>
          <span class="text-[10px] sm:text-xs mt-1 text-gray-700 group-hover:text-primary transition-colors duration-200 whitespace-nowrap truncate">
            Fashion &amp; Beauty
          </span>
        </a>

        <!-- All Categories -->
        <a href="/all-categories" class="flex-1 flex flex-col items-center group text-center px-1">
          <div class="bg-secondary_dark text-white w-12 h-12 rounded-full flex items-center justify-center group-hover:bg-primary-dark transition-colors duration-200">
            <img class="w-6 h-6" width="24" height="24" src="{{ asset('frontend/images/icons/list-100.png') }}" alt="All Categories">
          </div>
          <span class="text-[10px] sm:text-xs mt-1 text-gray-700 group-hover:text-primary transition-colors duration-200 whitespace-nowrap truncate">
            All Categories
          </span>
        </a>
      </div>
    </div>

    <!-- Mobile Gallery -->
    <div class="mt-4">
      @include('public.components.mobile.mobile-gallery')
    </div>

    <!-- Mobile Listings -->
    <div class="mt-6 pb-5">
      <h4 class="font-semibold text-lg px-4 mb-3 text-gray-800">Recent Listings</h4>
      <div id="listings-container-mobile"
           class="grid grid-cols-2 gap-2 px-2"
           data-load-more-url="{{ route('load.more.ads.mobile') }}">
          @foreach ($listings as $row)
              @include('public.components.advert.advert-card-mobile', ['row' => $row])
          @endforeach
      </div>

      <div class="flex justify-center m-3">
          <button id="load-more-btn-mobile"
                  class="bg-dark_green hover:bg-green-700 text-white font-semibold py-3 px-8 rounded-lg shadow-md transition duration-200 ease-in-out transform hover:scale-105 w-full">
              Show More
          </button>
          <div id="loading-mobile" class="hidden">
              <svg class="animate-spin h-8 w-8 text-dark_green" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
          </div>
      </div>

      <div id="no-more-ads-mobile" class="hidden text-center py-4 text-gray-500 pb-20">
          <p class="font-medium">No more listings to show</p>
      </div>
    </div>

  @else
    {{-- DESKTOP VERSION --}}
    <div class="my-5">
      @include('public.components.advert.banner-advert')
    </div>

    <div class="grid grid-cols-8 gap-3">

      <div class="col-span-8">
        <div>
            @include('public.components.home.categories')
        </div>
        <div class="bg-white p-2">
          @include('public.components.home.gallery')
        </div>
        <div>
          @include('public.components.home.homeads')
        </div>
      </div>
    </div>
  @endif

</section>

@include('public.layouts.footer')
