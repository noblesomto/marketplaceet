@include('public.layouts.header')
@include('public.layouts.nav')
@include('public.components.mobile.mobile-nav')
@include('public.layouts.search')

@include('public.components.seo.intro-block')

<section class="w-full max-w-[95rem] mx-auto mt-3">
    <div id="ads-container" class="grid grid-cols-2 lg:grid-cols-4 xl:grid-cols-4 gap-2 px-1">
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

    @if(isset($hasMore) && $hasMore && isset($moreUrl))
        <div class="mt-3 mb-4 px-2 flex justify-center pb-20">
            <a href="{{ $moreUrl }}"
               class="bg-dark_green hover:bg-secondary_dark text-white font-semibold py-3 px-8 rounded-lg transition duration-200 ease-in-out transform hover:scale-105">
                See More
            </a>
        </div>
    @endif
</section>

@include('public.components.seo.faq-tips-block')

<div class="pb-10"></div>

@include('public.layouts.footer')
