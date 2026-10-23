@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">
    <div class="pagetitle">
        <div class="d-flex justify-content-between align-items-center">
            <h1>{{ $page_title ?? 'Settlements' }}</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">{{ $page_title ?? 'Settlements' }}</li>
                </ol>
            </nav>
        </div>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title text-white mb-0">
                            <i class="bi bi-cash-coin me-2"></i>{{ $page_title ?? 'Settlements' }} Management
                        </h5>
                    </div>

                    <div class="card-body">
                        @if(session('status'))
                            <div class="alert alert-{{ session('status')['type'] ?? 'info' }} alert-dismissible fade show">
                                <div class="d-flex align-items-center">
                                    @if((session('status')['type'] ?? 'info') === 'success')
                                        <i class="bi bi-check-circle-fill me-2"></i>
                                    @else
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    @endif
                                    <div>{{ session('status')['text'] ?? 'Operation completed' }}</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" width="80px">#</th>
                                        <th scope="col">Advert Details</th>
                                        <th scope="col">User</th>
                                        <th scope="col">Amount</th>
                                        <th scope="col">Bank Details</th>
                                        <th scope="col">Status</th>
                                        <th scope="col" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payments as $row)
                                        <tr>
                                            <td>
                                                <img src="{{ optional($row->advert)->firstImage ? $row->advert->firstImage->getUrl('thumbnail') : asset('frontend/images/default.png') }}"
                                                     class="rounded"
                                                     alt="{{ optional($row->advert)->ad_title ?? 'Ad Image' }}"
                                                     style="width: 60px; height: 50px; object-fit: cover;">
                                            </td>
                                            <td>
                                                <h6 class="mb-0 fw-semibold">{{ optional($row->advert)->ad_title ?? 'N/A' }}</h6>
                                                <small class="text-muted">ID: {{ optional($row->advert)->id ?? 'N/A' }}</small>
                                            </td>
                                            <td>
                                                @if(optional($row->advert)->owner)
                                                    <a href="/admin/view-user/{{ $row->advert->owner->user_id }}" class="text-primary text-decoration-none">
                                                        {{ $row->advert->owner->name }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">User not found</span>
                                                @endif
                                            </td>
                                            <td class="fw-bold text-success">
                                                Br {{ number_format((float) ($row->payout_amount ?? $row->amount), 2) }}
                                                <div class="small text-muted fw-normal">Order {{ $row->order_code ?? $row->id }}</div>
                                            </td>
                                            <td>
                                                @if(optional($row->advert)->owner)
                                                    <div class="small">
                                                        <div><strong>{{ $row->advert->owner->bank_name ?? 'N/A' }}</strong></div>
                                                        <div>{{ $row->advert->owner->account_name ?? 'N/A' }}</div>
                                                        <div class="text-muted">{{ $row->advert->owner->account_number ?? 'N/A' }}</div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Bank details not available</span>
                                                @endif
                                            </td>
                                            @php
                                                $ready = $row->buyer_status === 'delivered' && in_array($row->payout_status, ['none', 'failed'], true) && $row->dispute_status !== 'open';
                                                [$badgeClass, $badgeText] = match (true) {
                                                    $row->dispute_status === 'open'      => ['bg-danger', 'Disputed'],
                                                    $row->payout_status === 'failed'     => ['bg-danger', 'Payout failed'],
                                                    $row->payout_status === 'processing' => ['bg-info text-dark', 'Payout processing'],
                                                    $ready                               => ['bg-success', 'Ready to pay'],
                                                    $row->seller_status === 'shipped'    => ['bg-primary', 'Shipped · awaiting buyer'],
                                                    default                              => ['bg-warning text-dark', 'Awaiting shipment'],
                                                };
                                            @endphp
                                            <td>
                                                <span class="badge rounded-pill {{ $badgeClass }}">{{ $badgeText }}</span>
                                                @if($row->seller_status === 'shipped' && $row->buyer_status !== 'delivered' && $row->auto_release_at)
                                                    <div class="small text-muted mt-1">Auto-release {{ $row->auto_release_at->format('d M Y H:i') }}</div>
                                                @elseif($row->seller_status === 'pending' && $row->ship_by)
                                                    <div class="small text-muted mt-1">Ship by {{ $row->ship_by->format('d M Y H:i') }}</div>
                                                @endif
                                                @if($row->payout_status === 'failed' && $row->payout_error)
                                                    <div class="small text-danger mt-1">{{ \Illuminate\Support\Str::limit($row->payout_error, 120) }}</div>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($ready)
                                                    <div class="d-flex flex-column gap-2 align-items-center">
                                                        <form method="POST" action="/admin/payout/{{ $row->id }}" onsubmit="return confirm('Send Br {{ number_format((float) $row->amount, 2) }} to the seller via Flutterwave?');">
                                                            @csrf
                                                            <button class="btn btn-sm btn-outline-primary"><i class="bi bi-send"></i> {{ $row->payout_status === 'failed' ? 'Retry payout' : 'Pay via Flutterwave' }}</button>
                                                        </form>
                                                        <form method="POST" action="/admin/confirm-settlement/{{ $row->id }}" onsubmit="const n = prompt('How was the seller paid? (e.g. bank transfer ref)'); if (!n) return false; this.note.value = n; return true;">
                                                            @csrf
                                                            <input type="hidden" name="note">
                                                            <button class="btn btn-sm btn-outline-success"><i class="bi bi-check-lg"></i> Mark paid manually</button>
                                                        </form>
                                                    </div>
                                                @elseif($row->dispute_status === 'open')
                                                    <a href="/admin/buy-direct/disputes" class="btn btn-sm btn-outline-danger"><i class="bi bi-exclamation-triangle"></i> Resolve dispute</a>
                                                @elseif($row->payout_status === 'processing')
                                                    <span class="text-muted small"><i class="bi bi-hourglass-split"></i> Waiting for Flutterwave</span>
                                                @else
                                                    <span class="text-muted small"><i class="bi bi-lock"></i> Held in escrow</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center">
                                                    <div class="bg-light rounded-circle p-4 mb-3">
                                                        <i class="bi bi-wallet2 display-4 text-muted"></i>
                                                    </div>
                                                    <h5 class="text-muted mb-2">No Settlement Records Found</h5>
                                                    <p class="text-muted">There are currently no pending payment settlements.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($payments->hasPages() && $payments->count() > 0)
                            <div class="row align-items-center mt-4 pt-3 border-top">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center text-muted">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <span>Showing <strong>{{ $payments->firstItem() ?? 0 }}</strong> to <strong>{{ $payments->lastItem() ?? 0 }}</strong> of <strong>{{ $payments->total() ?? 0 }}</strong> results</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-end">
                                        {{ $payments->links('pagination::bootstrap-4') }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</main><!-- End #main -->

<style>
    .card-header {
        border-radius: 0.5rem 0.5rem 0 0 !important;
    }
    .table th {
        font-weight: 600;
        color: #495057;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }
    .badge {
        padding: 0.5em 0.75em;
        font-weight: 500;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
    }
</style>

@include('admin.layouts.footer')
