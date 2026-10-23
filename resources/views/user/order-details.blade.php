@include('user.layouts.header')
@include('user.layouts.nav')
@include('user.layouts.back-nav')
@include('user.layouts.search')

@php
    $productName  = $payment->advert->ad_title ?? '—';
    $productState = $payment->advert->state ?? null;
    $adLink       = isset($payment->advert->state_slug, $payment->advert->title_slug, $payment->advert->ad_id)
                      ? url($payment->advert->state_slug . '/' . $payment->advert->title_slug . '/' . $payment->advert->ad_id)
                      : '#';

    $isPaid    = ($payment->payment_status ?? '') === 'paid';
    $orderDate = $payment->created_at ? date('d M Y', strtotime($payment->created_at)) : '—';
    $status    = \App\Support\BuyDirectStatus::for($payment, 'buyer');
    $badge     = $status;
    $banner    = $status['banner'];
    $actions   = $status['actions'];
    $delivery  = \App\Support\BuyDirectStatus::deliveryFields($payment);

    // Orders placed while courier integrations were on still show their pickup point.
    $legacyPickup = $payment->cityLocation
        ? trim(implode(', ', array_filter([$payment->cityLocation->address ?? null, $payment->cityLocation->city ?? null, $payment->cityLocation->state->name ?? null])))
        : null;
@endphp

<section class="w-full md:w-3/6 mx-auto px-3 py-4 text-sm" style="max-width:640px;padding-bottom:7rem;">

    @include('public.components.flash-message')

    {{-- Page title + shipping status --}}
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-base font-bold text-gray-800">Order #{{ $payment->order_code ?? $payment->id }}</h1>
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold border {{ $badge['class'] }}">
            <i class="bi {{ $badge['icon'] }}"></i> {{ $badge['label'] }}
        </span>
    </div>

    {{-- Status update banner --}}
    @if($banner)
    <div class="flex items-center gap-2 px-4 py-3 rounded-xl mb-3 text-xs font-medium {{ $banner['class'] }}">
        <i class="bi {{ $banner['icon'] }} text-base flex-shrink-0"></i>
        <span>{{ $banner['text'] }}</span>
    </div>
    @endif

    {{-- ── 1. Item Purchased ── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-3 overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 border-b border-gray-100">
            <i class="bi bi-bag-check-fill text-dark_green"></i>
            <span class="font-semibold text-gray-700 text-xs uppercase tracking-wide">Item Purchased</span>
        </div>
        <div class="flex items-center gap-3 px-4 py-3">
            <a href="{{ $adLink }}" class="flex-shrink-0 w-16 h-16 rounded-lg overflow-hidden bg-gray-100 block">
                @if($payment->advert && $payment->advert->hasMedia('images'))
                    <img class="w-full h-full object-cover"
                         src="{{ $payment->advert->getFirstMediaUrl('images', 'thumbnail') }}"
                         onerror="this.src='{{ asset('frontend/images/default.png') }}'">
                @else
                    <img class="w-full h-full object-cover" src="{{ asset('frontend/images/default.png') }}" alt="Product">
                @endif
            </a>
            <div class="flex-1 min-w-0">
                @if($productState)
                <div class="flex items-center gap-1 text-xs text-gray-400 mb-0.5">
                    <i class="bi bi-geo-alt-fill text-dark_green" style="font-size:0.65rem;"></i>
                    {{ $productState }}
                </div>
                @endif
                <div class="text-xs text-gray-400 mb-0.5">Product Name</div>
                <a href="{{ $adLink }}" class="font-semibold text-gray-800 leading-snug line-clamp-2 block no-underline hover:text-dark_green">
                    {{ $productName }}
                </a>
                <div class="text-base font-black text-dark_green mt-1">Br {{ number_format((float) $payment->amount, 0, '.', ',') }}</div>
            </div>
        </div>
    </div>

    {{-- ── 2. Delivery ── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-3 overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 border-b border-gray-100">
            <i class="bi bi-geo-alt-fill text-dark_green"></i>
            <span class="font-semibold text-gray-700 text-xs uppercase tracking-wide">Delivery</span>
        </div>
        <div class="px-4 py-3 space-y-2">
            <div class="flex justify-between gap-4">
                <span class="text-gray-400 flex-shrink-0">Contact</span>
                <span class="font-medium text-gray-800 text-right">{{ $delivery['name'] ?: '—' }} · {{ $delivery['phone'] ?? '—' }}</span>
            </div>
            <div class="border-t border-gray-50"></div>
            <div class="flex justify-between gap-4">
                <span class="text-gray-400 flex-shrink-0">Location</span>
                <span class="font-medium text-gray-800 text-right">{{ $delivery['full'] ?: ($legacyPickup ?: '—') }}</span>
            </div>
            <div class="border-t border-gray-50"></div>
            <p class="text-xs text-gray-500 pt-1"><i class="bi bi-telephone mr-1"></i>The seller will contact you to arrange delivery.</p>
        </div>
    </div>

    {{-- ── 3. Order Summary ── --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm mb-3 overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 border-b border-gray-100">
            <i class="bi bi-receipt text-dark_green"></i>
            <span class="font-semibold text-gray-700 text-xs uppercase tracking-wide">Order Summary</span>
        </div>
        <div class="px-4 py-3 space-y-2">
            <div class="flex justify-between">
                <span class="text-gray-400">Order Date</span>
                <span class="font-medium text-gray-800">{{ $orderDate }}</span>
            </div>
            <div class="border-t border-gray-50"></div>
            <div class="flex justify-between">
                <span class="text-gray-400">Payment Status</span>
                <span class="font-semibold {{ $isPaid ? 'text-green-700' : 'text-amber-600' }}">
                    {{ $isPaid ? '✓ Paid' : '⏳ Pending' }}
                </span>
            </div>
            @if($payment->payment_reference)
            <div class="border-t border-gray-50"></div>
            <div class="flex justify-between gap-4">
                <span class="text-gray-400 flex-shrink-0">Reference</span>
                <span class="font-medium text-gray-800 font-mono text-right text-xs">{{ $payment->payment_reference }}</span>
            </div>
            @endif
            <div class="border-t border-gray-50"></div>
            <div class="flex justify-between">
                <span class="text-gray-400">Item Price</span>
                <span class="font-medium text-gray-800">Br {{ number_format((float) $payment->amount, 2) }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400">Buyer Protection</span>
                <span class="font-medium text-gray-800">Br {{ number_format((float) $payment->commission, 2) }}</span>
            </div>
            <div class="border-t border-gray-50"></div>
            <div class="flex justify-between">
                <span class="text-gray-400">Amount Paid</span>
                <span class="font-black text-dark_green text-base">Br {{ number_format((float) $payment->amount_paid, 2) }}</span>
            </div>
        </div>
    </div>

    {{-- ── 4. Actions ── --}}
    @if($actions['can_confirm_receipt'] || $actions['can_cancel'] || $actions['can_report_problem'])
    <div class="bg-white rounded-xl border border-green-200 shadow-sm mb-3 overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-3 bg-green-50 border-b border-green-100">
            <i class="bi bi-box-seam-fill text-dark_green"></i>
            <span class="font-semibold text-dark_green text-xs uppercase tracking-wide">Received Your Order?</span>
        </div>
        <div class="px-4 py-4 text-xs text-gray-600 leading-relaxed space-y-3">
            <p>Only confirm once you have the item and you're happy with it. Confirming releases the payment to the seller and can't be undone.</p>
            <div class="flex flex-wrap gap-2">
                @if($actions['can_cancel'])
                    <button id="cancelBtn" type="button"
                            onclick="cancelOrder({{ $payment->id }})"
                            class="flex-1 min-w-[8rem] py-2.5 bg-white text-red-600 font-semibold rounded-lg text-sm border border-red-200 hover:bg-red-50 transition-colors flex items-center justify-center gap-1.5 text-center">
                        <i class="bi bi-x-circle"></i> Cancel Order
                    </button>
                @endif
                @if($actions['can_confirm_receipt'])
                <button id="confirmBtn" type="button"
                        onclick="confirmDelivery({{ $payment->id }})"
                        class="flex-1 min-w-[8rem] py-2.5 bg-dark_green text-white font-semibold rounded-lg text-sm hover:bg-green-800 transition-colors flex items-center justify-center gap-1.5 text-center">
                    <i class="bi bi-check-circle"></i> Confirm Received
                </button>
                @endif
            </div>

            @if($actions['can_report_problem'])
                <button id="reportBtn" type="button" onclick="reportProblem({{ $payment->id }})"
                        class="w-full py-2 text-orange-700 font-semibold rounded-lg text-xs border border-orange-200 bg-orange-50 hover:bg-orange-100 transition-colors flex items-center justify-center gap-1.5">
                    <i class="bi bi-exclamation-triangle"></i> Report a Problem
                </button>
            @endif

            @if($actions['can_cancel'])
                <p class="text-gray-400 leading-relaxed">
                    <i class="bi bi-info-circle mr-1"></i>
                    You can cancel for a full refund any time before the seller ships your order. After that, use "Report a Problem" if something is wrong.
                </p>
            @endif
        </div>
    </div>
    @endif

    @if($payment->dispute_status !== 'none' && $payment->dispute_reason)
    <div class="bg-white rounded-xl border border-orange-200 shadow-sm mb-3 overflow-hidden">
        <div class="flex items-center gap-2 px-4 py-3 bg-orange-50 border-b border-orange-100">
            <i class="bi bi-chat-left-text text-orange-700"></i>
            <span class="font-semibold text-orange-800 text-xs uppercase tracking-wide">Your Report</span>
        </div>
        <div class="px-4 py-3 text-xs text-gray-700 space-y-2">
            <p class="whitespace-pre-line">{{ $payment->dispute_reason }}</p>
            @if($payment->dispute_status === 'resolved')
                <p class="text-gray-500"><strong>Resolution:</strong> {{ $payment->dispute_resolution === 'refund' ? 'Refund to you' : 'Payment released to the seller' }}{{ $payment->dispute_admin_note ? ' — ' . $payment->dispute_admin_note : '' }}</p>
            @endif
        </div>
    </div>
    @endif

    {{-- ── 6. Help note ── --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl px-4 py-3 mb-4 text-xs text-blue-800 leading-relaxed">
        <p><i class="bi bi-shield-lock-fill mr-1"></i>
        <strong>Secured by Marketplace Ethiopia.</strong> If you have any questions or require assistance,
        please <a href="/contact-us" class="font-semibold underline">contact our support team</a>.</p>
    </div>

</section>

<div id="successModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" style="max-width:90vw;">
        <div class="mt-3 text-center">
            <div style="width:56px;height:56px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
                <i class="bi bi-check-circle-fill" style="font-size:1.6rem;color:#15803d;"></i>
            </div>
            <h3 style="font-size:1rem;font-weight:700;color:#111827;margin-bottom:6px;">Receipt Confirmed!</h3>
            <p style="font-size:0.82rem;color:#6b7280;margin-bottom:20px;">Thanks for confirming. The payment has been released to the seller.</p>
            <button id="closeModal"
                    style="background:#326916;color:#fff;padding:9px 28px;border-radius:8px;border:none;font-weight:600;font-size:0.85rem;cursor:pointer;">
                Done
            </button>
        </div>
    </div>
</div>

<div id="cancelModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" style="max-width:90vw;">
        <div class="mt-3 text-center">
            <div style="width:56px;height:56px;background:#fee2e2;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
                <i class="bi bi-x-circle-fill" style="font-size:1.6rem;color:#dc2626;"></i>
            </div>
            <h3 style="font-size:1rem;font-weight:700;color:#111827;margin-bottom:6px;">Order Canceled</h3>
            <p style="font-size:0.82rem;color:#6b7280;margin-bottom:20px;">Your refund is being processed and will be credited back to you shortly.</p>
            <button id="closeCancelModal"
                    style="background:#dc2626;color:#fff;padding:9px 28px;border-radius:8px;border:none;font-weight:600;font-size:0.85rem;cursor:pointer;">
                Done
            </button>
        </div>
    </div>
</div>

@include('user.layouts.footer')

<script>
function confirmDelivery(orderId) {
    Swal.fire({
        title: 'Confirm you received the item?',
        text: 'This releases the payment to the seller and cannot be undone.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#326916',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, I received it',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (!result.isConfirmed) return;

        const btn = document.getElementById('confirmBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing…';

        fetch(`/user/confirm-delivery/${orderId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: 'delivered' })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const modal = document.getElementById('successModal');
                modal.classList.remove('hidden');
                document.getElementById('closeModal').onclick = () => location.reload();
                setTimeout(() => location.reload(), 4000);
            } else {
                throw new Error(data.message || 'Failed');
            }
        })
        .catch(err => {
            Swal.fire({ icon: 'error', title: 'Error', text: err.message, confirmButtonColor: '#326916' });
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-check-circle"></i> Confirm Received';
        });
    });
}

function cancelOrder(orderId) {
    Swal.fire({
        title: 'Cancel this order?',
        text: 'This cannot be undone. You will receive a full refund.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, cancel order',
        cancelButtonText: 'Keep order'
    }).then((result) => {
        if (!result.isConfirmed) return;

        const btn = document.getElementById('cancelBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Canceling…';

        fetch(`/user/cancel-order/${orderId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const modal = document.getElementById('cancelModal');
                modal.classList.remove('hidden');
                document.getElementById('closeCancelModal').onclick = () => location.reload();
                setTimeout(() => location.reload(), 4000);
            } else {
                throw new Error(data.message || 'Failed');
            }
        })
        .catch(err => {
            Swal.fire({ icon: 'error', title: 'Error', text: err.message, confirmButtonColor: '#326916' });
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-x-circle"></i> Cancel Order';
        });
    });
}

function reportProblem(orderId) {
    Swal.fire({
        title: 'Report a problem',
        input: 'textarea',
        inputLabel: 'Tell us what went wrong (at least 10 characters)',
        inputPlaceholder: 'e.g. The item has not arrived / is damaged / is not as described',
        inputAttributes: { maxlength: 2000 },
        showCancelButton: true,
        confirmButtonColor: '#c2410c',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Submit report',
        inputValidator: (v) => (!v || v.trim().length < 10) ? 'Please describe the problem (at least 10 characters).' : undefined
    }).then((result) => {
        if (!result.isConfirmed) return;

        fetch(`/user/report-problem/${orderId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ reason: result.value })
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) throw new Error(data.message || 'Failed');
            Swal.fire({ icon: 'success', title: 'Report received', text: data.message, confirmButtonColor: '#326916' })
                .then(() => location.reload());
        })
        .catch(err => Swal.fire({ icon: 'error', title: 'Error', text: err.message, confirmButtonColor: '#326916' }));
    });
}
</script>
