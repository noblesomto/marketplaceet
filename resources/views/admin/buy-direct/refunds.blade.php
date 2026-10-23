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
                <h5 class="card-title text-white mb-0"><i class="bi bi-arrow-counterclockwise me-2"></i>Canceled orders</h5>
                <div class="btn-group btn-group-sm">
                    <a href="?filter=attention" class="btn {{ $filter === 'attention' ? 'btn-light' : 'btn-outline-light' }}">Needs attention</a>
                    <a href="?filter=refunded" class="btn {{ $filter === 'refunded' ? 'btn-light' : 'btn-outline-light' }}">Refunded</a>
                    <a href="?filter=all" class="btn {{ $filter === 'all' ? 'btn-light' : 'btn-outline-light' }}">All</a>
                </div>
            </div>
            <div class="card-body pt-3">
                <p class="text-muted small mb-3">Refunds run automatically through Flutterwave when an order is canceled. If one fails, retry it here, or refund the buyer another way and mark it refunded.</p>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Order</th>
                                <th>Buyer</th>
                                <th>Amount</th>
                                <th>Canceled</th>
                                <th>Refund</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($payments as $row)
                            <tr>
                                <td>
                                    <div class="fw-semibold">{{ $row->order_code ?? $row->id }}</div>
                                    <small class="text-muted">{{ \Illuminate\Support\Str::limit(optional($row->advert)->ad_title ?? 'Advert removed', 40) }}</small>
                                </td>
                                <td>
                                    <div>{{ trim($row->first_name . ' ' . $row->last_name) ?: optional($row->user)->name }}</div>
                                    <small class="text-muted">{{ $row->phone }} · {{ optional($row->user)->email }}</small>
                                </td>
                                <td class="fw-bold">Br {{ number_format((float) ($row->refund_amount ?? $row->amount_paid), 2) }}</td>
                                <td>
                                    <span class="text-capitalize">{{ $row->canceled_by ?? '—' }}</span>
                                    <div class="small text-muted">{{ optional($row->canceled_at)->format('d M Y H:i') }}</div>
                                    @if($row->cancel_reason)<div class="small text-muted">{{ \Illuminate\Support\Str::limit($row->cancel_reason, 80) }}</div>@endif
                                </td>
                                <td>
                                    @php
                                        $map = ['none' => ['bg-warning text-dark', 'Not started'], 'processing' => ['bg-info text-dark', 'Processing'], 'refunded' => ['bg-success', 'Refunded'], 'failed' => ['bg-danger', 'Failed']];
                                        [$cls, $txt] = $map[$row->refund_status] ?? ['bg-secondary', $row->refund_status];
                                    @endphp
                                    <span class="badge rounded-pill {{ $cls }}">{{ $txt }}</span>
                                    @if($row->refund_status === 'failed' && $row->refund_error)
                                        <div class="small text-danger mt-1">{{ \Illuminate\Support\Str::limit($row->refund_error, 120) }}</div>
                                    @endif
                                    @if($row->refund_reference)
                                        <div class="small text-muted mt-1">Ref: {{ \Illuminate\Support\Str::limit($row->refund_reference, 40) }}</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if(in_array($row->refund_status, ['none', 'failed'], true))
                                        <div class="d-flex flex-column gap-2 align-items-center">
                                            <form method="POST" action="/admin/buy-direct/refunds/{{ $row->id }}/retry" onsubmit="return confirm('Refund Br {{ number_format((float) $row->amount_paid, 2) }} to the buyer via Flutterwave?');">
                                                @csrf
                                                <button class="btn btn-sm btn-outline-primary"><i class="bi bi-arrow-repeat"></i> {{ $row->refund_status === 'failed' ? 'Retry refund' : 'Refund now' }}</button>
                                            </form>
                                            <form method="POST" action="/admin/buy-direct/refunds/{{ $row->id }}/mark-refunded" onsubmit="const n = prompt('How was the buyer refunded? (e.g. bank transfer ref)'); if (!n) return false; this.note.value = n; return true;">
                                                @csrf
                                                <input type="hidden" name="note">
                                                <button class="btn btn-sm btn-outline-success"><i class="bi bi-check-lg"></i> Mark refunded</button>
                                            </form>
                                        </div>
                                    @elseif($row->refund_status === 'processing')
                                        <span class="text-muted small"><i class="bi bi-hourglass-split"></i> Waiting for Flutterwave</span>
                                    @else
                                        <span class="text-success small"><i class="bi bi-check-circle-fill"></i> {{ optional($row->refunded_at)->format('d M Y') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-5">No canceled orders here.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end">{{ $payments->links('pagination::bootstrap-4') }}</div>
            </div>
        </div>
    </section>
</main>

@include('admin.layouts.footer')
