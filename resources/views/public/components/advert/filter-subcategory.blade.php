@php
    $brands = get_brands_with_advert_count($cat->id);
@endphp
 @php $catLimit = 15; @endphp
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 max-w-7xl">
    @foreach($categories as $subCategory)
        <div class="bg-white border border-gray-200 rounded-lg hover:shadow-sm transition duration-200">
            <a class="flex items-center justify-between px-4 py-3 w-full"
               href="{{ url('/category/' . $cat->category_slug . '/' . $subCategory->sub_cat_slug) }}">
                <span class="font-medium text-gray-800">{{ $subCategory->sub_category }}</span>
                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                    {{ $subCategory->advert_count }} ads
                </span>
            </a>
        </div>
    @endforeach
</div>
