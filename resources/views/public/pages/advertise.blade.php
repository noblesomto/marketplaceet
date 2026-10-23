@include('public.layouts.header')
@include('public.layouts.nav')
@include('public.components.mobile.mobile-nav')
@include('public.layouts.search')

<section class="bg-white py-12 px-4 sm:px-6 lg:px-8">
  <div class="max-w-7xl mx-auto">

    <!-- Hero -->
    <div class="text-center mb-12">
      <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
        Advertise With <span class="text-green-700">Marketplace Ethiopia</span>
      </h1>
      <p class="text-lg text-gray-600 max-w-4xl mx-auto">
        Put your brand in front of thousands of active buyers and sellers across Ethiopia every day. From banner placements to boosted listings, we'll help you reach the right audience.
      </p>
    </div>

    <!-- Options -->
    <div class="mb-16">
      <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 text-center mb-10">
        Ways to Advertise
      </h2>

      <div class="grid md:grid-cols-3 gap-8">
        <div class="bg-gray-50 p-6 rounded-xl shadow-sm border border-gray-100">
          <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18v14H3V3zm0 18h18M8 21V10"/>
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-3">Display Banner Ads</h3>
          <p class="text-gray-600">
            Feature your business on high-traffic pages such as the homepage, category pages, and search results.
          </p>
        </div>

        <div class="bg-gray-50 p-6 rounded-xl shadow-sm border border-gray-100">
          <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-3">Boosted & Featured Listings</h3>
          <p class="text-gray-600">
            Push your ads to the top of category and location pages so serious buyers see your listings first.
          </p>
        </div>

        <div class="bg-gray-50 p-6 rounded-xl shadow-sm border border-gray-100">
          <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mb-4">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
          </div>
          <h3 class="text-xl font-semibold text-gray-900 mb-3">Business & Bulk Partnerships</h3>
          <p class="text-gray-600">
            Selling in volume? We work with dealers, agents, and brands on custom storefront and bulk-listing arrangements.
          </p>
        </div>
      </div>
    </div>

    <!-- Why Advertise -->
    <div class="mb-16 bg-gray-50 rounded-2xl p-8 sm:p-12">
      <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 text-center mb-10">
        Why Advertise on Marketplace Ethiopia?
      </h2>

      <div class="grid md:grid-cols-2 gap-6 max-w-4xl mx-auto">
        <div class="flex items-start">
          <svg class="w-6 h-6 text-green-600 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <p class="ml-4 text-gray-700">Direct access to buyers already searching to spend, across every state in Ethiopia.</p>
        </div>
        <div class="flex items-start">
          <svg class="w-6 h-6 text-green-600 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <p class="ml-4 text-gray-700">Flexible placements by category and location, so your budget reaches the right shoppers.</p>
        </div>
        <div class="flex items-start">
          <svg class="w-6 h-6 text-green-600 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <p class="ml-4 text-gray-700">A dedicated team to help set up campaigns and track results.</p>
        </div>
        <div class="flex items-start">
          <svg class="w-6 h-6 text-green-600 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <p class="ml-4 text-gray-700">No long-term lock-in — start with a single campaign and scale up.</p>
        </div>
      </div>
    </div>

    <!-- CTA -->
    <div class="text-center bg-gradient-to-r bg-green-700 rounded-2xl p-12 text-white">
      <h2 class="text-3xl sm:text-4xl font-bold mb-6">Let's Talk About Your Campaign</h2>
      <p class="text-lg mb-8 max-w-2xl mx-auto text-blue-100">
        Tell us about your business and what you'd like to promote, and our team will get back to you with the best advertising option for your budget.
      </p>
      <div class="flex flex-col sm:flex-row gap-4 justify-center">
        <a href="{{ route('contact') }}" class="bg-white text-green-700 hover:bg-gray-100 font-bold py-3 px-8 rounded-lg transition duration-300">
          Contact Our Team
        </a>
        <a href="mailto:{{ config('global.site_email') }}" class="bg-transparent border-2 border-white hover:bg-white/10 text-white font-bold py-3 px-8 rounded-lg transition duration-300">
          {{ config('global.site_email') }}
        </a>
      </div>
    </div>

  </div>
</section>

@include('public.layouts.footer')
