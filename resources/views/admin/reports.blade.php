@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">
    <div class="pagetitle">
        <div class="d-flex justify-content-between align-items-center">
            <h1>{{ $page_title ?? 'Complaints' }}</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">{{ $page_title ?? 'Complaints' }}</li>
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
                            <i class="bi bi-flag me-2"></i>Complaint Management
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
                                        <th scope="col">Ad Title</th>
                                        <th scope="col">Ad Owner</th>
                                        <th scope="col">Complainer</th>
                                        <th scope="col">Complaint</th>
                                        <th scope="col">Status</th>
                                        <th scope="col" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($adverts ?? [] as $row)
                                        <tr>
                                            <td>
                                                @if($row->ad_is_live && $row->adverts->state_slug && $row->adverts->title_slug && $row->adverts->ad_id)
                                                    <a href="{{ url($row->adverts->state_slug . '/' . $row->adverts->title_slug . '/' . $row->adverts->ad_id) }}"
                                                       target="_blank" rel="noopener"
                                                       class="fw-semibold text-decoration-none">
                                                        {{ $row->display_ad_title }}
                                                    </a>
                                                @else
                                                    <h6 class="mb-0 fw-semibold {{ $row->ad_is_live ? '' : 'text-muted fst-italic' }}">
                                                        {{ $row->display_ad_title }}
                                                    </h6>
                                                @endif
                                                <small class="text-muted">
                                                    @if($row->display_ad_number)
                                                        ID: {{ $row->display_ad_number }}
                                                    @endif
                                                    @unless($row->ad_is_live)
                                                        <span class="badge bg-secondary ms-1">Ad deleted</span>
                                                    @endunless
                                                </small>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-medium">{{ $row->display_seller_name }}</span>
                                                    <small class="text-muted">{{ $row->display_seller_phone ?? 'No phone' }}</small>
                                                    <small class="text-muted">{{ $row->display_seller_email ?? 'No email' }}</small>
                                                    @unless($row->ad_is_live)
                                                        <small class="text-muted fst-italic">Ad deleted — identity on record{{ $row->display_seller_id ? ' for law enforcement if requested' : '' }}</small>
                                                    @endunless
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-medium">{{ $row->user->name ?? 'Unknown User' }}</span>
                                                    <small class="text-muted">{{ $row->user->phone ?? 'No phone' }}</small>
                                                    <small class="text-muted">{{ $row->user->email ?? 'No email' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 250px;" title="{{ $row->message ?? 'No message' }}">
                                                    {{ $row->message ?? 'No complaint message' }}
                                                </div>
                                            </td>
                                            <td>
                                                @if(($row->status ?? 'pending') == "resolved")
                                                    <span class="badge bg-success text-white">
                                                        <i class="bi bi-check-circle me-1"></i> Resolved
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning text-white">
                                                        <i class="bi bi-exclamation-triangle me-1"></i> Pending
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex gap-2 justify-content-center">
                                                    @if(($row->status ?? 'pending') == "resolved")
                                                        <a href="/admin/report-status/{{ $row->id ?? '#' }}/pending"
                                                           class="btn btn-sm btn-outline-warning"
                                                           title="Mark as unresolved">
                                                            <i class="bi bi-arrow-counterclockwise"></i>
                                                        </a>
                                                    @else
                                                        <a href="/admin/report-status/{{ $row->id ?? '#' }}/resolved"
                                                           class="btn btn-sm btn-outline-success"
                                                           title="Mark as resolved">
                                                            <i class="bi bi-check-lg"></i>
                                                        </a>
                                                    @endif
                                                    <button class="btn btn-sm btn-outline-danger"
                                                            onclick="confirmDelete('{{ $row->id ?? '#' }}')"
                                                            title="Delete complaint">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center">
                                                    <div class="bg-light rounded-circle p-4 mb-3">
                                                        <i class="bi bi-flag display-4 text-muted"></i>
                                                    </div>
                                                    <h5 class="text-muted mb-2">No Complaints Found</h5>
                                                    <p class="text-muted">There are currently no complaints to display.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if(($adverts ?? null)?->hasPages())
                            <div class="row align-items-center mt-4 pt-3 border-top">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center text-muted">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <span>Showing <strong>{{ $adverts->firstItem() ?? 0 }}</strong> to <strong>{{ $adverts->lastItem() ?? 0 }}</strong> of <strong>{{ $adverts->total() ?? 0 }}</strong> results</span>
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
function confirmDelete(id) {
    if(confirm('Are you sure you want to delete this complaint?')) {
        if(id && id !== '#') {
            // Create form for DELETE request with CSRF token
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/delete-complaint/' + id;

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);

            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            form.appendChild(methodField);

            document.body.appendChild(form);
            form.submit();
        }
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
    }
    .badge {
        padding: 0.5em 0.75em;
        font-weight: 500;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
    }
    .text-truncate {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>

@include('admin.layouts.footer')
