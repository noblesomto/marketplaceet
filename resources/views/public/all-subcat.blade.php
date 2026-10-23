@include('public.layouts.header')
@include('public.layouts.nav')
@include('public.components.mobile.mobile-nav')
@include('public.layouts.search')

<section class="container mx-auto px-4 py-8 max-w-6xl pb-20">
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">
            {{ $subcat->sub_category }}
        </h1>

        <!-- Grid on desktop, list on mobile -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
            @if($subcat->sub_category == "Cars" || $subcat->sub_category == "Mobile Phones")
                @foreach($brands as $brand)
                    <!-- Card style on desktop, list item on mobile -->
                    <div class="md:bg-gray-50 md:hover:bg-gray-100 md:rounded-lg md:p-4 transition duration-200 border-b border-gray-100 last:border-0 md:border-0">
                        <a href="/category/{{ strtolower($cat->category) }}/{{ strtolower($subcat->sub_category) }}/{{ strtolower($brand->brand) }}"
                           class="flex items-center justify-between py-3 md:py-0">
                            <span class="font-medium text-gray-700 lowercase">{{ $brand->brand }}</span>
                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                {{ $brand->advert_count }}
                            </span>
                        </a>
                    </div>
                @endforeach
            @else
                @foreach($brands as $brand)
                    <!-- Always list style for this condition -->
                    <div class="md:bg-gray-50 md:hover:bg-gray-100 md:rounded-lg md:p-4 transition duration-200 border-b border-gray-100 last:border-0 md:border-0">
                        <a href="/brand/{{ $brand->id }}/{{ $brand->brand_slug }}" class="flex items-center justify-between py-3 md:py-0">
                            <span class="font-medium text-gray-700">{{ $brand->brand }}</span>
                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                {{ $brand->advert_count }}
                            </span>
                        </a>
                    </div>
                @endforeach
            @endif
        </div>
    </div>
</section>

@include('public.layouts.footer')
