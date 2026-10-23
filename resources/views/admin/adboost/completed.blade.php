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
                            <i class="bi bi-megaphone me-2"></i>Boosted Advertisements
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
                                        <th scope="col">Status</th>
                                        <th scope="col">Boost Type</th>
                                        <th scope="col">Dates</th>
                                        <th scope="col" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($adverts as $row)
                                        @php
                                            $expiry = \Carbon\Carbon::parse($row->start_date)->addDays($row->duration);
                                            $now = \Carbon\Carbon::now();

                                            if ($now->lessThanOrEqualTo($expiry)) {
                                                $daysRemaining = (int) ceil($now->diffInDays($expiry, false));
                                            } else {
                                                $daysRemaining = -1; // expired
                                            }
                                        @endphp
                                        <tr>
                                            <td>
                                                <a href="{{ url($row->advert->state_slug . '/' . $row->advert->title_slug . '/' . $row->advert->ad_id) }}" target="_blank">
                                                    <img src="{{ $row->advert && $row->advert->hasMedia('images')
                                                        ? $row->advert->getFirstMediaUrl('images', 'thumbnail')
                                                        : asset('frontend/images/default.png') }}"
                                                         class="rounded"
                                                         width="60"
                                                         height="60"
                                                         alt="{{ $row->advert->ad_title }}"
                                                         style="object-fit: cover;">
                                                </a>
                                            </td>
                                            <td>
                                                <a href="{{ url($row->advert->state_slug . '/' . $row->advert->title_slug . '/' . $row->advert->ad_id) }}" target="_blank" class="text-decoration-none">
                                                    <h6 class="mb-0 fw-semibold">{{ $row->advert->ad_title }}</h6>
                                                </a>
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
                                                @if($daysRemaining >= 0)
                                                    <span class="badge bg-success text-white">
                                                        <i class="bi bi-clock me-1"></i> {{ $daysRemaining }} day{{ $daysRemaining != 1 ? 's' : '' }} left
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger text-white">
                                                        <i class="bi bi-x-circle me-1"></i> Expired
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge bg-info text-white text-capitalize">
                                                    <i class="bi bi-lightning-charge me-1"></i> {{ $row->boost_type }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    <div class="fw-medium">
                                                        Started:
                                                        {{ !empty($row->start_date) ? date('M j, Y', strtotime($row->start_date)) : 'Not set' }}
                                                    </div>
                                                    <div class="text-muted">
                                                        Expires:
                                                        {{ !empty($expiry) ? date('M j, Y', strtotime($expiry)) : 'Not set' }}
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @if($row->payment_status == "pending")
                                                   
                                                    <button class="btn btn-sm btn-outline-success"
                                                            onclick="return confirmAction('activate', '{{ $row->advert->ad_title }}', '{{ $row->id }}')">
                                                        <i class="bi bi-play-fill me-1"></i> Activate
                                                    </button>
                                                @endif
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
</main><!-- End #main -->
<script>
function confirmAction(action, adTitle, id) {
    const message = action === 'activate'
        ? `Are you sure you want to Confirm Ad boost for  "${adTitle}"?`
        : `Are you sure you want to resume boosting "${adTitle}"?`;

    if(confirm(message)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/boost/payment-status/${id}/activate`;

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';
        form.appendChild(csrfToken);

        document.body.appendChild(form);
        form.submit();
    }
    return false;
}
</script>
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
    .img-responsive {
        max-width: 100%;
        height: auto;
    }
</style>

@include('admin.layouts.footer')
