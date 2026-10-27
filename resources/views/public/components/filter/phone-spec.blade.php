{{--
    Mobile Phones spec filter — one spec ($field: ram or storage) as a whole
    filter section, listing only the values that active ads in this context
    have; renders nothing when there are none (e.g. Tablets, Accessories).
    $layout: 'card' (sidebar, $headingClass for the page's heading style)
    or 'modal' (mobile Phone Filters modal).
--}}
@php
    $contextFilters = [];

    // Use filterType variable set by controller for accurate context detection
    if(isset($filterType)) {
        if($filterType === 'brand' && isset($filterId)) {
            $contextFilters['brand'] = $filterId;
        } elseif($filterType === 'sub_category' && isset($filterId)) {
            $contextFilters['sub_category'] = $filterId;
        } elseif($filterType === 'category' && isset($filterId)) {
            $contextFilters['category'] = $filterId;
        }
    }

    $counts = DB::table('adverts')
        ->join('phone_details', 'adverts.id', '=', 'phone_details.advert_id')
        ->where('adverts.ad_status', 'active')
        ->where('adverts.sold', 'No')
        ->whereNotNull('phone_details.' . $field);

    foreach($contextFilters as $key => $value) {
        $counts->where('adverts.' . $key, $value);
    }
    if(isset($model)) {
        $counts->where('phone_details.model', $model->id);
    }

    $counts = $counts->groupBy('phone_details.' . $field)
        ->pluck(DB::raw('count(*)'), 'phone_details.' . $field);

    $layout = $layout ?? 'card';
@endphp

@if($counts->isNotEmpty())
<div class="{{ $layout === 'modal' ? 'border-b border-gray-200 pb-4' : 'bg-white p-4 rounded-xl shadow-md border border-gray-100' }}">
    <h4 class="{{ $layout === 'modal' ? 'font-bold text-gray-900 text-sm mb-3' : ($headingClass ?? 'font-bold text-gray-900 text-sm mb-3') }}">{{ \App\Support\PhoneSpecs::LABELS[$field] }}</h4>
    <div class="space-y-2">
        @foreach(\App\Support\PhoneSpecs::options($field) as $value)
            @continue(!isset($counts[$value]))
            <label class="flex items-center justify-between p-2 rounded-md hover:bg-gray-50 cursor-pointer transition-all">
                <div class="flex items-center">
                    <input type="checkbox"
                           name="phone_{{ $field }}[]"
                           value="{{ $value }}"
                           class="phone-filter-checkbox w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500 focus:ring-2 transition-all">
                    <span class="ml-2 text-sm text-gray-700 font-medium">{{ $value }}</span>
                </div>
                <span class="text-xs text-gray-500 font-medium">({{ $counts[$value] }})</span>
            </label>
        @endforeach
    </div>
</div>
@endif
