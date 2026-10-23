<div class="space-y-2">
    @php
        $deviceTypes = ['Device', 'Accessories', 'Device & Accessories'];
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
    @endphp

    @foreach($deviceTypes as $deviceType)
        @php
            $count = DB::table('adverts')
                ->join('phone_details', 'adverts.id', '=', 'phone_details.advert_id')
                ->where('adverts.ad_status', 'active')
                ->where('adverts.sold', 'No')
                ->where('phone_details.device', $deviceType);

            foreach($contextFilters as $key => $value) {
                $count->where('adverts.' . $key, $value);
            }

            $count = $count->count();
        @endphp

        <label class="flex items-center justify-between p-2 rounded-md hover:bg-gray-50 cursor-pointer transition-all">
            <div class="flex items-center">
                <input type="checkbox"
                       name="phone_device_type[]"
                       value="{{ $deviceType }}"
                       class="phone-filter-checkbox w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500 focus:ring-2 transition-all">
                <span class="ml-2 text-sm text-gray-700 font-medium">{{ $deviceType }}</span>
            </div>
            <span class="text-xs text-gray-500 font-medium">({{ $count }})</span>
        </label>
    @endforeach
</div>
