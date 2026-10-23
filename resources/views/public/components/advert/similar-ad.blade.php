@if($advertsCount > 0 || count($similar_ads) > 0)
    <!-- More from Seller -->
    @if($advertsCount > 0)
    <div class="mb-10 bg-white rounded-xl p-1 shadow-sm border border-gray-100">
        <div class="flex justify-between items-end mb-4 px-1 border-b pb-2 gap-2">
            <h3 class="font-bold text-base lg:text-xl text-gray-900 flex items-center gap-1 min-w-0 flex-1">
                <span class="shrink-0">More Ads from</span>
                <span class="truncate">
                    {{ \Illuminate\Support\Str::title(optional($ad->owner)->name ?? 'unknown') }}
                </span>
            </h3>
            <a href="/seller/{{ \Illuminate\Support\Str::slug($ad->owner->name) }}/{{ $ad->owner->user_id }}" class="text-sm font-semibold text-dark_green hover:underline shrink-0">View All</a>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2">
            @foreach ($adverts as $row)
                @if($isMobile)
                    @include('public.components.advert.advert-card-mobile', ['row' => $row])
                @else
                    @include('public.components.advert.advert-card', ['row' => $row])
                @endif
            @endforeach
        </div>
    </div>
    @endif

    <!-- Similar Items -->
    @if(count($similar_ads) > 0)
    <div class="bg-white rounded-xl p-1 shadow-sm border border-gray-100">
        <h3 class="font-bold text-base lg:text-xl text-gray-900 mb-6 border-b pb-2">Recommended for you</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-2">
            @foreach ($similar_ads as $row)
                @if($isMobile)
                        @include('public.components.advert.advert-card-mobile', ['row' => $row])
                    @else
                        @include('public.components.advert.advert-card', ['row' => $row])
                    @endif

            @endforeach
        </div>
    </div>
    @endif
@endif
