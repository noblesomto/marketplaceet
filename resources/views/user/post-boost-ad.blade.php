@include('user.layouts.header')
@include('user.layouts.nav')
@include('user.layouts.back-nav')
@include('user.layouts.search')

<section class="w-full max-w-4xl mx-auto p-4 md:p-6 pb-20">
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <!-- Header Section -->
        <div class="bg-dark_green text-white px-6 py-4">
            <h1 class="text-xl font-semibold">Boost Your Advert</h1>
            @include('public.components.flash-message')
        </div>

        <!-- Content Section -->
        <div class="p-6">
            <form method="POST" action="/post-boost/pay" class="space-y-6">
                @csrf

                <!-- Ad Details Section -->
                <div>
                    <h2 class="text-lg font-semibold text-dark_green mb-4">Ad Details</h2>
                    <div class="flex flex-col md:flex-row gap-6 items-start">
                        <img class="w-full md:w-48 h-48 object-contain rounded-lg border border-gray-200"
                             src="{{ $advert->getFirstMediaUrl('images', 'thumbnail') }}"
                             alt="{{ $advert->ad_title }}">
                        <div>
                            <h3 class="font-bold text-lg text-gray-800">{{ $advert->ad_title }}</h3>
                            <p class="text-gray-600 mt-2">Status: {{ $advert->status }}</p>
                            <p class="text-gray-600">Posted: {{ $advert->created_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Boost Options Section -->
                <div class="border-t border-gray-200 pt-6">
                    <h2 class="text-lg font-semibold text-dark_green mb-4">Boost Options</h2>

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Boost Type *</label>
                            @if ($errors->has('boost_type_id'))
                                <p class="text-red-600 text-sm mb-2">{{ $errors->first('boost_type_id') }}</p>
                            @endif

                            <select name="boost_type_id" id="boost_type_id"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-dark_green focus:border-dark_green"
                                    onchange="updateDescription(); calculatePrice();"
                                    required>
                                <option value="">Select Boost Type</option>
                                @foreach($boostTypes as $type)
                                    <option value="{{ $type->id }}"
                                            data-name="{{ $type->name }}"
                                            data-rate="{{ $type->daily_rate }}"
                                            data-description="{{ $type->description }}">
                                        {{ $type->name }} - Br {{ number_format($type->daily_rate, 2) }}/day
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-600 mt-1" id="boost_description"></p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Duration *</label>
                            @if ($errors->has('duration_id'))
                                <p class="text-red-600 text-sm mb-2">{{ $errors->first('duration_id') }}</p>
                            @endif

                            <select name="duration_id" id="duration_id"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-dark_green focus:border-dark_green"
                                    onchange="calculatePrice();"
                                    required>
                                <option value="">Select Duration</option>
                                @foreach($boostDurations as $duration)
                                    <option value="{{ $duration->id }}"
                                            data-days="{{ $duration->days }}"
                                            data-discount="{{ $duration->discount_percentage }}">
                                        {{ $duration->label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-md border">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Total Price</label>
                            <div class="text-2xl font-bold text-dark_green" id="calculated_price">
                                Select boost type and duration
                            </div>
                        </div>

                        <div class="bg-blue-50 p-4 rounded-md">
                            <h3 class="font-medium text-blue-800">What does boosting do?</h3>
                            <ul class="list-disc list-inside text-sm text-blue-700 mt-2 space-y-1">
                                <li>Increases visibility of your ad</li>
                                <li>Appears in premium positions</li>
                                <li>Gets more clicks and responses</li>
                            </ul>
                        </div>
                    </div>

                    <input type="hidden" name="duration" id="duration_days" value="">
                    <input type="hidden" name="promotion" id="promotion_value" value="">
                    <input type="hidden" name="advert_id" value="{{ $advert->id }}">
                    <input type="hidden" name="amount" id="amount" value="">
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row justify-end gap-3 pt-6 border-t border-gray-200">
                    <a href="/user/my-ads"
                       class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 flex items-center justify-center space-x-2">
                        <span>Boost Later</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                        </svg>
                    </a>

                    <button type="submit"
                            class="px-6 py-2 border border-transparent rounded-md shadow-sm text-white bg-dark_green hover:bg-green-700 flex items-center justify-center space-x-2 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-dark_green">
                        <span>Boost Ad Now</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 0 1-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 0 0 6.16-12.12A14.98 14.98 0 0 0 9.631 8.41m5.96 5.96a14.926 14.926 0 0 1-5.841 2.58m-.119-8.54a6 6 0 0 0-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 0 0-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 0 1-2.448-2.448 14.9 14.9 0 0 1 .06-.312m-2.24 2.39a4.493 4.493 0 0 0-1.757 4.306 4.493 4.493 0 0 0 4.306-1.758M16.5 9a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0Z" />
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
  function updateDescription() {
    const boostSelect = document.getElementById('boost_type_id');
    const selectedOption = boostSelect.options[boostSelect.selectedIndex];

    if (selectedOption.value) {
      const description = selectedOption.dataset.description || '';
      document.getElementById('boost_description').textContent = description;
      document.getElementById('promotion_value').value = selectedOption.dataset.name.toLowerCase();
    } else {
      document.getElementById('boost_description').textContent = '';
      document.getElementById('promotion_value').value = '';
    }
  }

  async function calculatePrice() {
  const boostTypeId = document.getElementById('boost_type_id').value;
  const durationId = document.getElementById('duration_id').value;
  const priceDisplay = document.getElementById('calculated_price');
  const durationSelect = document.getElementById('duration_id');

  if (!boostTypeId || !durationId) {
    priceDisplay.textContent = 'Select boost type and duration';
    document.getElementById('amount').value = ''; // Clear amount if invalid
    return;
  }

  // Update backward compatibility field
  const selectedDuration = durationSelect.options[durationSelect.selectedIndex];
  if (selectedDuration.value) {
    document.getElementById('duration_days').value = selectedDuration.dataset.days;
  }

  try {
    priceDisplay.textContent = 'Calculating...';

    const response = await fetch('/api/boost/calculate', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: JSON.stringify({
        boost_type_id: parseInt(boostTypeId),
        duration_id: parseInt(durationId)
      })
    });

    const data = await response.json();

    if (data.success && data.data.pricing) {
      const finalPrice = parseFloat(data.data.pricing.final_price);
      const discountPercentage = parseFloat(data.data.pricing.discount_percentage);

      let priceText = 'Br ' + Math.round(finalPrice).toLocaleString();

      if (discountPercentage > 0) {
        const basePrice = parseFloat(data.data.pricing.base_price);
        priceText += ' <span class="text-sm text-gray-600">(Save Br ' +
                     Math.round(basePrice - finalPrice).toLocaleString() + ')</span>';
      }

      priceDisplay.innerHTML = priceText;

      // Update the hidden amount field
      document.getElementById('amount').value = finalPrice;
    } else {
      priceDisplay.textContent = 'Error calculating price';
      document.getElementById('amount').value = ''; // Clear on error
      console.error('Price calculation failed:', data);
    }
  } catch (error) {
    console.error('Error calculating price:', error);
    priceDisplay.textContent = 'Error calculating price';
    document.getElementById('amount').value = ''; // Clear on error
  }
}
</script>

@include('user.layouts.footer')
