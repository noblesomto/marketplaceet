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
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title text-white mb-0">
                            <i class="bi bi-patch-check me-2"></i>Paid Boost Adverts
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
                                        <th scope="col" width="80px">Ad</th>
                                        <th scope="col">Details</th>
                                        <th scope="col">Owner</th>
                                        <th scope="col">Amount</th>
                                        <th scope="col">Boost Type</th>
                                        <th scope="col">Boost Status</th>
                                        <th scope="col">Trans ID</th>
                                        <th scope="col">Dates</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($adverts as $row)
                                        @php
                                            $expiry = $row->start_date
                                                ? \Carbon\Carbon::parse($row->start_date)->addDays($row->duration)
                                                : null;
                                            $daysRemaining = $expiry
                                                ? (int) ceil(\Carbon\Carbon::now()->diffInDays($expiry, false))
                                                : null;
                                        @endphp
                                        <tr>
                                            <td>
                                                <img src="{{ $row->advert && $row->advert->hasMedia('images')
                                                    ? $row->advert->getFirstMediaUrl('images', 'thumbnail')
                                                    : asset('frontend/images/default.png') }}"
                                                     class="rounded"
                                                     width="60"
                                                     height="60"
                                                     alt="{{ $row->advert->ad_title }}"
                                                     style="object-fit: cover;">
                                            </td>
                                            <td>
                                                <h6 class="mb-0 fw-semibold">{{ $row->advert->ad_title }}</h6>
                                                <small class="text-muted">ID: {{ $row->advert->id }}</small>
                                            </td>
                                            <td>
                                                <a href="/admin/view-user/{{ $row->user->user_id }}" class="text-primary fw-medium">
                                                    {{ $row->user->name }}
                                                </a>
                                            </td>
                                            <td class="fw-bold text-success">
                                                Br {{ number_format($row->amount, 2) }}
                                            </td>
                                            <td>
                                                <span class="badge bg-info text-white text-capitalize">
                                                    <i class="bi bi-lightning-charge me-1"></i> {{ $row->boost_type }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($row->boost_status === 'active')
                                                    @if($daysRemaining !== null && $daysRemaining >= 0)
                                                        <span class="badge bg-success">
                                                            <i class="bi bi-clock me-1"></i> Active – {{ $daysRemaining }}d left
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning text-dark">
                                                            <i class="bi bi-exclamation-circle me-1"></i> Active (expired)
                                                        </span>
                                                    @endif
                                                @elseif($row->boost_status === 'completed')
                                                    <span class="badge bg-secondary">
                                                        <i class="bi bi-check2-all me-1"></i> Completed
                                                    </span>
                                                @else
                                                    <span class="badge bg-light text-dark border">{{ $row->boost_status }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted font-monospace">{{ $row->trans_id }}</small>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    <div class="fw-medium">
                                                        Started: {{ $row->start_date ? $row->start_date->format('M j, Y') : '—' }}
                                                    </div>
                                                    <div class="text-muted">
                                                        Expires: {{ $expiry ? $expiry->format('M j, Y') : '—' }}
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($adverts->hasPages())
                            <div class="row align-items-center mt-4 pt-3 border-top">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center text-muted">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <span>Showing <strong>{{ $adverts->firstItem() }}</strong> to <strong>{{ $adverts->lastItem() }}</strong> of <strong>{{ $adverts->total() }}</strong> results</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-end">
                                        {{ $adverts->links('pagination::bootstrap-4') }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<style>
    .card-header { border-radius: 0.5rem 0.5rem 0 0 !important; }
    .table th { font-weight: 600; color: #495057; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; white-space: nowrap; }
    .badge { padding: 0.5em 0.75em; font-weight: 500; }
    .table-hover tbody tr:hover { background-color: rgba(13, 110, 253, 0.05); }
</style>

@include('admin.layouts.footer')
