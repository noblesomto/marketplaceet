{{--
    Mobile Phones RAM / Storage (post-ad / edit-ad), inside the #divPhone card's
    grid. Optional: blank fields are read from the title and description when
    the ad is saved (App\Support\PhoneSpecs).
    $specs: the advert's current values (edit), field => value.
--}}
@php $specs = $specs ?? []; @endphp
@foreach(\App\Models\PhoneDetail::SPEC_INPUTS as $field => $input)
    @php $current = old($input, $specs[$field] ?? null); @endphp
    <div>
        <label for="{{ $input }}" class="block text-sm font-semibold text-gray-700 mb-2">{{ \App\Support\PhoneSpecs::LABELS[$field] }} <span class="text-xs font-normal text-gray-400">(optional)</span></label>
        <select id="{{ $input }}" name="{{ $input }}" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent bg-white text-base">
            <option value="">Not specified</option>
            @foreach(\App\Support\PhoneSpecs::options($field) as $option)
                <option value="{{ $option }}" {{ $current === $option ? 'selected' : '' }}>{{ $option }}</option>
            @endforeach
        </select>
        @error($input) <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>
@endforeach
<p class="md:col-span-2 -mt-3 text-xs text-gray-500">Buyers filter by RAM and storage. Anything you leave blank is picked up from your title and description.</p>
