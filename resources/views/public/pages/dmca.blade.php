@include('public.layouts.header')
@include('public.layouts.nav')
@include('public.components.mobile.mobile-nav')
@include('public.layouts.search')

<div class="max-w-4xl mx-auto bg-white my-10">

  <!-- Article Header -->
  <header class="max-w-4xl mx-auto px-4 pt-10 sm:px-6">
    <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 mb-4">
      Intellectual Property & <span class="text-green-700">Copyright Policy</span>
    </h1>
  </header>

  <main class="max-w-4xl mx-auto px-4 pb-24 sm:px-6">
    <div class="prose prose-slate prose-lg max-w-none">

      <!-- 1. Introduction -->
      <section class="mb-12">
        <h2 class="text-2xl font-bold text-slate-900 mb-4 flex items-center gap-3">
          <span class="text-green-700">1.</span> Introduction
        </h2>
        <p class="text-slate-600 leading-relaxed">
          Marketplace Ethiopia respects the intellectual property rights of others and expects users of the platform to do the same. We operate as an online classified advertising platform that allows independent users to post listings for goods and services.
        </p>
        <div class="mt-4 p-4 bg-slate-50 border-l-4 border-green-700 rounded-r-lg">
          <p class="text-sm text-slate-700 italic">
            <strong>Note:</strong> We do not manufacture, distribute, or directly sell the products listed on the website.
          </p>
        </div>
      </section>

      <!-- 2. User Responsibility -->
      <section class="mb-12">
        <h2 class="text-2xl font-bold text-slate-900 mb-4 flex items-center gap-3">
          <span class="text-green-700">2.</span> User Responsibility
        </h2>
        <p class="text-slate-600 mb-4">By posting content on Marketplace Ethiopia, users represent and warrant that:</p>
        <ul class="space-y-3">
          <li class="flex items-start gap-3 text-slate-600">
            <svg class="w-5 h-5 text-green-700 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            They own the content they post or have obtained all necessary rights and licenses.
          </li>
          <li class="flex items-start gap-3 text-slate-600">
            <svg class="w-5 h-5 text-green-700 mt-1 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
            Listings do not infringe any copyright, trademark, patent, or other intellectual property rights.
          </li>
        </ul>
      </section>

      <!-- 3. Reporting -->
      <section class="mb-6 bg-slate-50 border border-slate-200 rounded-2xl p-6 sm:p-8">
        <h2 class="text-2xl font-bold text-slate-900 mb-6 flex items-center gap-3">
          <span class="text-green-700">3.</span> Reporting Infringement
        </h2>
        <p class="text-slate-600 mb-6">To submit a complaint, please provide a written notice including:</p>
        <div class="grid sm:grid-cols-2 gap-4 text-sm text-slate-700">
          <div class="bg-white p-4 rounded-lg border border-slate-200"><span class="font-bold text-green-700">01.</span> Description of the IP right claimed to be infringed.</div>
          <div class="bg-white p-4 rounded-lg border border-slate-200"><span class="font-bold text-green-700">02.</span> Exact URL(s) of the infringing content.</div>
          <div class="bg-white p-4 rounded-lg border border-slate-200"><span class="font-bold text-green-700">03.</span> Your full contact information (Name, Phone, Email).</div>
          <div class="bg-white p-4 rounded-lg border border-slate-200"><span class="font-bold text-green-700">04.</span> A statement of "Good Faith Belief."</div>
          <div class="bg-white p-4 rounded-lg border border-slate-200"><span class="font-bold text-green-700">05.</span> Statement of Accuracy & Authorization.</div>
          <div class="bg-white p-4 rounded-lg border border-slate-200"><span class="font-bold text-green-700">06.</span> Physical or electronic signature.</div>
        </div>
        <div class="mt-8 pt-6 border-t border-slate-200">
          <p class="text-slate-900 font-bold mb-2">Submit notices to:</p>
          <p class="text-green-700 font-medium">Email: info@marketplace.et</p>
          <p class="text-slate-500 text-sm">Subject: Intellectual Property Complaint</p>
        </div>
      </section>

      <!-- 4-5. Review & Counter -->
      <div class="grid md:grid-cols-2 gap-12 mb-6">
        <section>
          <h2 class="text-xl font-bold text-slate-900 mb-4 tracking-tight">4. Review Procedure</h2>
          <p class="text-slate-600 text-sm leading-relaxed">
            Upon a valid complaint, content may be temporarily disabled. Users may be asked to provide proof of ownership. We reserve the right to remove content at our sole discretion where infringement appears likely.
          </p>
        </section>
        <section>
          <h2 class="text-xl font-bold text-slate-900 mb-4 tracking-tight">5. Counter-Notification</h2>
          <p class="text-slate-600 text-sm leading-relaxed">
            If you believe content was removed in error, submit a counter-notice identifying the previous location and the reason for error. We reserve the right to restore content if appropriate.
          </p>
        </section>
      </div>

      <!-- 6. Repeat Infringers -->
      <section class="mb-12 border-t border-slate-100 ">
        <h2 class="text-2xl font-bold text-slate-900 mb-4">6. Repeat Infringers</h2>
        <p class="text-slate-600 leading-relaxed">
          Marketplace Ethiopia maintains a strict policy of terminating accounts of users found to be repeat infringers. This includes permanent removal of listings, account suspension, and permanent termination.
        </p>
      </section>

      <!-- 7. Limitation of Liability -->
      <section class="mb-12">
        <h2 class="text-2xl font-bold text-slate-900 mb-4">7. Limitation of Liability</h2>
        <p class="text-slate-600 leading-relaxed">
          Marketplace Ethiopia acts as an intermediary platform. We do not pre-screen all listings. We are not liable for user-generated content posted by third parties.
        </p>
      </section>

      <!-- 8-9. Footer Info -->
      <section class="mt-20 p-8 bg-green-50 rounded-2xl flex flex-col md:flex-row justify-between items-center gap-8">
        <div>
          <h2 class="text-xl font-bold text-green-900 mb-2">Have a question?</h2>
          <p class="text-green-800 text-sm">Our legal team is here to assist with intellectual property concerns.</p>
        </div>
        <div class="flex flex-col sm:flex-row gap-4 w-full md:w-auto">
          <a href="mailto:info@marketplace.et" class="bg-green-700 text-white px-6 py-3 rounded-xl font-bold text-center hover:bg-green-800 transition-colors">
            Email Us
          </a>
          <a href="tel:{{ config('global.site_phone') }}" class="bg-white text-green-700 border border-green-200 px-6 py-3 rounded-xl font-bold text-center hover:bg-green-50 transition-colors text-sm">
            {{ config('global.site_phone') }}
          </a>
        </div>
      </section>
    </div>
  </main>


</div>

@include('public.layouts.footer')


