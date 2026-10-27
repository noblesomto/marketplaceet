<style>
    /* Hide number input spinners */
input[type='number']::-webkit-outer-spin-button,
input[type='number']::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}

input[type='number'] {
  -moz-appearance: textfield; /* Firefox */
}

</style>
<form id="price-filter-form" class="space-y-4">
  <div class="flex items-center justify-between gap-4 relative">
  <!-- Min Price Input -->
  <div class="relative w-full">
    <input
      type="number"
      inputmode="numeric"
      name="min"
      id="price-min"
      class="peer w-full h-12 pt-5 pb-1 px-3 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
      placeholder=" "
    />
    <label
      for="price-min"
      class="absolute left-3 top-1 text-xs text-gray-500 transition-all peer-placeholder-shown:top-3 peer-placeholder-shown:text-sm peer-focus:top-1 peer-focus:text-xs"
    >
      Min
    </label>
  </div>

  <span class="text-gray-400 px-2">-</span>

  <!-- Max Price Input -->
  <div class="relative w-full">
    <input
      type="number"
      inputmode="numeric"
      name="max"
      id="price-max"
      class="peer w-full h-12 pt-5 pb-1 px-3 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
      placeholder=" "
    />
    <label
      for="price-max"
      class="absolute left-3 top-1 text-xs text-gray-500 transition-all peer-placeholder-shown:top-3 peer-placeholder-shown:text-sm peer-focus:top-1 peer-focus:text-xs"
    >
      Max
    </label>
  </div>
</div>


  <!-- Price Range Radios -->
  <div class="flex flex-col space-y-3" id="price-range-group">
    <label class="inline-flex items-center">
      <input type="radio" name="range" value="under_20k" class="price-radio" />
      <span class="ml-2">Under 2k</span>
    </label>
    <label class="inline-flex items-center">
      <input type="radio" name="range" value="20k_120k" class="price-radio" />
      <span class="ml-2">2k - 10k</span>
    </label>
    <label class="inline-flex items-center">
      <input type="radio" name="range" value="120k_1m" class="price-radio" />
      <span class="ml-2">10k - 100k</span>
    </label>
    <label class="inline-flex items-center">
      <input type="radio" name="range" value="1m_10m" class="price-radio" />
      <span class="ml-2">100k - 1M</span>
    </label>
    <label class="inline-flex items-center">
      <input type="radio" name="range" value="above_10m" class="price-radio" />
      <span class="ml-2">More than 1M</span>
    </label>
  </div>

   <!-- Hidden Context (optional fields based on current page) -->

  @if(isset($brand))
    <input type="hidden" name="brand" value="{{ $brand->id }}">
  @endif

  @if(isset($model))
    <input type="hidden" name="model" value="{{ $model->id }}">
  @endif

  @if(isset($location))
    <input type="hidden" name="location" value="{{ $location }}">
  @endif

  <!-- Buttons -->
  <div class="flex justify-between font-semibold">
    <button type="reset" class="py-2 text-secondary-200 uppercase" id="clear-filter">Clear</button>
    <button type="submit" class="py-2 text-dark_green uppercase">Save</button>
  </div>
</form>


