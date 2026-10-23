@include('user.layouts.header')
@include('user.layouts.nav')
@include('user.layouts.back-nav')
@include('user.layouts.search')

<section class="w-full max-w-5xl mx-auto pb-20 pt-2">
    <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-200">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-dark_green to-green-700 text-white px-6 py-5">
            <h1 class="text-2xl font-bold">Complete Your Payment</h1>
            <p class="text-green-100 text-sm mt-1">Boost your ad to reach more customers</p>
        </div>

        <div class="px-4">
            @include('public.components.flash-message')
        </div>

        <!-- Content Section -->
        <div class="p-2 md:p-2">
            <!-- Ad Details Section -->
            <div class="mb-8 bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h2 class="text-lg font-semibold text-dark_green mb-4 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path>
                    </svg>
                    Ad Details
                </h2>
                <div class="flex flex-col md:flex-row gap-6 items-start">
                    <img class="w-full md:w-56 h-56 object-cover rounded-lg border-2 border-gray-300 shadow-md"
                         src="{{ $ad->advert->getFirstMediaUrl('images', 'thumbnail') }}"
                         alt="{{ $ad->advert->ad_title }}">
                    <div class="flex-1">
                        <h3 class="font-bold text-xl text-gray-800 mb-3">{{ $ad->advert->ad_title }}</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-sm">
                            <div class="flex items-center bg-white p-3 rounded-md border">
                                <span class="text-gray-600 font-medium">Boost Type:</span>
                                <span class="ml-2 capitalize font-semibold text-dark_green">{{ $ad->boost_type }}</span>
                            </div>
                            <div class="flex items-center bg-white p-3 rounded-md border">
                                <span class="text-gray-600 font-medium">Duration:</span>
                                <span class="ml-2 font-semibold text-dark_green">{{ $ad->duration }} Days</span>
                            </div>
                            <div class="flex items-center bg-white p-3 rounded-md border md:col-span-2">
                                <span class="text-gray-600 font-medium">Amount:</span>
                                <span class="ml-2 text-xl font-bold text-dark_green">Br {{ number_format($ad->amount, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bank Transfer Section -->
            <div class="mb-8 border border-gray-200 rounded-lg overflow-hidden">
                <div class="bg-gray-100 px-6 py-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        Bank Transfer Payment
                    </h3>
                </div>
                <div class="p-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-5 mb-4">
                        <h4 class="font-semibold text-gray-800 mb-3">Bank Account Details</h4>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Account Name:</span>
                                <span class="font-semibold">B&S MARKETPLACE NG LTD</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Account Number:</span>
                                <span class="font-semibold text-lg">1310155574</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600">Bank:</span>
                                <span class="font-semibold">ZENITH BANK</span>
                            </div>
                        </div>
                    </div>
                    <form method="POST" action="/boost/upload-proof" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="boost_id" value="{{ $ad->id }}">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Upload Payment Proof</label>
                            <div class="flex flex-col md:flex-row gap-3">
                                <input type="file" name="payment_proof" accept="image/*,.pdf" required
                                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-dark_green focus:border-transparent">
                                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors">
                                    Upload Proof
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Accepted formats: JPG, PNG, PDF (Max 5MB)</p>
                        </div>
                    </form>
                </div>
            </div>

             <div class="flex items-center my-6">
                <div class="flex-grow border-t border-gray-300"></div>
                <span class="flex-shrink mx-4 text-gray-600 text-sm">or</span>
                <div class="flex-grow border-t border-gray-300"></div>
              </div>

            <!-- Online Payment Section -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <div class="bg-gray-100 px-6 py-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        Online Payment (Card/Transfer)
                    </h3>
                </div>
                <div class="p-6">
                    <form method="POST" action="/boost/make-payment" class="space-y-6" onsubmit="setBoostName()">
                        @csrf

                        <input type="hidden" name="boost_id" value="{{ $ad->id }}">
                        <!-- Boost Type -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Boost Type <span class="text-red-500">*</span>
                            </label>
                            <select name="boost_type" id="boost_type"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-dark_green focus:border-transparent"
                                    onchange="setBoostName(); calculatePrice();" required>
                                <option value="">Select Boost Type</option>
                                <option value="214.285" {{ strtolower($ad->boost_type) == 'highlight' ? 'selected' : '' }}>Highlight - Stand out from the crowd</option>
                                <option value="500" {{ strtolower($ad->boost_type) == 'repeated' ? 'selected' : '' }}>Repeated - Stay at the top</option>
                                <option value="1071.428" {{ strtolower($ad->boost_type) == 'top' ? 'selected' : '' }}>Top - Maximum visibility</option>
                                <option value="1428.571" {{ strtolower($ad->boost_type) == 'gallery' ? 'selected' : '' }}>Gallery - Featured showcase</option>
                            </select>
                            <input type="hidden" name="boost_name" id="boost_name" value="{{ $ad->boost_type }}">
                            <input type="hidden" name="advert_id" value="{{ $ad->advert->id }}">
                        </div>

                        <!-- Duration -->
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Duration <span class="text-red-500">*</span>
                            </label>
                            <select name="duration" id="duration"
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-dark_green focus:border-transparent"
                                    onchange="calculatePrice();" required>
                                <option value="">Select Duration</option>
                                <option value="7" {{ $ad->duration == 7 ? 'selected' : '' }}>7 Days (Standard)</option>
                                <option value="14" {{ $ad->duration == 14 ? 'selected' : '' }}>14 Days (3% discount)</option>
                                <option value="30" {{ $ad->duration == 30 ? 'selected' : '' }}>30 Days (5% discount)</option>
                                <option value="90" {{ $ad->duration == 90 ? 'selected' : '' }}>90 Days (7% discount)</option>
                                <option value="120" {{ $ad->duration == 120 ? 'selected' : '' }}>120 Days (10% discount)</option>
                            </select>
                        </div>

                        <!-- Price Display -->
                        <div class="bg-gradient-to-br from-dark_green to-green-700 text-white p-6 rounded-lg shadow-md">
                            <label class="block text-sm font-medium mb-2 text-green-100">Total Amount</label>
                            <div class="flex items-baseline">
                                <span class="text-3xl font-bold">Br </span>
                                <input type="text" id="amount" name="amount" readonly value="{{ number_format($ad->amount, 0) }}"
                                       class="w-full bg-transparent border-none text-4xl font-bold focus:ring-0 pl-2 text-white">
                            </div>
                            <p class="text-xs text-green-100 mt-2">Price calculated with applicable discounts</p>
                        </div>

                        <input type="hidden" id="basePrice" value="{{ $price }}">

                        <!-- Submit Button -->
                        <div class="flex justify-end pt-4">
                            <button type="submit" class="px-8 py-4 bg-gradient-to-r from-dark_green to-green-700 hover:from-green-700 hover:to-dark_green text-white rounded-lg font-semibold flex items-center space-x-2 shadow-lg transition-all transform hover:scale-105">
                                <span>Proceed to Payment</span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Info Section -->
            <div class="mt-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                    </svg>
                    <div class="text-sm text-yellow-800">
                        <p class="font-semibold mb-1">Important Information:</p>
                        <ul class="list-disc list-inside space-y-1 text-xs">
                            <li>Your ad will be boosted immediately after payment confirmation</li>
                            <li>For bank transfers, please upload payment proof for faster processing</li>
                            <li>Contact support if you encounter any issues</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
  // Set initial values on page load
  document.addEventListener('DOMContentLoaded', function() {
    setBoostName();
    calculatePrice();
  });

  function setBoostName() {
    const boostSelect = document.getElementById('boost_type');
    const selectedText = boostSelect.options[boostSelect.selectedIndex].text;
    // Extract just the boost type name (before the dash)
    const boostName = selectedText.split('-')[0].trim();
    document.getElementById('boost_name').value = boostName;
  }

  function calculatePrice() {
    const boostValue = parseFloat(document.getElementById('boost_type').value) || 0;
    const days = parseInt(document.getElementById('duration').value) || 0;

    if (!boostValue || !days) {
      document.getElementById('amount').value = '{{ number_format($ad->amount, 0) }}';
      return;
    }

    // Discount lookup
    const discounts = {
      14: 0.03,
      30: 0.05,
      90: 0.07,
      120: 0.10
    };

    // Compute raw and discounted total
    const rawTotal = boostValue * days;
    const discountRate = discounts[days] || 0;
    const finalTotal = rawTotal * (1 - discountRate);

    // Format with commas, no decimals
    document.getElementById('amount').value = Math.round(finalTotal).toLocaleString();
  }
</script>

@include('user.layouts.footer')
