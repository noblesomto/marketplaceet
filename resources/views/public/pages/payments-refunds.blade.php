@include('public.layouts.header')
@include('public.layouts.nav')
@include('public.components.mobile.mobile-nav')
@include('public.layouts.search')

<div class="max-w-4xl mx-auto bg-white my-10">
    <section class="py-2 px-4 sm:px-6 lg:px-8">
      <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Payment & Refund Policy</h1>
        <div class="flex flex-col sm:flex-row gap-4 text-sm text-gray-500 mb-8">
            <p>Effective Date: August 1, 2025</p>
            <p>Last Updated: January 4, 2026</p>
        </div>
        <p class="text-gray-600 mb-8">
          At Marketplace Ethiopia, we believe in transparency, fairness, and trust when it comes to payments and refunds. This Payment & Refund Policy explains how charges are processed on our platform, what services are billable, and under what conditions users may be eligible for refunds.
        </p>
        <p class="text-gray-600">
          By using any paid service on www.marketplace.et, you agree to the terms outlined in this policy.
        </p>
      </div>
    </section>

    <section class="py-2 px-4 sm:px-6 lg:px-8">
      <div class="max-w-4xl mx-auto">
        <h2 class="text-2xl font-semibold text-gray-900 mb-6">1. Paid Services on Marketplace Ethiopia</h2>
        <p class="text-gray-600 mb-4">
          Marketplace Ethiopia offers both free and paid features for users. Paid services may include:
        </p>
        <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-6">
          <li>Boosted Listings (ad visibility promotion)</li>
          <li>Featured Listings (homepage or top-of-category placement)</li>
          <li>Buy Direct transactions (secure payment between buyers and sellers)</li>
          <li>Vendor Subscriptions</li>
          <li>Other promotional services or tools</li>
        </ul>
        <p class="text-gray-600">
          All paid services are optional and billed based on the package selected.
        </p>
      </div>
    </section>

    <section class="py-2 px-4 sm:px-6 lg:px-8">
      <div class="max-w-4xl mx-auto">
        <h2 class="text-2xl font-semibold text-gray-900 mb-6">2. Payment Methods Accepted</h2>
        <p class="text-gray-600 mb-4">
          We accept the following payment methods:
        </p>
        <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-6">
          <li>Debit/Credit Cards (Visa, Mastercard, Verve)</li>
          <li>Bank Transfers</li>
          <li>USSD Codes</li>
          <li>Third-Party Payment Gateways (Flutterwave)</li>
        </ul>
        <p class="text-gray-600">
          Your payment details are encrypted and securely processed by our licensed payment partners.
        </p>
      </div>
    </section>

    <section class="py-2 px-4 sm:px-6 lg:px-8">
      <div class="max-w-4xl mx-auto">
        <h2 class="text-2xl font-semibold text-gray-900 mb-6">3. Ad Promotion Billing & Validity</h2>
        <p class="text-gray-600 mb-4">
          When you pay to promote a listing (Boost or Feature), the following conditions apply:
        </p>
        <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-6">
          <li>Payment is required upfront before promotion begins.</li>
          <li>Promotion durations are based on the selected package (e.g., 7 days, 30 days).</li>
          <li>Once paid, ad visibility cannot be paused, transferred, or modified.</li>
          <li>Changes to the ad content may require re-approval but will not extend the promotion period.</li>
        </ul>
        <p class="text-gray-600">
          If your ad violates our Terms of Use or Posting Guidelines, it may be removed without refund.
        </p>
      </div>
    </section>

    <section class="py-2 px-4 sm:px-6 lg:px-8">
      <div class="max-w-4xl mx-auto">
        <h2 class="text-2xl font-semibold text-gray-900 mb-6">4. Buy Direct Payment Terms</h2>
        <p class="text-gray-600 mb-6">
          The Buy Direct feature allows buyers to make safe purchases using Marketplace Ethiopia's secure escrow service.
        </p>

        <h3 class="text-xl font-medium text-gray-900 mb-4">For Buyers:</h3>
        <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-6">
          <li>You pay the item price plus a small buyer-protection fee using your preferred payment method.</li>
          <li>We hold the funds until you confirm that the item was received in good condition.</li>
          <li>The seller must ship within {{ \App\Services\BuyDirectSettings::shipDeadlineDays() }} days of your payment, otherwise your order is canceled and you are refunded in full automatically.</li>
          <li>After the item is shipped you have {{ \App\Services\BuyDirectSettings::autoReleaseDays() }} days to confirm receipt or report a problem. If you do neither, the payment is released to the seller.</li>
          <li>If the item is not received or is significantly different from the listing, use "Report a problem" on your order page and we will hold the payment while we review it.</li>
        </ul>

        <h3 class="text-xl font-medium text-gray-900 mb-4">For Sellers:</h3>
        <ul class="list-disc pl-6 text-gray-600 space-y-2">
          <li>Funds are released to your bank account after the buyer confirms receipt, or automatically {{ \App\Services\BuyDirectSettings::autoReleaseDays() }} days after you mark the order as shipped if the buyer takes no action.</li>
          <li>You must arrange delivery with the buyer and mark the order as shipped within {{ \App\Services\BuyDirectSettings::shipDeadlineDays() }} days of payment, or the order is canceled and the buyer refunded.</li>
          <li>You receive the full item price; the buyer-protection fee is paid by the buyer.</li>
          <li>In case of disputes, payments may be delayed or withheld pending investigation.</li>
          <li>You are responsible for delivering or shipping the item on time and as described.</li>
        </ul>
      </div>
    </section>

    <section class="py-2 px-4 sm:px-6 lg:px-8">
      <div class="max-w-4xl mx-auto">
        <h2 class="text-2xl font-semibold text-gray-900 mb-6">5. Refund Policy</h2>
        <p class="text-gray-600 mb-6">
          Refunds are considered under the following circumstances:
        </p>

        <h3 class="text-xl font-medium text-gray-900 mb-4">a. Ad Promotion Refunds</h3>
        <p class="text-gray-600 mb-4">
          Refunds may be issued only if:
        </p>
        <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-6">
          <li>The promoted ad was not displayed due to a verified technical error on our end</li>
          <li>You were wrongly charged multiple times</li>
          <li>Your ad was rejected before it went live</li>
        </ul>
        <p class="text-gray-600 mb-6">
          Refunds are not granted if:
        </p>
        <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-6">
          <li>You change your mind after the ad goes live</li>
          <li>Your ad was removed due to violation of our Terms or Policies</li>
          <li>You received lower engagement than expected</li>
        </ul>

        <h3 class="text-xl font-medium text-gray-900 mb-4">b. Buy Direct Refunds (for Buyers)</h3>
        <p class="text-gray-600 mb-4">
          You may request a refund if:
        </p>
        <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-6">
          <li>You did not receive the item</li>
          <li>The item is materially different from what was described</li>
          <li>The seller is unresponsive or unavailable</li>
        </ul>
        <p class="text-gray-600 mb-6">
          We will investigate disputes and may require supporting evidence (e.g., photos, chat logs, receipts).
          If resolved in your favor, your payment will be refunded within 3–7 working days to your original payment method.
        </p>

        <h3 class="text-xl font-medium text-gray-900 mb-4">c. Failed or Duplicate Transactions</h3>
        <p class="text-gray-600 mb-4">
          If you are charged twice or experience a failed transaction:
        </p>
        <ul class="list-disc pl-6 text-gray-600 space-y-2">
          <li>Contact our support team with transaction reference(s)</li>
          <li>Refunds will be issued after verification, typically within 3–5 working days</li>
        </ul>
      </div>
    </section>

    <section class="py-2 px-4 sm:px-6 lg:px-8">
      <div class="max-w-4xl mx-auto">
        <h2 class="text-2xl font-semibold text-gray-900 mb-6">6. Disputes and Chargebacks</h2>
        <ul class="list-disc pl-6 text-gray-600 space-y-2">
          <li>Initiating a chargeback through your bank without contacting our support first may result in account suspension.</li>
          <li>We encourage users to resolve disputes with the help of our support team.</li>
          <li>Repeated abuse of the refund system may lead to removal from the platform.</li>
        </ul>
      </div>
    </section>

    <section class="py-2 px-4 sm:px-6 lg:px-8">
      <div class="max-w-4xl mx-auto">
        <h2 class="text-2xl font-semibold text-gray-900 mb-6">7. How to Request a Refund</h2>
        <p class="text-gray-600 mb-6">
          To request a refund or report a payment issue:
        </p>
        <div class="flex flex-col sm:flex-row gap-4 mb-6">
          <p class="text-gray-600 flex items-center">
            <svg class="h-5 w-5 text-gray-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            <a href="mailto:support@marketplace.et" class="text-blue-600 hover:text-blue-800">support@marketplace.et</a>
          </p>
          <p class="text-gray-600 flex items-center">
            <svg class="h-5 w-5 text-gray-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
            {{ config('global.site_phone') }}
          </p>
          <p class="text-gray-600 flex items-center">
            <svg class="h-5 w-5 text-gray-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            Or use the in-app Help & Support feature on your dashboard.
          </p>
        </div>
        <p class="text-gray-600 mb-4">
          Please include:
        </p>
        <ul class="list-disc pl-6 text-gray-600 space-y-2 mb-6">
          <li>Your full name & registered email</li>
          <li>The listing or order reference number</li>
          <li>Payment proof (e.g., screenshot or bank alert)</li>
          <li>Reason for your refund request</li>
        </ul>
        <p class="text-gray-600">
          We aim to resolve all verified refund requests within 5–10 business days.
        </p>
      </div>
    </section>

    <section class="py-2 px-4 sm:px-6 lg:px-8">
      <div class="max-w-4xl mx-auto">
        <h2 class="text-2xl font-semibold text-gray-900 mb-6">8. Currency & Tax</h2>
        <ul class="list-disc pl-6 text-gray-600 space-y-2">
          <li>All charges are in Ethiopian Birr (Br)</li>
          <li>Users outside Ethiopia may incur exchange or processing fees based on their bank or card provider.</li>
        </ul>
      </div>
    </section>

    <section class="py-2 px-4 sm:px-6 lg:px-8">
      <div class="max-w-4xl mx-auto">
        <h2 class="text-2xl font-semibold text-gray-900 mb-6">9. Updates to This Policy</h2>
        <p class="text-gray-600">
          We may update this Payment & Refund Policy from time to time. The effective date at the top of the page will reflect the latest changes. Continued use of paid services after changes indicates acceptance of the updated terms.
        </p>
      </div>
    </section>

    <section class="py-2 px-4 sm:px-6 lg:px-8">
      <div class="max-w-4xl mx-auto text-center">
        <h2 class="text-2xl font-semibold text-gray-900 mb-6">Questions?</h2>
        <p class="text-gray-600 mb-8">
          If you have any concerns about a payment, refund eligibility, or how our billing system works, don't hesitate to reach out.
        </p>
        <div class="bg-blue-50 rounded-lg p-6 inline-block text-left">
          <p class="text-lg font-medium text-gray-900 mb-4">Marketplace Ethiopia – Billing & Payments Support</p>
          <div class="space-y-3">
            <p class="text-gray-600 flex items-center">
              <svg class="h-5 w-5 text-gray-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
              </svg>
              <a href="https://www.marketplace.et" class="text-blue-600 hover:text-blue-800">www.marketplace.et</a>
            </p>
            <p class="text-gray-600 flex items-center">
              <svg class="h-5 w-5 text-gray-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
              <a href="mailto:support@marketplace.et" class="text-blue-600 hover:text-blue-800">support@marketplace.et</a>
            </p>
          </div>
        </div>
      </div>
    </section>
</div>

@include('public.layouts.footer')


