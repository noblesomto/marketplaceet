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
                            <i class="bi bi-exclamation-octagon me-2"></i>Unmatched Payments
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

                        <p class="text-muted">
                            Flutterwave confirmed these payments as successful, but they don't match any boost or order record
                            in the database — usually because the paying client skipped our normal checkout flow. Pick the
                            advert the customer meant to boost and complete it below.
                        </p>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Reference</th>
                                        <th scope="col">Amount</th>
                                        <th scope="col">Customer</th>
                                        <th scope="col">Paid At</th>
                                        <th scope="col">Complete</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payments as $payment)
                                        @php $adverts = $advertsByPayment[$payment->id]; @endphp
                                        <tr>
                                            <td><code>{{ $payment->reference }}</code></td>
                                            <td class="fw-bold text-success">Br {{ number_format($payment->amount, 2) }}</td>
                                            <td>{{ $payment->customer_email }}</td>
                                            <td>{{ $payment->paid_at ? $payment->paid_at->format('M j, Y g:ia') : '—' }}</td>
                                            <td>
                                                @if($adverts->isEmpty())
                                                    <span class="badge bg-light text-muted border">
                                                        No adverts found for this email — verify manually
                                                    </span>
                                                @else
                                                    <form method="POST" action="{{ route('admin.boost.unmatched-payments.complete', $payment->id) }}"
                                                          onsubmit="return confirm('Activate a boost on the selected advert for ' + {!! \Illuminate\Support\Js::from($payment->customer_email) !!} + '?');"
                                                          class="d-flex flex-wrap gap-2 align-items-center">
                                                        @csrf
                                                        <select name="advert_id" class="form-select form-select-sm" style="width:auto" required>
                                                            @foreach($adverts as $advert)
                                                                <option value="{{ $advert->id }}">{{ $advert->ad_title }} (#{{ $advert->id }})</option>
                                                            @endforeach
                                                        </select>
                                                        <select name="boost_type_id" class="form-select form-select-sm" style="width:auto" required>
                                                            @foreach($boostTypes as $boostType)
                                                                <option value="{{ $boostType->id }}">{{ $boostType->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <select name="duration_id" class="form-select form-select-sm" style="width:auto" required>
                                                            @foreach($boostDurations as $duration)
                                                                <option value="{{ $duration->id }}">{{ $duration->label }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button type="submit" class="btn btn-sm btn-outline-success d-flex align-items-center">
                                                            <i class="bi bi-check2-circle me-1"></i>Complete
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">No unmatched payments right now.</td>
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
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
    }
</style>

@include('admin.layouts.footer')
