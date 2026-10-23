@include('user.layouts.header')
@include('user.layouts.nav')
@include('user.layouts.back-nav')
@include('user.layouts.search')

@php
    $productName = $ad->advert->ad_title ?? '—';
    $status      = \App\Support\BuyDirectStatus::for($ad, 'seller');
    $badge       = $status;
    $actions     = $status['actions'];
    $delivery    = \App\Support\BuyDirectStatus::deliveryFields($ad);
    $legacyPickup = $ad->cityLocation
        ? trim(implode(', ', array_filter([$ad->cityLocation->address ?? null, $ad->cityLocation->city ?? null, $ad->cityLocation->state->name ?? null])))
        : null;
    $hasBank = !empty($user->account_number) && !empty($user->bank_code);
@endphp

<section class="w-full md:w-3/6 mx-auto px-3 py-4 text-sm" style="max-width:640px;padding-bottom:7rem;">

    @include('public.components.flash-message')

    {{-- Page title + status badge --}}
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-base font-bold text-gray-800">Order #{{ $ad->order_code ?? $ad->id }}</h1>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border {{ $badge['class'] }}">
            <i class="bi {{ $badge['icon'] }}"></i> {{ $badge['label'] }}
        </span>
    </div>

    <div class="flex items-start gap-2 px-4 py-3 rounded-xl mb-3 text-xs font-medium {{ $status['banner']['class'] }}">
        <i class="bi {{ $status['banner']['icon'] }} text-base flex-shrink-0"></i>
        <span>{{ $status['banner']['text'] }}</span>
    </div>

    @if(!$hasBank && !in_array($status['stage'], ['canceled'], true) && $ad->payout_status !== 'paid')
    <div class="flex items-start gap-2 px-4 py-3 rounded-xl mb-3 text-xs font-medium bg-red-50 border border-red-200 text-red-800">
        <i class="bi bi-bank text-base flex-shrink-0"></i>
        <span>Add your bank details so we can pay you. <a href="/user/payments" class="font-semibold underline">Update bank details</a></span>
    </div>
    @endif

    {{-- ── 1. Buyer & Delivery ── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-3 overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 border-b border-gray-100">
            <i class="bi bi-person-fill text-dark_green"></i>
            <span class="font-semibold text-gray-700 text-xs uppercase tracking-wide">Buyer &amp; Delivery</span>
        </div>
        <div class="px-4 py-3 space-y-2">
            <div class="flex justify-between">
                <span class="text-gray-400">Name</span>
                <span class="font-medium text-gray-800">{{ $delivery['name'] ?: '—' }}</span>
            </div>
            <div class="border-t border-gray-50"></div>
            <div class="flex justify-between">
                <span class="text-gray-400">Phone</span>
                <a href="tel:{{ $delivery['phone'] }}" class="font-medium text-dark_green">{{ $delivery['phone'] ?? '—' }}</a>
            </div>
            <div class="border-t border-gray-50"></div>
            <div class="flex justify-between gap-4">
                <span class="text-gray-400 flex-shrink-0">Location</span>
                <span class="font-medium text-gray-800 text-right">{{ $delivery['full'] ?: ($legacyPickup ?: '—') }}</span>
            </div>
        </div>
    </div>

    {{-- ── 2. Item Sold ── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-3 overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 border-b border-gray-100">
            <i class="bi bi-bag-check-fill text-dark_green"></i>
            <span class="font-semibold text-gray-700 text-xs uppercase tracking-wide">Item Sold</span>
        </div>
        <div class="flex items-center gap-3 px-4 py-3">
            <div class="flex-shrink-0 w-14 h-14 rounded-lg overflow-hidden bg-gray-100">
                @if($ad->advert && $ad->advert->hasMedia('images'))
                    <img class="w-full h-full object-cover"
                         src="{{ $ad->advert->getFirstMediaUrl('images', 'thumbnail') }}"
                         alt="{{ $productName }}"
                         onerror="this.src='{{ asset('frontend/images/default.png') }}'">
                @else
                    <img class="w-full h-full object-cover" src="{{ asset('frontend/images/default.png') }}" alt="Product">
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-xs text-gray-400 mb-0.5">Product Name</div>
                <div class="font-semibold text-gray-800 leading-snug">{{ $productName }}</div>
            </div>
            <div class="text-right">
                <div class="text-xs text-gray-400 mb-0.5">You receive</div>
                <div class="font-black text-dark_green">Br {{ number_format((float) $ad->amount, 2) }}</div>
            </div>
        </div>
    </div>

    {{-- ── 3. Ship it ── --}}
    @if($actions['can_mark_shipped'])
    <div class="bg-white rounded-xl border border-green-200 shadow-sm mb-3 overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-3 bg-green-50 border-b border-green-100">
            <i class="bi bi-box-seam-fill text-dark_green"></i>
            <span class="font-semibold text-dark_green text-xs uppercase tracking-wide">It's Time to Ship</span>
        </div>
        <div class="px-4 py-4 space-y-4">
            <ol class="space-y-3">
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-dark_green text-white text-[10px] font-bold flex items-center justify-center mt-0.5">1</span>
                    <p class="text-gray-700 text-xs leading-relaxed">Call the buyer on <a href="tel:{{ $delivery['phone'] }}" class="font-semibold text-dark_green">{{ $delivery['phone'] }}</a> to agree on delivery or pickup.</p>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-dark_green text-white text-[10px] font-bold flex items-center justify-center mt-0.5">2</span>
                    <p class="text-gray-700 text-xs leading-relaxed">Package the item well and hand it over or send it to the buyer.</p>
                </li>
                <li class="flex gap-3">
                    <span class="flex-shrink-0 w-5 h-5 rounded-full bg-dark_green text-white text-[10px] font-bold flex items-center justify-center mt-0.5">3</span>
                    <p class="text-gray-700 text-xs leading-relaxed">Tap <strong>Mark as Shipped</strong> below{{ $ad->ship_by ? ' before ' . $ad->ship_by->format('d M Y, g:i A') : '' }}.</p>
                </li>
            </ol>

            <form method="POST" action="/user/update-shipping/{{ $ad->id }}" onsubmit="return confirm('Confirm that you have shipped or handed over this item?');">
                @csrf
                <input type="hidden" name="seller_status" value="shipped">
                <button type="submit" class="w-full py-2.5 bg-dark_green text-white font-semibold rounded-lg text-sm hover:bg-green-800 transition-colors flex items-center justify-center gap-1.5">
                    <i class="bi bi-truck"></i> Mark as Shipped
                </button>
            </form>

            @if($actions['can_cancel'])
            <form method="POST" action="/user/update-shipping/{{ $ad->id }}" onsubmit="return confirm('Cancel this order? The buyer will be refunded in full and your advert goes back on sale.');">
                @csrf
                <input type="hidden" name="seller_status" value="canceled">
                <button type="submit" class="w-full py-2 bg-white text-red-600 font-semibold rounded-lg text-xs border border-red-200 hover:bg-red-50 transition-colors">
                    I can't fulfil this order — cancel it
                </button>
            </form>
            @endif

            <div class="bg-amber-50 border border-amber-200 rounded-lg px-3 py-2.5 text-xs text-amber-800 leading-relaxed">
                <i class="bi bi-exclamation-triangle-fill mr-1"></i>
                If the order isn't marked as shipped in time, it is canceled automatically and the buyer is refunded.
            </div>
        </div>
    </div>
    @endif

    {{-- ── 4. Payout ── --}}
    @if(in_array($status['stage'], ['shipped', 'completed', 'disputed'], true))
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-3 overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 border-b border-gray-100">
            <i class="bi bi-cash-coin text-dark_green"></i>
            <span class="font-semibold text-gray-700 text-xs uppercase tracking-wide">Your Payout</span>
        </div>
        <div class="px-4 py-3 space-y-2">
            <div class="flex justify-between">
                <span class="text-gray-400">Amount</span>
                <span class="font-semibold text-gray-800">Br {{ number_format((float) ($ad->payout_amount ?? $ad->amount), 2) }}</span>
            </div>
            <div class="border-t border-gray-50"></div>
            <div class="flex justify-between">
                <span class="text-gray-400">Status</span>
                <span class="font-semibold {{ $ad->payout_status === 'paid' ? 'text-green-700' : 'text-amber-600' }}">
                    @switch($ad->payout_status)
                        @case('paid') Paid{{ $ad->paid_out_at ? ' · ' . $ad->paid_out_at->format('d M Y') : '' }} @break
                        @case('processing') Processing @break
                        @case('failed') Delayed — our team is on it @break
                        @default {{ $status['stage'] === 'completed' ? 'Queued' : 'Held until the buyer confirms' }}
                    @endswitch
                </span>
            </div>
        </div>
    </div>
    @endif

    {{-- ── 5. Note ── --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 mb-4 text-xs text-blue-800 leading-relaxed space-y-2">
        <p><i class="bi bi-shield-lock-fill mr-1"></i>
        <strong>Note:</strong> This sale is secured by Buy Direct. We hold the buyer's payment and release it to you when they confirm receipt, or automatically after the confirmation window ends.</p>
        <p>If you have any questions, feel free to <a href="/contact-us" class="font-semibold underline">contact our support team</a>.</p>
    </div>

</section>

@include('user.layouts.footer')
