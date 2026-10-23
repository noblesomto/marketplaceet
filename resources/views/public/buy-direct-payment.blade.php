@include('public.layouts.header')
@include('public.layouts.nav')
@include('public.layouts.mobile-back-nav')
@include('public.layouts.search')

<section class="max-w-4xl mx-auto  space-y-3 mb-20">
  <!-- Advertisement Banner (Desktop) -->
  <div class="hidden lg:block my-6 rounded-lg overflow-hidden shadow-md">
    @include('public.components.advert.banner-advert')
  </div>

  <!-- Product Purchase Section -->
  <div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <div class="md:flex">
      <!-- Product Image Gallery -->
      <div class="md:w-1/2 p-2">
        <div class="relative overflow-hidden rounded-lg bg-gray-100 aspect-square mb-4">
          <img src="{{ $ad->hasMedia('images') ? $ad->getFirstMediaUrl('images', 'large') : asset('frontend/images/default.png') }}"
               alt="{{ $ad->ad_title }}" 
               class="w-full h-full object-cover transition-transform duration-300 hover:scale-105">
        </div>
        <div class="">
          <!-- Additional thumbnails can go here -->
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">{{ $ad->ad_title }}</h1>
          <div class="flex items-center space-x-4 mb-1">
              <span class="text-2xl font-bold text-primary">Br {{ number_format($ad->price, 0, '.', ',') }}</span>
              <span class="text-gray-600">{{ $ad->price_type }}</span>
            </div>
        </div>
      </div>
      
      <!-- Product Details -->
      <div class="md:w-1/2 px-6">
        <div class="mb-6">

          
          <!-- Price Information -->
          <div class="mt-6">

            @include('public.components.flash-message')

            <h4 class="font-semibold">Delivery Details</h4>

            <div class="space-y-2 mt-2 text-sm">
                <div class="flex gap-4"><span class="font-semibold w-20 flex-shrink-0">Name:</span><span>{{ $checkout['first_name'] }} {{ $checkout['last_name'] }}</span></div>
                <div class="flex gap-4"><span class="font-semibold w-20 flex-shrink-0">Phone:</span><span>{{ $checkout['phone'] }}</span></div>
                <div class="flex gap-4"><span class="font-semibold w-20 flex-shrink-0">Location:</span><span>{{ $checkout['delivery_address'] }}, {{ $checkout['lga'] }}, {{ $checkout['state_name'] }}</span></div>
                <a href="{{ route('buy.direct', $ad->ad_id) }}" class="inline-block text-xs text-dark_green font-semibold underline">Change details</a>
            </div>

            <div class="bg-gray-50 p-3 rounded-lg mt-6 space-y-3">
                <div class="flex justify-between mb-1">
                    <span class="text-gray-600">Item Price:</span>
                    <span class="font-medium">Br {{ number_format($checkout['item_price'], 2, '.', ',') }}</span>
                </div>
                <div class="flex justify-between mb-1">
                    <span class="text-gray-600">Buyer protection:</span>
                    <span class="font-medium">Br {{ number_format($checkout['commission'], 2, '.', ',') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-800 font-semibold">Total Price:</span>
                    <span class="text-xl font-bold">Br {{ number_format($checkout['grand_total'], 2, '.', ',') }}</span>
                </div>
            </div>

            <div class="bg-green-50 border border-green-200 rounded-lg p-3 mt-4 text-xs text-green-900 space-y-1">
                <p><strong>How your money is protected:</strong> we hold your payment until you confirm you've received the item.</p>
                <p>The seller must ship within {{ $shipDeadlineDays }} days or you get a full refund. If you don't confirm or report a problem, payment is released {{ $autoReleaseDays }} days after the item is shipped.</p>
            </div>
          </div>

           <!-- Purchase Form -->
            <form method="POST" action="{{ route('flutterwave.pay') }}" id="flutterwave" class="mt-6">
                @csrf

            <button type="submit" class="w-full md:w-auto mt-4 flex items-center justify-center px-8 py-3 bg-primary hover:bg-primary-dark  font-medium rounded-lg shadow-md transition-colors duration-200">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
              Pay Br {{ number_format($checkout['grand_total'], 2) }} Securely
            </button>
          </form>

          <div id="clickableDiv" class="py-4 cursor-pointer" >
            <p class="text-dark_green">Learn more about Buy Direct</p>
        </div>
          
          <!-- Trust Badges -->
          <div class="mt-4 flex items-center space-x-4">
            <div class="flex items-center text-gray-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
              </svg>
              <span class="text-sm">Secure Payment</span>
            </div>
            <div class="flex items-center text-gray-600">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
              </svg>
              <span class="text-sm">Buyer Protection</span>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Product Description (can be added if available) -->
    @if($ad->description)
    <div class="border-t border-gray-200 p-6">
      <h2 class="text-xl font-semibold mb-4">Description</h2>
      <div class="prose max-w-none">
        {!! $ad->description !!}
      </div>
    </div>
    @endif
  </div>
</section>


<!-- Modal -->
<div id="modalBackdrop" class="hidden fixed inset-0 z-50 overflow-y-auto bg-gray-900 bg-opacity-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div id="modal" class="hidden relative bg-white rounded-lg shadow-xl w-full max-w-md max-h-[90vh] flex flex-col">
            <!-- Header -->
            <div class="flex justify-between items-center p-4 border-b sticky top-0 bg-white z-10">
                <h3 class="text-xl font-semibold">What is "Buy Direct"?</h3>
                <button id="closeModalBuy" class="text-gray-500 hover:text-gray-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Scrollable content -->
            <div class="p-4 overflow-y-auto flex-grow">
                <p class="text-gray-600 mb-4">
                Buy Direct is a fast and secure way to purchase items instantly on our platform—no need to wait for negotiations or back-and-forth messaging.
            </p>

                <p class="text-gray-600 mb-4">
                    When you see the Buy Direct button, it means the seller has enabled instant purchase. Simply click, pay, and the item is yours. It's the easiest way to shop with confidence.
                </p>
                
                <h3 class="text-xl font-semibold text-gray-800 mt-6 mb-3">How it works:</h3>
                
                <ul class="space-y-4">
                    <li class="flex items-start">
                        <div class="flex-shrink-0 h-6 w-6 text-blue-500 mr-3">✓</div>
                        <div>
                            <h4 class="font-medium text-gray-800">Instant Purchase</h4>
                            <p class="text-gray-600">Tap Buy Direct to immediately lock in the item before someone else does.</p>
                        </div>
                    </li>
                    
                    <li class="flex items-start">
                        <div class="flex-shrink-0 h-6 w-6 text-blue-500 mr-3">✓</div>
                        <div>
                            <h4 class="font-medium text-gray-800">Secure Payments with Flutterwave</h4>
                            <p class="text-gray-600">All payments are processed safely through Flutterwave, supporting cards, bank transfers, and other local methods.</p>
                        </div>
                    </li>
                    
                    <li class="flex items-start">
                        <div class="flex-shrink-0 h-6 w-6 text-blue-500 mr-3">✓</div>
                        <div>
                            <h4 class="font-medium text-gray-800">Delivery Arranged With the Seller</h4>
                            <p class="text-gray-600">After you pay, the seller contacts you on the phone number you provided to arrange delivery or pickup. If they don't ship within {{ $shipDeadlineDays }} days, you get a full refund.</p>
                        </div>
                    </li>
                    
                    <li class="flex items-start">
                        <div class="flex-shrink-0 h-6 w-6 text-blue-500 mr-3">✓</div>
                        <div>
                            <h4 class="font-medium text-gray-800">Buyer Protection</h4>
                            <p class="text-gray-600">Your payment is held securely until you confirm you've received the item in good condition. If you don't take any action, we automatically release the payment to the seller {{ $autoReleaseDays }} days after the item is shipped—giving you enough time to inspect it and report any issues.</p>
                        </div>
                    </li>
                    
                    <li class="flex items-start">
                        <div class="flex-shrink-0 h-6 w-6 text-blue-500 mr-3">✓</div>
                        <div>
                            <h4 class="font-medium text-gray-800">Dispute Resolution</h4>
                            <p class="text-gray-600">If there's a problem with your order, tap "Report a problem" on your order page before the payment is released. We hold the payment while our support team helps resolve it fairly.</p>
                        </div>
                    </li>
                </ul>
                
                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <p class="text-blue-800 font-medium">
                        With Buy Direct, shopping is simpler, faster, and more reliable—giving you peace of mind every step of the way.
                    </p>
                </div>
            </div>

            <!-- Footer (optional) -->
            <div class="p-4 border-t sticky bottom-0 bg-white">
                <button id="closeModalBtn" class="px-4 py-2 bg-primary hover:bg-primary-dark rounded">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

@include('public.layouts.footer')

<!-- Alpine.js for simple interactivity (can be replaced with plain JS if needed) -->
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
 <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>


<script>
    document.getElementById('clickableDiv').addEventListener('click', function() {
        document.getElementById('modalBackdrop').classList.remove('hidden');
        document.getElementById('modal').classList.remove('hidden');
    });

    document.getElementById('closeModalBuy').addEventListener('click', function() {
        document.getElementById('modalBackdrop').classList.add('hidden');
        document.getElementById('modal').classList.add('hidden');
    });

    // Close modal when clicking on backdrop
    document.getElementById('modalBackdrop').addEventListener('click', function() {
        document.getElementById('modalBackdrop').classList.add('hidden');
        document.getElementById('modal').classList.add('hidden');
    });
</script>



