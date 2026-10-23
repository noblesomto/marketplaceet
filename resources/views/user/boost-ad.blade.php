@include('user.layouts.header')
@include('user.layouts.nav')
@include('user.layouts.back-nav')
@include('user.layouts.search')

<section class="w-full max-w-4xl mx-auto pb-20">
    <div class="bg-white rounded-lg shadow-md overflow-hidden border border-gray-100 p-4 md:p-6">
        <!-- Header Section -->
        <div class="bg-dark_green text-white px-6 py-4">
            <h1 class="text-xl font-semibold">Boost Your Advert</h1>
            @include('public.components.flash-message')
        </div>

        <!-- Content Section -->
        <div class="p-6">
            <form method="POST" action="/boost/pay" class="space-y-6" onsubmit="setBoostName()">
                @csrf

                <!-- Ad Details Section -->
                <div>
                    <h2 class="text-lg font-semibold text-dark_green mb-4">Ad Details</h2>
                    <div class="flex flex-col md:flex-row gap-6 items-start">
                        <img class="w-full md:w-48 h-48 object-cover rounded-lg border border-gray-200 shadow-sm"
                             src="{{ $advert->getFirstMediaUrl('images', 'thumbnail') }}"
                             alt="{{ $advert->ad_title }}">
                        <div>
                            <h3 class="font-bold text-lg text-gray-800">{{ $advert->ad_title }}</h3>
                            <div class="mt-2 text-sm text-gray-600">

                            </div>
                        </div>
                    </div>
                </div>

                <!-- Boost Options Section -->
                <div class="border-t border-gray-200 pt-6">
                    <h2 class="text-lg font-semibold text-dark_green mb-4">Boost Options</h2>

                    <div class="space-y-4">
                       <!-- Boost Type -->
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Boost Type *</label>
    <select name="boost_type_id" id="boost_type_id"
      class="w-full px-4 py-2 border rounded shadow-sm"
      onchange="setBoostName(); calculatePrice();" required>
      <option value="">Select Boost</option>
      @foreach($boostTypes as $type)
        <option value="{{ $type->id }}"
                data-name="{{ $type->name }}"
                data-rate="{{ $type->daily_rate }}"
                data-description="{{ $type->description }}">
          {{ $type->name }} - Br {{ number_format($type->daily_rate, 2) }}/day
        </option>
      @endforeach
    </select>
    <input type="hidden" name="boost_name" id="boost_name">
    <p class="text-xs text-gray-500 mt-1" id="boost_description"></p>
  </div>

  <!-- Duration -->
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Duration *</label>
    <select name="duration_id" id="duration_id"
      class="w-full px-4 py-2 border rounded shadow-sm"
      onchange="calculatePrice();" required>
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

  <!-- Price Display -->
  <div class="bg-gray-50 p-4 rounded-md border">
    <label class="block text-sm font-medium text-gray-700 mb-1">Total Price</label>
    <div class="flex items-center">
      <span class="text-2xl font-bold text-[#1a5276]">Br </span>
      <input type="text" id="amount" name="amount" readonly
        class="w-full bg-transparent border-none text-2xl font-bold focus:ring-0 pl-2">
    </div>
    <p class="text-xs text-gray-500 mt-1">Automatically calculated</p>
  </div>

  <input type="hidden" id="basePrice" value="{{ $price }}">
  <input type="hidden" name="advert_id" value="{{ $advert->id }}">
  <input type="hidden" name="duration" id="duration_days" value="">
  <input type="hidden" name="boost_type" id="boost_type_name" value="">

  <div class="flex justify-end pt-6 border-t">
    <button type="submit" class="px-8 py-3 bg-dark_green text-white rounded-md flex items-center space-x-2">
      <span>Boost Ad Now</span>
      <!-- svg icon -->
    </button>
  </div>
</form>
        </div>
    </div>
</section>

<script>
  function setBoostName() {
    const boostSelect = document.getElementById('boost_type_id');
    const selectedOption = boostSelect.options[boostSelect.selectedIndex];

    if (selectedOption.value) {
      document.getElementById('boost_name').value = selectedOption.dataset.name;
      document.getElementById('boost_description').textContent = selectedOption.dataset.description || '';
    } else {
      document.getElementById('boost_name').value = '';
      document.getElementById('boost_description').textContent = '';
    }
  }

  async function calculatePrice() {
    const boostTypeId = document.getElementById('boost_type_id').value;
    const durationId = document.getElementById('duration_id').value;
    const amountField = document.getElementById('amount');
    const durationSelect = document.getElementById('duration_id');

    if (!boostTypeId || !durationId) {
      amountField.value = '';
      return;
    }

    // Update backward compatibility fields
    const selectedDuration = durationSelect.options[durationSelect.selectedIndex];
    if (selectedDuration.value) {
      document.getElementById('duration_days').value = selectedDuration.dataset.days;
    }

    const boostTypeSelect = document.getElementById('boost_type_id');
    const selectedBoostType = boostTypeSelect.options[boostTypeSelect.selectedIndex];
    if (selectedBoostType.value) {
      document.getElementById('boost_type_name').value = selectedBoostType.dataset.name.toLowerCase();
    }

    try {
      // Show loading state
      amountField.value = 'Calculating...';

      // Call API to calculate price
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
        // Format price with commas
        const finalPrice = parseFloat(data.data.pricing.final_price);
        amountField.value = Math.round(finalPrice).toLocaleString();

        // Store the actual numeric value for form submission
        amountField.dataset.actualValue = Math.round(finalPrice);
      } else {
        amountField.value = 'Error';
        console.error('Price calculation failed:', data);
      }
    } catch (error) {
      console.error('Error calculating price:', error);
      amountField.value = 'Error';
    }
  }

  // Update amount before form submission to use numeric value
  document.querySelector('form').addEventListener('submit', function(e) {
    const amountField = document.getElementById('amount');
    if (amountField.dataset.actualValue) {
      amountField.value = amountField.dataset.actualValue;
    }
  });
</script>

@include('user.layouts.footer')
