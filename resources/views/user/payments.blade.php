@include('user.layouts.header')
@include('user.layouts.nav')
@include('user.layouts.back-nav')
@include('user.layouts.search')

<style>
/* ── Page wrapper ───────────────────────────────────── */
.orders-wrap {
    width: 100%;
    max-width: 900px;
    margin: 24px auto 100px;
    padding: 0 12px;
}

/* ── Page header ───────────────────────────────────── */
.orders-page-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 16px;
}
.orders-page-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #1a1a2e;
    display: flex;
    align-items: center;
    gap: 8px;
}
.orders-page-title i { color: #326916; }
.orders-count-badge {
    font-size: 0.7rem;
    font-weight: 700;
    background: #326916;
    color: #fff;
    padding: 2px 8px;
    border-radius: 10px;
}

/* ── Order card ─────────────────────────────────────── */
.order-card {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    margin-bottom: 16px;
    overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
}

/* Card top bar */
.order-topbar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 16px;
    background: #f8f9fa;
    border-bottom: 1px solid #f0f0f0;
    flex-wrap: wrap;
    gap: 6px;
}
.order-id {
    font-size: 0.72rem;
    font-weight: 700;
    color: #6b7280;
    letter-spacing: 0.04em;
    text-transform: uppercase;
}
.order-date {
    font-size: 0.72rem;
    color: #9ca3af;
    display: flex;
    align-items: center;
    gap: 4px;
}
.order-payment-badge {
    font-size: 0.7rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
    text-transform: capitalize;
    letter-spacing: 0.02em;
}
.badge-paid    { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
.badge-pending { background: #fffbeb; color: #a16207; border: 1px solid #fde68a; }

/* Card body */
.order-body {
    display: flex;
    gap: 16px;
    padding: 16px;
    align-items: flex-start;
}
.order-img-wrap {
    flex-shrink: 0;
    width: 100px;
    height: 100px;
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid #e5e7eb;
    background: #f9fafb;
}
.order-img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}
.order-details { flex: 1; min-width: 0; overflow: hidden; }
.order-location {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 0.72rem;
    color: #9ca3af;
    margin-bottom: 4px;
}
.order-location i { font-size: 0.75rem; color: #326916; }
.order-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #111827;
    margin-bottom: 4px;
    line-height: 1.3;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}
.order-desc {
    font-size: 0.75rem;
    color: #9ca3af;
    margin-bottom: 8px;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    word-break: break-word;
}
.order-price {
    font-size: 1.05rem;
    font-weight: 800;
    color: #326916;
}

/* Mobile: compact horizontal layout */
@media (max-width: 540px) {
    .orders-wrap { margin-top: 12px; padding: 0 8px; }
    .order-card { margin-bottom: 10px; border-radius: 10px; }
    .order-topbar { padding: 7px 10px; gap: 4px; }
    .order-id { font-size: 0.65rem; }
    .order-date { font-size: 0.65rem; }
    .order-payment-badge { font-size: 0.65rem; padding: 2px 7px; }
    .order-body { gap: 10px; padding: 10px; }
    .order-img-wrap { width: 72px; height: 72px; border-radius: 8px; flex-shrink: 0; }
    .order-details { flex: 1; min-width: 0; }
    .order-location { font-size: 0.65rem; margin-bottom: 2px; }
    .order-title { font-size: 0.8rem; margin-bottom: 2px; -webkit-line-clamp: 2; }
    .order-desc { display: none; }
    .order-price { font-size: 0.88rem; }
    .order-shipping { padding: 8px 10px 10px; gap: 8px; }
    .shipping-label { font-size: 0.62rem; }
    .shipping-company { font-size: 0.72rem; }
    .shipping-company img { height: 16px; max-width: 40px; }
    .shipping-status-pill { font-size: 0.65rem; padding: 3px 7px; }
    .shipping-info-row { gap: 12px; }
    .order-actions { gap: 4px; }
    .btn-report, .btn-details, .btn-confirm { padding: 6px 2px; font-size: 0.6rem; gap: 3px; }
    .btn-text-full { display: none; }
    .btn-text-short { display: inline; }
}

/* ── Shipping section ───────────────────────────────── */
.order-shipping {
    border-top: 1px solid #f0f0f0;
    padding: 12px 16px 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.shipping-info-row {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}
.shipping-block { display: flex; flex-direction: column; gap: 4px; }
.shipping-label {
    font-size: 0.68rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #9ca3af;
}
.shipping-company {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8rem;
    font-weight: 700;
    color: #374151;
}
.shipping-company img { height: 22px; max-width: 52px; object-fit: contain; }

.shipping-status-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.72rem;
    font-weight: 600;
    padding: 4px 10px;
    border-radius: 20px;
    text-transform: capitalize;
}
.status-delivered { background: #dcfce7; color: #15803d; }
.status-shipped   { background: #dbeafe; color: #1d4ed8; }
.status-pending   { background: #fee2e2; color: #dc2626; }

.shipping-eta {
    font-size: 0.72rem;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 5px;
}
.shipping-eta i { color: #326916; }

/* ── Action buttons ─────────────────────────────────── */
.order-actions {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 6px;
}
.btn-report, .btn-details, .btn-confirm {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
    padding: 7px 6px;
    border-radius: 8px;
    font-size: 0.7rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.15s;
    cursor: pointer;
    white-space: nowrap;
    width: 100%;
}
.btn-report {
    border: 1.5px solid #e5e7eb;
    color: #6b7280;
    background: #fff;
}
.btn-report:hover { border-color: #fca5a5; color: #dc2626; background: #fff5f5; text-decoration: none; }
.btn-details {
    border: 1.5px solid #326916;
    color: #326916;
    background: #fff;
}
.btn-details:hover { background: #f0fdf4; text-decoration: none; color: #326916; }
.btn-confirm {
    border: none;
    background: #326916;
    color: #fff;
}
.btn-confirm:hover:not(:disabled) { background: #2a5812; }
.btn-confirm:disabled, .btn-confirm-done {
    background: #d1fae5;
    color: #065f46;
    cursor: not-allowed;
    border: none;
}

@media (min-width: 480px) {
    .btn-text-full { display: inline; }
    .btn-text-short { display: none; }
}

/* ── Pending payment card ───────────────────────────── */
.order-card-pending {
    border-color: #fde68a;
    background: #fffbeb;
}
.order-card-pending .order-topbar {
    background: #fffbeb;
    border-bottom-color: #fde68a;
}
.pending-notice {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #fffbeb;
    border-top: 1px solid #fde68a;
    padding: 12px 16px;
    font-size: 0.8rem;
    color: #92400e;
}
.pending-notice i { font-size: 1rem; color: #d97706; flex-shrink: 0; }
.btn-complete-payment {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    width: 100%;
    padding: 10px;
    background: #d97706;
    color: #fff;
    border: none;
    border-radius: 8px;
    font-size: 0.8rem;
    font-weight: 700;
    text-decoration: none;
    transition: background 0.15s;
    margin: 12px 16px;
    width: calc(100% - 32px);
}
.btn-complete-payment:hover { background: #b45309; color: #fff; text-decoration: none; }
@media (max-width: 540px) {
    .pending-notice { font-size: 0.72rem; padding: 9px 10px; }
    .btn-complete-payment { font-size: 0.75rem; padding: 9px; margin: 10px 10px; width: calc(100% - 20px); }
}

/* ── Empty state ─────────────────────────────────────── */
.orders-empty {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #e5e7eb;
    padding: 60px 20px;
    text-align: center;
    color: #9ca3af;
}
.orders-empty i { font-size: 3rem; color: #d1d5db; display: block; margin-bottom: 12px; }
</style>

<div class="orders-wrap">

    {{-- Page header --}}
    <div class="orders-page-header">
        <div class="orders-page-title">
            <i class="bi bi-bag-check-fill"></i>
            My Orders
            @if($buyAds->total() > 0)
                <span class="orders-count-badge">{{ $buyAds->total() }}</span>
            @endif
        </div>
    </div>

    @include('public.components.flash-message')

    {{-- Pending / abandoned payments --}}
    @foreach($pendingOrders as $pending)
    @php
        $adLink = isset($pending->advert->state_slug, $pending->advert->title_slug, $pending->advert->ad_id)
                    ? url($pending->advert->state_slug . '/' . $pending->advert->title_slug . '/' . $pending->advert->ad_id)
                    : '#';
    @endphp
    <div class="order-card order-card-pending">

        <div class="order-topbar">
            <span class="order-id"><i class="bi bi-receipt"></i> Order #{{ $pending->order_code ?? $pending->id }}</span>
            <span class="order-date">
                <i class="bi bi-calendar3"></i>
                {{ isset($pending->created_at) ? date('d M Y', strtotime($pending->created_at)) : 'N/A' }}
            </span>
            <span class="order-payment-badge badge-pending">⏳ Pending Payment</span>
        </div>

        <div class="order-body">
            <a href="{{ $adLink }}" class="order-img-wrap">
                @if($pending->advert && $pending->advert->hasMedia('images'))
                    <img src="{{ $pending->advert->getFirstMediaUrl('images', 'thumbnail') }}"
                         alt="{{ $pending->advert->ad_title ?? 'Order' }}"
                         onerror="this.src='{{ asset('frontend/images/default.png') }}'">
                @else
                    <img src="{{ asset('frontend/images/default.png') }}" alt="Order image">
                @endif
            </a>
            <div class="order-details">
                <div class="order-location">
                    <i class="bi bi-geo-alt-fill"></i>
                    {{ $pending->advert->state ?? 'Location not specified' }}
                </div>
                <a href="{{ $adLink }}" style="text-decoration:none;">
                    <div class="order-title">{{ Str::limit($pending->advert->ad_title ?? 'No title available', 60) }}</div>
                </a>
                <div class="order-desc">{{ strip_tags($pending->advert->description ?? '') }}</div>
                <div class="order-price">
                    Br {{ number_format($pending->amount_paid ?? 0, 0, '.', ',') }}
                </div>
            </div>
        </div>

        <div class="pending-notice">
            <i class="bi bi-exclamation-circle-fill"></i>
            Your payment was not completed. Complete it now to secure this item before it sells out.
        </div>

        <a href="{{ route('user.resume.payment', $pending->id) }}" class="btn-complete-payment">
            <i class="bi bi-credit-card"></i> Complete Payment
        </a>

    </div>
    @endforeach

    @if (!$buyAds->isEmpty())
        @foreach ($buyAds as $row)
        @php
            $isPaid      = ($row->payment_status ?? '') === 'paid';
            $shipStatus  = $row->shipping_status ?? 'pending';
            $isDelivered = ($row->buyer_status ?? '') === 'delivered';
            $adLink      = isset($row->advert->state_slug, $row->advert->title_slug, $row->advert->ad_id)
                            ? url($row->advert->state_slug . '/' . $row->advert->title_slug . '/' . $row->advert->ad_id)
                            : '#';
        @endphp

        <div class="order-card">

            {{-- Top bar: order ref, date, payment status --}}
            <div class="order-topbar">
                <span class="order-id"><i class="bi bi-receipt"></i> Order #{{ $row->order_code ?? $row->id }}</span>
                <span class="order-date">
                    <i class="bi bi-calendar3"></i>
                    {{ isset($row->created_at) ? date('d M Y', strtotime($row->created_at)) : 'N/A' }}
                </span>
                <span class="order-payment-badge {{ $isPaid ? 'badge-paid' : 'badge-pending' }}">
                    {{ $isPaid ? '✓ Paid' : '⏳ Pending' }}
                </span>
            </div>

            {{-- Body: image + details --}}
            <div class="order-body">
                <a href="{{ $adLink }}" class="order-img-wrap">
                    @if($row->advert && $row->advert->hasMedia('images'))
                        <img src="{{ $row->advert->getFirstMediaUrl('images', 'thumbnail') }}"
                             alt="{{ $row->advert->ad_title ?? 'Order' }}"
                             onerror="this.src='{{ asset('frontend/images/default.png') }}'">
                    @else
                        <img src="{{ asset('frontend/images/default.png') }}" alt="Order image">
                    @endif
                </a>

                <div class="order-details">
                    <div class="order-location">
                        <i class="bi bi-geo-alt-fill"></i>
                        {{ $row->advert->state ?? 'Location not specified' }}
                    </div>
                    <a href="{{ $adLink }}" style="text-decoration:none;">
                        <div class="order-title">{{ Str::limit($row->advert->ad_title ?? 'No title available', 60) }}</div>
                    </a>
                    <div class="order-desc">
                        {{ strip_tags($row->advert->description ?? 'No description available') }}
                    </div>
                    <div class="order-price">
                        Br {{ isset($row->amount_paid) ? number_format($row->amount_paid, 0, '.', ',') : '0' }}
                    </div>
                </div>
            </div>

            {{-- Order status (Buy Direct escrow) --}}
            @if($isPaid)
            @php $escrow = \App\Support\BuyDirectStatus::for($row, 'buyer'); @endphp
            <div class="order-shipping">
                <div class="shipping-info-row">
                    <div class="shipping-block">
                        <div class="shipping-label">Order Status</div>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $escrow['class'] }}">
                            <i class="bi {{ $escrow['icon'] }}"></i> {{ $escrow['label'] }}
                        </span>
                    </div>
                    <div class="shipping-block" style="flex:2 1 14rem;">
                        <div class="shipping-eta" style="white-space:normal;">{{ \Illuminate\Support\Str::limit($escrow['banner']['text'], 160) }}</div>
                    </div>
                </div>

                <div class="order-actions">
                    <a href="/user/order-details/{{ $row->id }}" class="btn-details">
                        <i class="bi bi-receipt"></i> Order Details
                    </a>
                    @if($escrow['actions']['can_confirm_receipt'])
                        <a href="/user/order-details/{{ $row->id }}" class="btn-confirm">
                            <i class="bi bi-check-circle"></i> Confirm Received
                        </a>
                    @endif
                </div>
            </div>
            @endif

        </div>
        @endforeach

    @else
        @if($pendingOrders->isEmpty())
        <div class="orders-empty">
            <i class="bi bi-bag-x"></i>
            <div style="font-size:1rem;font-weight:600;color:#374151;margin-bottom:6px;">No orders yet</div>
            <p style="font-size:0.85rem;">Items you purchase will appear here.</p>
            <a href="/" style="display:inline-block;margin-top:16px;padding:9px 20px;background:#326916;color:#fff;border-radius:8px;font-size:0.82rem;font-weight:600;text-decoration:none;">
                Browse Listings
            </a>
        </div>
        @endif
    @endif

</div>

{{-- Delivery confirmation modal --}}
<div id="successModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" style="max-width:90vw;">
        <div class="mt-3 text-center">
            <div style="width:56px;height:56px;background:#dcfce7;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 14px;">
                <i class="bi bi-check-circle-fill" style="font-size:1.6rem;color:#15803d;"></i>
            </div>
            <h3 style="font-size:1rem;font-weight:700;color:#111827;margin-bottom:6px;">Delivery Confirmed!</h3>
            <p style="font-size:0.82rem;color:#6b7280;margin-bottom:4px;">Thanks for confirming the delivery.</p>
            <p style="font-size:0.82rem;color:#6b7280;margin-bottom:20px;">We're glad your item arrived safely. Your transaction is now complete.</p>
            <button id="closeModal"
                    style="background:#326916;color:#fff;padding:9px 28px;border-radius:8px;border:none;font-weight:600;font-size:0.85rem;cursor:pointer;">
                Done
            </button>
        </div>
    </div>
</div>

@include('user.layouts.footer')
<script>
function confirmDelivery(button) {
    const orderId = button.getAttribute('data-order-id');

    if (confirm('Are you sure you want to confirm this delivery?')) {
        button.disabled = true;
        button.innerHTML = '<i class="bi bi-hourglass-split"></i> Processing…';

        fetch(`/user/confirm-delivery/${orderId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ status: 'delivered' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessModal();
                if (data.order && data.order.shipping_status === 'delivered') {
                    button.innerHTML = '<i class="bi bi-check-circle-fill"></i> Delivered';
                    button.classList.add('btn-confirm-done');
                    button.disabled = true;
                } else {
                    button.disabled = false;
                    button.innerHTML = '<i class="bi bi-check-circle"></i> Confirm Delivery';
                }
            } else {
                throw new Error(data.message || 'Failed to update delivery status');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred: ' + error.message);
            button.disabled = false;
            button.innerHTML = '<i class="bi bi-check-circle"></i> Confirm Delivery';
        });
    }
}

function showSuccessModal() {
    const modal = document.getElementById('successModal');
    modal.classList.remove('hidden');
    document.getElementById('closeModal').onclick = () => modal.classList.add('hidden');
    modal.onclick = (e) => { if (e.target === modal) modal.classList.add('hidden'); };
    setTimeout(() => modal.classList.add('hidden'), 10000);
}
</script>
