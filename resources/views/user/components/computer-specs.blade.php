{{--
    Laptops & Computers specs card (post-ad / edit-ad). Shown for subcategory 27
    by device-model-field.js. All optional: blank fields are read from the
    title and description when the ad is saved (App\Support\ComputerSpecs).
    $specs: the advert's current values (edit), field => value.
--}}
@php
    $specs = $specs ?? [];
    $show = (string) ($subcategoryId ?? old('subcategory')) === (string) \App\Support\DeviceModels::COMPUTERS_SUBCATEGORY_ID;
@endphp
<div id="divComputer" class="{{ $show ? '' : 'hidden' }} bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
    <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
        <h2 class="text-base lg:text-lg font-semibold text-gray-800">Computer Specs</h2>
        <p class="text-xs text-gray-500 mt-1">Optional — buyers filter by these. Anything you leave blank is picked up from your title and description.</p>
    </div>
    <div class="p-3 md:p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach(\App\Support\ComputerSpecs::allOptions() as $field => $options)
                @php $current = old($field, $specs[$field] ?? null); @endphp
                <div>
                    <label for="spec_{{ $field }}" class="block text-sm font-semibold text-gray-700 mb-2">{{ \App\Support\ComputerSpecs::LABELS[$field] }}</label>
                    <select id="spec_{{ $field }}" name="{{ $field }}" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent bg-white text-base">
                        <option value="">Not specified</option>
                        @foreach($options as $option)
                            <option value="{{ $option }}" {{ $current === $option ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                    </select>
                    @error($field) <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            @endforeach
        </div>
    </div>
</div>
