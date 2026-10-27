{{-- Mobile counterpart of filter/shop-by-model: a swipeable row of model chips above the listings. --}}
@if(isset($models) && $models->isNotEmpty())
@php
    $currentModelId = isset($model) ? $model->id : null;
    $chipUrl = fn ($slug) => isset($location)
        ? url('/' . $location . '/' . $cat->category_slug . '/' . $slug)
        : url($brandUrl . '/' . $slug);
    $allChipUrl = isset($location)
        ? url('/' . $location . '/' . $cat->category_slug . '/' . $brand->brand_slug)
        : url($brandUrl);
    $chipBase = 'shrink-0 px-3 py-1.5 rounded-full border text-[13px] font-semibold whitespace-nowrap';
    $chipOn   = 'bg-dark_green border-dark_green text-white';
    $chipOff  = 'bg-white border-gray-200 text-gray-700';
@endphp
<nav aria-label="Shop by model" class="block lg:hidden -mx-1 mb-2">
    <div class="flex gap-2 overflow-x-auto px-1 pb-1" style="scrollbar-width: none;">
        <a href="{{ $allChipUrl }}" class="{{ $chipBase }} {{ $currentModelId ? $chipOff : $chipOn }}">All {{ $brand->brand }}</a>
        @foreach($models as $catModel)
            <a href="{{ $chipUrl($catModel->model_slug) }}"
               @if($currentModelId == $catModel->id) aria-current="page" @endif
               class="{{ $chipBase }} {{ $currentModelId == $catModel->id ? $chipOn : $chipOff }}">
                {{ $catModel->model }} <span class="font-normal opacity-75">({{ $catModel->advert_count }})</span>
            </a>
        @endforeach
    </div>
</nav>
@endif
