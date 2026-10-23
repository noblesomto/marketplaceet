@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">
    <div class="pagetitle">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h1>{{ $page_title }}</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">{{ $page_title }}</li>
                </ol>
            </nav>
        </div>
    </div>

    <section class="section">
        @include('admin.buy-direct._status')

        <div class="card shadow-sm border-0">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h5 class="card-title text-white mb-0"><i class="bi bi-exclamation-triangle me-2"></i>Reported problems</h5>
                <div class="btn-group btn-group-sm">
                    <a href="?status=open" class="btn {{ $status === 'open' ? 'btn-light' : 'btn-outline-light' }}">Open</a>
                    <a href="?status=resolved" class="btn {{ $status === 'resolved' ? 'btn-light' : 'btn-outline-light' }}">Resolved</a>
                </div>
            </div>
            <div class="card-body pt-3">
                <p class="text-muted small mb-3">While a dispute is open the payment is frozen (no automatic release or payout). Contact both parties, then refund the buyer or release the payment to the seller.</p>

                @forelse($payments as $row)
                    @php $seller = optional($row->advert)->owner; @endphp
                    <div class="border rounded-3 p-3 mb-3">
                        <div class="d-flex justify-content-between flex-wrap gap-2 mb-2">
                            <div>
                                <span class="fw-semibold">Order {{ $row->order_code ?? $row->id }}</span>
                                <span class="text-muted">· {{ optional($row->advert)->ad_title ?? 'Advert removed' }}</span>
                            </div>
                            <div class="fw-bold">Br {{ number_format((float) $row->amount_paid, 2) }}</div>
                        </div>
                        <div class="row g-3 small">
                            <div class="col-md-4">
                                <div class="text-muted">Buyer</div>
                                <div>{{ trim($row->first_name . ' ' . $row->last_name) ?: optional($row->user)->name }}</div>
                                <div><a href="tel:{{ $row->phone }}">{{ $row->phone }}</a> · {{ optional($row->user)->email }}</div>
                                <div class="text-muted">{{ $row->deliveryLocationText() }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted">Seller</div>
                                <div>{{ $seller->name ?? '—' }}</div>
                                <div><a href="tel:{{ $seller->phone ?? '' }}">{{ $seller->phone ?? '—' }}</a> · {{ $seller->email ?? '' }}</div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-muted">Timeline</div>
                                <div>Paid {{ optional($row->paid_at)->format('d M Y') ?? '—' }}</div>
                                <div>Shipped {{ optional($row->shipped_at)->format('d M Y') ?? '—' }}</div>
                                <div>Reported {{ optional($row->dispute_opened_at)->format('d M Y H:i') }}</div>
                            </div>
                        </div>
                        <div class="bg-light rounded p-2 mt-3 small" style="white-space:pre-line;">{{ $row->dispute_reason }}</div>

                        @if($status === 'open')
                            <form method="POST" action="/admin/buy-direct/disputes/{{ $row->id }}/resolve" class="mt-3">
                                @csrf
                                <label class="form-label small fw-semibold" for="note-{{ $row->id }}">Resolution note (shown to buyer and seller)</label>
                                <textarea name="note" id="note-{{ $row->id }}" class="form-control form-control-sm mb-2" rows="2" maxlength="1000" required></textarea>
                                <div class="d-flex gap-2 flex-wrap">
                                    <button name="resolution" value="refund" class="btn btn-sm btn-outline-danger" onclick="return confirm('Cancel this order and refund the buyer in full?');">
                                        <i class="bi bi-arrow-counterclockwise"></i> Refund buyer
                                    </button>
                                    <button name="resolution" value="release" class="btn btn-sm btn-outline-success" onclick="return confirm('Release the payment to the seller?');">
                                        <i class="bi bi-cash-coin"></i> Release to seller
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="small mt-2">
                                <span class="badge {{ $row->dispute_resolution === 'refund' ? 'bg-danger' : 'bg-success' }}">{{ $row->dispute_resolution === 'refund' ? 'Refunded buyer' : 'Released to seller' }}</span>
                                <span class="text-muted">{{ optional($row->dispute_resolved_at)->format('d M Y H:i') }}</span>
                                @if($row->dispute_admin_note)<div class="mt-1">{{ $row->dispute_admin_note }}</div>@endif
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center text-muted py-5">No {{ $status }} disputes.</div>
                @endforelse

                <div class="d-flex justify-content-end">{{ $payments->links('pagination::bootstrap-4') }}</div>
            </div>
        </div>
    </section>
</main>

@include('admin.layouts.footer')
