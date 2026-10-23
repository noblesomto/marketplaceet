@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">
    <div class="pagetitle">
        <div class="d-flex justify-content-between align-items-center">
            <h1>{{ $page_title }}</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">{{ $page_title }}</li>
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
                            <i class="bi bi-cash-stack me-2"></i>Payment Settlements
                        </h5>
                    </div>

                    <div class="card-body">
                        @if(session('status'))
                            <div class="alert alert-{{ session('status')['type'] }} alert-dismissible fade show">
                                <div class="d-flex align-items-center">
                                    @if(session('status')['type'] === 'success')
                                        <i class="bi bi-check-circle-fill me-2"></i>
                                    @else
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    @endif
                                    <div>{{ session('status')['text'] }}</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" width="70px">#</th>
                                        <th scope="col">Advert Details</th>
                                        <th scope="col">User</th>
                                        <th scope="col">Amount</th>
                                        <th scope="col">Bank Details</th>
                                        <th scope="col">Status</th>
                                        <th scope="col">Settled On</th>
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
                                                    <a href="/admin/view-user/{{ $row->advert->owner->user_id }}" class="text-primary fw-medium">
                                                        {{ $row->advert->owner->name }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">User not found</span>
                                                @endif
                                            </td>
                                            <td class="fw-bold text-success">
                                                Br {{ number_format(optional($row->advert)->price ?? 0, 2) }}
                                            </td>
                                            <td>
                                                @if(optional($row->advert)->owner)
                                                    <div class="small">
                                                        <div class="fw-medium">{{ $row->advert->owner->bank_name ?? 'N/A' }}</div>
                                                        <div>{{ $row->advert->owner->account_name ?? 'N/A' }}</div>
                                                        <div class="text-muted font-monospace">{{ $row->advert->owner->account_number ?? 'N/A' }}</div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">Bank details not available</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge rounded-pill text-capitalize
                                                    {{ $row->seller_settlement === 'yes' ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $row->seller_settlement === 'yes' ? 'Settled' : 'Pending' }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($row->settlement_date)
                                                    <div class="small">
                                                        <div class="fw-medium">{{ date('j M Y', strtotime($row->settlement_date)) }}</div>
                                                        <div class="text-muted">{{ date('g:i A', strtotime($row->settlement_date)) }}</div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
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
                                                    <h5 class="text-muted mb-2">No Settlement Records</h5>
                                                    <p class="text-muted">There are currently no payment settlements to display.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($payments->hasPages())
                            <div class="row align-items-center mt-4 pt-3 border-top">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center text-muted">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <span>Showing <strong>{{ $payments->firstItem() }}</strong> to <strong>{{ $payments->lastItem() }}</strong> of <strong>{{ $payments->total() }}</strong> results</span>
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
        white-space: nowrap;
    }
    .badge {
        padding: 0.5em 0.75em;
        font-weight: 500;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
    }
    .font-monospace {
        font-family: monospace;
    }
</style>

@include('admin.layouts.footer')
