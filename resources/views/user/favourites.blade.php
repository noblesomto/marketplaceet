@include('user.layouts.header')
@include('user.layouts.nav')
@include('user.layouts.back-nav')
@include('user.layouts.search')

<section class="w-full md:w-3/6  mx-auto  text-sm">
    <div class="border-b-2 bg-white border-b-gray-200 p-4 font-bold text-dark_green mb-2">
        My Wishlist
        @include('public.components.flash-message')
    </div>

    <div class="pb-20">
        @include('public.components.advert.advert-list', ['ads' => $favoriteAds])

    </div>

    
</section>


@include('user.layouts.footer')
