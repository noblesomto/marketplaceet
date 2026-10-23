<div class="space-y-2">
    @php
        $conditions = ['Local used', 'Foreign used', 'Brand new'];
        $contextFilters = [];

        // Detect page context based on filterType variable set by controller
        // This is more reliable than checking variable existence
        if(isset($filterType)) {
            if($filterType === 'brand' && isset($filterId)) {
                // Brand page: filter by brand only
                $contextFilters['brand'] = $filterId;
            } elseif($filterType === 'sub_category' && isset($filterId)) {
                // Subcategory page: filter by subcategory
                $contextFilters['sub_category'] = $filterId;
            } elseif($filterType === 'category' && isset($filterId)) {
                // Category page: filter by category
                $contextFilters['category'] = $filterId;
            }
        }

    @endphp

    @foreach($conditions as $condition)
        @php
            $query = DB::table('adverts')
                ->join('car_details', 'adverts.id', '=', 'car_details.advert_id')
                ->where('adverts.ad_status', 'active')
                ->where('adverts.sold', 'No')
                ->where('car_details.condition', $condition);

            foreach($contextFilters as $key => $value) {
                $query->where('adverts.' . $key, $value);
            }

            $count = $query->count();
        @endphp

        <label class="flex items-center justify-between p-2 rounded-md hover:bg-gray-50 cursor-pointer transition-all">
            <div class="flex items-center">
                <input type="checkbox"
                       name="car_condition[]"
                       value="{{ $condition }}"
                       class="car-filter-checkbox w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500 focus:ring-2 transition-all">
                <span class="ml-2 text-sm text-gray-700 font-medium">{{ $condition }}</span>
            </div>
            <span class="text-xs text-gray-500 font-medium">({{ $count }})</span>
        </label>
    @endforeach
</div>
