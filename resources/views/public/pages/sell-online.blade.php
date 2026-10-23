@include('public.layouts.header-sell')
@include('public.layouts.nav')
@include('public.components.mobile.mobile-nav')
@include('public.layouts.search')

<section class="bg-white py-12 px-4 sm:px-6 lg:px-8">
  <div class="max-w-7xl mx-auto">

    <!-- Hero/Intro Section -->
    <div class="text-center mb-12">
      <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
        Welcome to <span class="text-green-700">Marketplace Ethiopia</span>
      </h1>
      <p class="text-lg text-gray-600 max-w-4xl mx-auto">
        Your premier digital destination to sell online in Ethiopia. We've built more than just a listing site; we've created a robust ecosystem where local commerce thrives.
      </p>
    </div>

    <!-- Why Choose Us Section -->
    <div class="mb-16">
      <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 text-center mb-10">
        Why Sell Online in Ethiopia with Marketplace Ethiopia?
      </h2>

      <div class="grid md:grid-cols-3 gap-8">
        <!-- Card 1 -->
        <div class="bg-gray-50 p-6 rounded-xl shadow-sm border border-gray-100">
          <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-3">Reach Real Buyers Instantly</h3>
          <p class="text-gray-600">
            Your ads are placed directly in front of thousands of genuine, high-intent buyers across Ethiopia daily.
          </p>
        </div>

        <!-- Card 2 -->
        <div class="bg-gray-50 p-6 rounded-xl shadow-sm border border-gray-100">
          <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-3">No Hidden Fees – Keep Your Profit</h3>
          <p class="text-gray-600">
            It's completely free to start selling. Keep 100% of your profit on basic items with no commissions.
          </p>
        </div>

        <!-- Card 3 -->
        <div class="bg-gray-50 p-6 rounded-xl shadow-sm border border-gray-100">
          <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-3">Verified Safety Protocols</h3>
          <p class="text-gray-600">
            Our robust buyer verification system and reporting tools ensure you deal with real people in a transparent environment.
          </p>
        </div>
      </div>
    </div>

    <!-- What You Can Sell Section -->
    <div class="mb-16">
      <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 text-center mb-10">
        What Can You Sell Online in Ethiopia?
      </h2>

      <div class="grid md:grid-cols-3 gap-6">
        <!-- Category 1 -->
        <div class="border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg transition-shadow duration-300">
          <div class="h-48 bg-blue-50 flex items-center justify-center">
            <svg class="w-20 h-20 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
          </div>
          <div class="p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-3">Sell Phone Online in Ethiopia</h3>
            <p class="text-gray-600 mb-4">
              List your current device whether it's a "UK-used" iPhone, the latest Samsung Galaxy, or a reliable Transmission device.
            </p>
            <div class="bg-blue-50 inline-block px-4 py-2 rounded-full">
              <span class="text-blue-700 text-sm font-medium">Pro-Tip:</span>
              <span class="text-gray-700 text-sm ml-2">Mention battery health, storage & accessories</span>
            </div>
          </div>
        </div>

        <!-- Category 2 -->
        <div class="border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg transition-shadow duration-300">
          <div class="h-48 bg-green-50 flex items-center justify-center">
            <svg class="w-20 h-20 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
          </div>
          <div class="p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-3">Sell Property Online</h3>
            <p class="text-gray-600 mb-4">
              Perfect for real estate agents, developers, or landlords. Sell property in Bole, Kazanchis, Piazza, or anywhere in Ethiopia.
            </p>
            <div class="bg-green-50 inline-block px-4 py-2 rounded-full">
              <span class="text-green-700 text-sm font-medium">Pro-Tip:</span>
              <span class="text-gray-700 text-sm ml-2">Use high-resolution images & detailed descriptions</span>
            </div>
          </div>
        </div>

        <!-- Category 3 -->
        <div class="border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg transition-shadow duration-300">
          <div class="h-48 bg-purple-50 flex items-center justify-center">
            <svg class="w-20 h-20 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
            </svg>
          </div>
          <div class="p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-3">Sell Cars, Electronics & Fashion</h3>
            <p class="text-gray-600 mb-4">
              From "Tokunbo" cars to electronics, appliances, Aso-Ebi fabrics, and modern street wear.
            </p>
            <div class="bg-purple-50 inline-block px-4 py-2 rounded-full">
              <span class="text-purple-700 text-sm font-medium">Pro-Tip:</span>
              <span class="text-gray-700 text-sm ml-2">Clear, well-lit photos increase sales by 5x</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- How to Start Section -->
    <div class="mb-16 bg-gray-50 rounded-2xl p-8 sm:p-12">
      <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 text-center mb-12">
        How to Start Selling Online in 3 Simple Steps
      </h2>

      <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
        <!-- Step 1 -->
        <div class="text-center">
          <div class="w-16 h-16 bg-blue-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-6">
            1
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-4">Snap & Post</h3>
          <p class="text-gray-600">
            Open your camera and take clear, well-lit photos. A good picture helps you sell 5x faster!
          </p>
        </div>

        <!-- Step 2 -->
        <div class="text-center">
          <div class="w-16 h-16 bg-blue-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-6">
            2
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-4">Add Details</h3>
          <p class="text-gray-600">
            Write a title with brand and model. Be honest about the item's age and condition to attract serious buyers.
          </p>
        </div>

        <!-- Step 3 -->
        <div class="text-center">
          <div class="w-16 h-16 bg-blue-600 text-white rounded-full flex items-center justify-center text-2xl font-bold mx-auto mb-6">
            3
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-4">Get Contacted</h3>
          <p class="text-gray-600">
            Receive calls or chats via our secure messaging system. Negotiate and agree on a safe meeting spot.
          </p>
        </div>
      </div>
    </div>

    <!-- Safety Tips Section -->
    <div class="mb-16">
      <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 text-center mb-10">
        Tips for a Safe Selling Experience
      </h2>

      <div class="bg-yellow-50 border border-yellow-100 rounded-2xl p-6 md:p-8">
        <div class="grid md:grid-cols-2 gap-6">
          <!-- Tip 1 -->
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <svg class="w-6 h-6 text-yellow-600 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
            </div>
            <div class="ml-4">
              <h4 class="font-semibold text-gray-900">Meet in Public</h4>
              <p class="text-gray-700 text-sm mt-1">Always choose a well-populated, public location with CCTV for exchanges.</p>
            </div>
          </div>

          <!-- Tip 2 -->
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <svg class="w-6 h-6 text-yellow-600 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div class="ml-4">
              <h4 class="font-semibold text-gray-900">Payment Verification First</h4>
              <p class="text-gray-700 text-sm mt-1">Verify your balance via banking app or USSD code before handing over items.</p>
            </div>
          </div>

          <!-- Tip 3 -->
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <svg class="w-6 h-6 text-yellow-600 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
              </svg>
            </div>
            <div class="ml-4">
              <h4 class="font-semibold text-gray-900">Data Security</h4>
              <p class="text-gray-700 text-sm mt-1">Perform factory reset and log out of all accounts before selling phones.</p>
            </div>
          </div>

          <!-- Tip 4 -->
          <div class="flex items-start">
            <div class="flex-shrink-0">
              <svg class="w-6 h-6 text-yellow-600 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
              </svg>
            </div>
            <div class="ml-4">
              <h4 class="font-semibold text-gray-900">Maintain Honesty</h4>
              <p class="text-gray-700 text-sm mt-1">Accurate descriptions lead to better ratings and long-term reputation.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- FAQ Section -->
    <div class="mb-16">
      <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 text-center mb-10">
        Frequently Asked Questions
      </h2>

      <div class="max-w-3xl mx-auto space-y-6">
        <!-- FAQ 1 -->
        <div class="border border-gray-200 rounded-xl p-6">
          <h3 class="font-bold text-gray-900 mb-2">Is it free to sell online in Ethiopia on Marketplace Ethiopia?</h3>
          <p class="text-gray-600">Yes! Registering an account and posting ads for general items is completely free. We also offer premium "Boost" packages for those who want to reach even more buyers.</p>
        </div>

        <!-- FAQ 2 -->
        <div class="border border-gray-200 rounded-xl p-6">
          <h3 class="font-bold text-gray-900 mb-2">How do I sell my phone online in Ethiopia safely?</h3>
          <p class="text-gray-600">Back up your data, perform a factory reset, and list it with clear photos. Meet in a public place and verify payment before handing over the device.</p>
        </div>

        <!-- FAQ 3 -->
        <div class="border border-gray-200 rounded-xl p-6">
          <h3 class="font-bold text-gray-900 mb-2">Can I sell property online here?</h3>
          <p class="text-gray-600">Absolutely. Marketplace Ethiopia is a preferred platform for real estate agents and homeowners to reach serious tenants and buyers across Ethiopia.</p>
        </div>
      </div>
    </div>

    <!-- CTA Section -->
    <div class="text-center bg-gradient-to-r bg-green-700 rounded-2xl p-12 text-white">
      <h2 class="text-3xl sm:text-4xl font-bold mb-6">Join the Marketplace Ethiopia Community Today</h2>
      <p class="text-lg mb-8 max-w-2xl mx-auto text-blue-100">
        Whether you want to sell phone online in Ethiopia or sell property online to serious investors, we provide the tools you need to succeed.
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="/register" class="bg-white text-green-700 hover:bg-gray-100 font-bold py-3 px-8 rounded-lg transition duration-300">
          Start Selling Now
        </a>
        <a href="/all-categories" class="bg-transparent border-2 border-white hover:bg-white/10 text-white font-bold py-3 px-8 rounded-lg transition duration-300">
          Browse Categories
        </a>
      </div>
      <p class="mt-8 text-blue-100 font-medium">
        Don't let your unused items gather dust. Choose Marketplace Ethiopia where Ethiopia buys and sells.
      </p>
    </div>

  </div>
</section>

@include('public.layouts.footer')
