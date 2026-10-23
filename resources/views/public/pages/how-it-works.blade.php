@include('public.layouts.header')
@include('public.layouts.nav')
@include('public.components.mobile.mobile-nav')
@include('public.layouts.search')

<main class="max-w-6xl mx-auto px-4 py-12">
  <!-- Hero -->
  <section class="grid md:grid-cols-2 gap-8 items-center mb-16">
    <div>
      <h2 class="text-3xl md:text-4xl font-extrabold mb-4 text-gray-900">How Marketplace Ethiopia works</h2>
      <p class="text-lg text-gray-600 mb-6">A simple, secure marketplace to buy and sell new and used items. Follow these easy steps and start earning or finding great deals today.</p>
      <div class="flex gap-3">
        <a href="/register" class="px-5 py-3 bg-[#B5E93F] hover:bg-[#AFD145] text-[#326916] rounded-md font-semibold transition-colors">Get started</a>
        <a href="/faq" class="px-5 py-3 border border-gray-200 hover:border-gray-300 rounded-md text-gray-700 hover:text-gray-900 transition-colors">Read the FAQ</a>
      </div>
    </div>
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
      <img src="{{ asset('frontend/images/how/1.png') }}" alt="marketplace" class="w-full h-full object-cover" />
    </div>
  </section>

  <!-- Selling Steps -->
  <section id="selling" class="mb-16">
    <div class="text-center mb-10">
      <h3 class="text-2xl font-bold mb-2 text-gray-900">Selling is simple</h3>
      <p class="text-gray-500 max-w-2xl mx-auto">Follow these three easy steps to turn your unused items into cash</p>
    </div>

    <div class="grid md:grid-cols-3 gap-8">
      <article class="bg-white rounded-xl p-6 shadow-md hover:shadow-lg transition-shadow">
        <div class="mb-4 overflow-hidden rounded-lg">
          <img src="{{ asset('frontend/images/how/2.png') }}" alt="Create listing" class="w-full h-48 object-cover hover:scale-105 transition-transform" />
        </div>
        <div class="flex items-center gap-4 mb-3">
          <div class="w-10 h-10 rounded-full bg-[#B5E93F] flex items-center justify-center font-semibold text-[#326916]">1</div>
          <h4 class="text-lg font-semibold text-gray-800">Create a listing</h4>
        </div>
        <p class="text-sm text-gray-600">Take photos, add a clear title and honest description, set a price — then publish. We'll guide you through shipping options and recommended pricing.</p>
      </article>

      <article class="bg-white rounded-xl p-6 shadow-md hover:shadow-lg transition-shadow">
        <div class="mb-4 overflow-hidden rounded-lg">
          <img src="{{ asset('frontend/images/how/3.png') }}" alt="Connect with buyers" class="w-full h-48 object-cover hover:scale-105 transition-transform" />
        </div>
        <div class="flex items-center gap-4 mb-3">
          <div class="w-10 h-10 rounded-full bg-[#B5E93F] flex items-center justify-center font-semibold text-[#326916]">2</div>
          <h4 class="text-lg font-semibold text-gray-800">Sell it, ship it</h4>
        </div>
        <p class="text-sm text-gray-600">Sold! Contact the buyer to arrange delivery, ship the item within {{ \App\Services\BuyDirectSettings::shipDeadlineDays() }} days and mark it as shipped. You get paid once the buyer confirms receipt.</p>
      </article>

      <article class="bg-white rounded-xl p-6 shadow-md hover:shadow-lg transition-shadow">
        <div class="mb-4 overflow-hidden rounded-lg">
          <img src="{{ asset('frontend/images/how/4.png') }}" alt="Ship items" class="w-full h-48 object-cover hover:scale-105 transition-transform" />
        </div>
        <div class="flex items-center gap-4 mb-3">
          <div class="w-10 h-10 rounded-full bg-[#B5E93F] flex items-center justify-center font-semibold text-[#326916]">3</div>
          <h4 class="text-lg font-semibold text-gray-800">It’s payday!</h4>
        </div>
        <p class="text-sm text-gray-600">There are zero selling fees, so what you earn is yours to keep. Payment is released to you when the buyer confirms delivery</p>
      </article>
    </div>
  </section>

  <!-- Trust & Safety -->
  <section class="mb-16 bg-white p-8 rounded-xl shadow-md">
    <div class="text-center mb-8">
      <h3 class="text-2xl font-bold text-gray-900 mb-2">Trust & safety</h3>
      <p class="text-gray-500 max-w-2xl mx-auto">We prioritize your security in every transaction</p>
    </div>

    <div class="grid md:grid-cols-3 gap-8">
      <div class="p-4 rounded-lg hover:bg-gray-50 transition-colors">
        <div class="w-12 h-12 rounded-full bg-[#B5E93F]/20 flex items-center justify-center mb-3">
          <svg class="w-6 h-6 text-[#326916]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
          </svg>
        </div>
        <h5 class="font-semibold text-gray-800 mb-2">Verified payments</h5>
        <p class="text-sm text-gray-600">All transactions go through our secure payment system — we hold funds until delivery is confirmed.</p>
      </div>

      <div class="p-4 rounded-lg hover:bg-gray-50 transition-colors">
        <div class="w-12 h-12 rounded-full bg-[#B5E93F]/20 flex items-center justify-center mb-3">
          <svg class="w-6 h-6 text-[#326916]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
        </div>
        <h5 class="font-semibold text-gray-800 mb-2">Community guidelines</h5>
        <p class="text-sm text-gray-600">Clear rules keep listings accurate. Reports are reviewed by our team within 48 hours.</p>
      </div>

      <div class="p-4 rounded-lg hover:bg-gray-50 transition-colors">
        <div class="w-12 h-12 rounded-full bg-[#B5E93F]/20 flex items-center justify-center mb-3">
          <svg class="w-6 h-6 text-[#326916]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h5 class="font-semibold text-gray-800 mb-2">Dispute support</h5>
        <p class="text-sm text-gray-600">If something goes wrong, our support team helps mediate and can refund buyers where appropriate.</p>
      </div>
    </div>
  </section>

  <!-- Buying Steps -->
  <section id="buying" class="mb-16">
    <div class="text-center mb-10">
      <h3 class="text-2xl font-bold mb-2 text-gray-900">Shop safely and securely</h3>
      <p class="text-gray-500 max-w-2xl mx-auto">How buying works on Marketplace Ethiopia</p>
    </div>

    <div class="grid md:grid-cols-3 gap-8">
      <article class="bg-white rounded-xl p-6 shadow-md hover:shadow-lg transition-shadow">
        <div class="mb-4 overflow-hidden rounded-lg">
          <img src="{{ asset('frontend/images/how/5.png') }}" alt="Find items" class="w-full h-48 object-cover hover:scale-105 transition-transform" />
        </div>
        <div class="flex items-center gap-4 mb-3">
          <div class="w-10 h-10 rounded-full bg-[#B5E93F] flex items-center justify-center font-semibold text-[#326916]">1</div>
          <h4 class="text-lg font-semibold text-gray-800">Find it</h4>
        </div>
        <p class="text-sm text-gray-600">Browse thousands of listings or search for exactly what you want. Filter by location, price, and condition.</p>
      </article>

      <article class="bg-white rounded-xl p-6 shadow-md hover:shadow-lg transition-shadow">
        <div class="mb-4 overflow-hidden rounded-lg">
          <img src="{{ asset('frontend/images/how/6.png') }}" alt="Purchase items" class="w-full h-48 object-cover hover:scale-105 transition-transform" />
        </div>
        <div class="flex items-center gap-4 mb-3">
          <div class="w-10 h-10 rounded-full bg-[#B5E93F] flex items-center justify-center font-semibold text-[#326916]">2</div>
          <h4 class="text-lg font-semibold text-gray-800">Buy it</h4>
        </div>
        <p class="text-sm text-gray-600">Message the seller with any questions, then pay securely through our platform. Your payment is protected until delivery.</p>
      </article>

      <article class="bg-white rounded-xl p-6 shadow-md hover:shadow-lg transition-shadow">
        <div class="mb-4 overflow-hidden rounded-lg">
          <img src="{{ asset('frontend/images/how/7.png') }}" alt="Receive items" class="w-full h-48 object-cover hover:scale-105 transition-transform" />
        </div>
        <div class="flex items-center gap-4 mb-3">
          <div class="w-10 h-10 rounded-full bg-[#B5E93F] flex items-center justify-center font-semibold text-[#326916]">3</div>
          <h4 class="text-lg font-semibold text-gray-800">Get it</h4>
        </div>
        <p class="text-sm text-gray-600">Choose pickup or delivery options. Inspect your item and confirm receipt to release payment to the seller.</p>
      </article>
    </div>
  </section>

  <!-- CTA -->
  <section class="mb-12">
    <div class="max-w-4xl mx-auto bg-gradient-to-r from-[#326916] to-[#AFD145] text-white p-10 rounded-2xl shadow-lg text-center">
      <h4 class="text-2xl font-bold mb-3">Ready to start buying or selling?</h4>
      <p class="mb-6 max-w-2xl mx-auto">Create your free account and join thousands of other users trading safely on Marketplace Ethiopia.</p>
      <a href="/register" class="inline-block bg-white text-[#326916] px-8 py-3 rounded-md font-semibold hover:bg-gray-100 transition-colors">Sign up — it's free</a>
    </div>
  </section>
</main>

@include('public.layouts.footer')
