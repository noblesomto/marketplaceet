{{-- Shop by Model — brand pages for brands with models (vehicles, phones e.g. Apple → iPhone 12).
     National pages link to /category/.../{brand}/{model}; location pages to /{location}/{category}/{model}. --}}
@if(isset($models) && $models->isNotEmpty())
@php
    $modelLimit = 10;
    $currentModelId = isset($model) ? $model->id : null;
    $modelUrl = fn ($slug) => isset($location)
        ? url('/' . $location . '/' . $cat->category_slug . '/' . $slug)
        : url($brandUrl . '/' . $slug);
    $allModelsUrl = isset($location)
        ? url('/' . $location . '/' . $cat->category_slug . '/' . $brand->brand_slug)
        : url($brandUrl);
    // Keep the model being viewed visible even when it's past the cut-off.
    $currentIndex = $currentModelId ? $models->search(fn ($m) => $m->id == $currentModelId) : false;
    $visibleCount = ($currentIndex !== false && $currentIndex >= $modelLimit) ? $models->count() : $modelLimit;
@endphp
<div class="bg-white p-4 rounded-xl shadow-md border border-gray-100">
    <h4 class="font-bold text-gray-900 text-sm mb-3 uppercase tracking-wider text-[11px]">Shop by Model</h4>
    <ul class="space-y-0.5">
        <li>
            <a href="{{ $allModelsUrl }}"
               class="group flex items-center justify-between p-2 rounded-md transition-all duration-200 {{ $currentModelId ? 'hover:bg-gray-50' : 'bg-green-50' }}">
                <span class="text-[14px] leading-tight font-bold transition-colors {{ $currentModelId ? 'text-gray-600 group-hover:text-secondary_dark' : 'text-secondary_dark' }}">
                    All {{ $brand->brand }}
                </span>
            </a>
        </li>
        @foreach($models->take($visibleCount) as $catModel)
            @include('public.components.filter.shop-by-model-item')
        @endforeach
    </ul>
    @if($models->count() > $visibleCount)
    <details class="group/more">
        <summary class="list-none cursor-pointer p-2 text-[13px] font-semibold text-dark_green hover:underline">
            <span class="group-open/more:hidden">Show all {{ $models->count() }} models</span>
            <span class="hidden group-open/more:inline">Show fewer</span>
        </summary>
        <ul class="space-y-0.5">
            @foreach($models->slice($visibleCount) as $catModel)
                @include('public.components.filter.shop-by-model-item')
            @endforeach
        </ul>
    </details>
    @endif
</div>
@endif
