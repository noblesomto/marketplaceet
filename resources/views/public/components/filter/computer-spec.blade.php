{{--
    Laptops & Computers spec filter — one spec ($field: processor, ram or
    storage), listing only the values that active ads in this context have.
--}}
<div class="space-y-2">
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
            ->join('computer_details', 'adverts.id', '=', 'computer_details.advert_id')
            ->where('adverts.ad_status', 'active')
            ->where('adverts.sold', 'No')
            ->whereNotNull('computer_details.' . $field);

        foreach($contextFilters as $key => $value) {
            $counts->where('adverts.' . $key, $value);
        }
        if(isset($model)) {
            $counts->where('computer_details.model', $model->id);
        }

        $counts = $counts->groupBy('computer_details.' . $field)
            ->pluck(DB::raw('count(*)'), 'computer_details.' . $field);
    @endphp

    @foreach(\App\Support\ComputerSpecs::options($field) as $value)
        @continue(!isset($counts[$value]))
        <label class="flex items-center justify-between p-2 rounded-md hover:bg-gray-50 cursor-pointer transition-all">
            <div class="flex items-center">
                <input type="checkbox"
                       name="computer_{{ $field }}[]"
                       value="{{ $value }}"
                       class="computer-filter-checkbox w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500 focus:ring-2 transition-all">
                <span class="ml-2 text-sm text-gray-700 font-medium">{{ $value }}</span>
            </div>
            <span class="text-xs text-gray-500 font-medium">({{ $counts[$value] }})</span>
        </label>
    @endforeach
</div>
