@include('public.layouts.header')
@include('public.layouts.nav')

<section class="w-full md:w-3/6 mx-auto p-4 pb-24 min-h-screen flex items-center justify-center">
    <div class="bg-white rounded-2xl shadow-md p-8 text-center w-full max-w-md">

        <div class="flex justify-center mb-4">
            <div class="bg-green-100 rounded-full p-4">
                <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>
        </div>

        <h1 class="text-2xl font-bold text-gray-800 mb-2">Order Confirmed!</h1>

        @if(session('ad_title'))
            <p class="text-gray-600 mb-1">
                You successfully purchased <strong>{{ session('ad_title') }}</strong>.
            </p>
        @else
            <p class="text-gray-600 mb-1">Your order has been placed successfully.</p>
        @endif

        @if(session('amount'))
            <p class="text-gray-500 text-sm mb-1">Amount paid: <strong>Br {{ session('amount') }}</strong></p>
        @endif

        @if(session('order_code'))
            <p class="text-gray-500 text-sm mb-4">Order: <strong class="font-mono tracking-widest">{{ session('order_code') }}</strong></p>
        @endif

        <div class="bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-left text-sm text-green-900 mb-6 space-y-1.5">
            <p class="font-semibold"><i class="bi bi-shield-lock-fill mr-1"></i> Your payment is held securely</p>
            <p>The seller will contact you to arrange delivery{{ session('ship_by') ? ' and must ship by ' . session('ship_by') : '' }}. If they don't, you get a full refund.</p>
            <p>Once you receive the item, open your order and tap <strong>Confirm received</strong> to release the payment to the seller.</p>
        </div>

        <div class="flex flex-col sm:flex-row gap-3 justify-center">
            @if(session('order_id'))
            <a href="/user/order-details/{{ session('order_id') }}"
               class="inline-block bg-dark_green hover:opacity-90 text-white px-8 py-3 rounded-full font-semibold transition">
                View Order
            </a>
            @endif
            <a href="/user/orders"
               class="inline-block border border-dark_green text-dark_green hover:bg-green-50 px-8 py-3 rounded-full font-semibold transition">
                My Orders
            </a>
        </div>
    </div>
</section>

@include('public.layouts.footer')
