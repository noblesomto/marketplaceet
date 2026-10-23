@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">
    <div class="pagetitle">
        <h1>{{ $page_title }}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">{{ $page_title }}</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-gradient-primary text-white">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="card-title mb-0 text-white">
                                    <i class="bi bi-file-earmark-check me-2"></i>{{ $page_title }}
                                </h5>
                                <p class="mb-0 opacity-75">Review and manage user document verification requests</p>
                            </div>
                            <div class="col-auto">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-light btn-sm" onclick="refreshPage()">
                                        <i class="bi bi-arrow-clockwise"></i> Refresh
                                    </button>
                                    <button type="button" class="btn btn-outline-light btn-sm" onclick="exportData()">
                                        <i class="bi bi-download"></i> Export
                                    </button>
                                    <button type="button" class="btn btn-outline-light btn-sm" data-bs-toggle="modal" data-bs-target="#bulkActionModal">
                                        <i class="bi bi-check-all"></i> Bulk Actions
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-4">
                        @if(session('status'))
                            <div class="alert alert-{{ session('status')['type'] }} alert-dismissible fade show border-0 shadow-sm" role="alert">
                                <div class="d-flex align-items-center">
                                    @if(session('status')['type'] === 'success')
                                        <i class="bi bi-check-circle-fill text-success me-3 fs-4"></i>
                                    @elseif(session('status')['type'] === 'danger')
                                        <i class="bi bi-exclamation-triangle-fill text-danger me-3 fs-4"></i>
                                    @else
                                        <i class="bi bi-info-circle-fill text-info me-3 fs-4"></i>
                                    @endif
                                    <div>
                                        <strong>{{ ucfirst(session('status')['type']) }}!</strong>
                                        {{ session('status')['text'] }}
                                    </div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Filter and Search Section -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-search text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0 ps-0" id="searchInput" placeholder="Search users...">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" id="accountTypeFilter">
                                    <option value="">All Types</option>
                                    <option value="admin">Admin</option>
                                    <option value="user">User</option>
                                    <option value="vendor">Vendor</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" id="verificationFilter">
                                    <option value="">All Status</option>
                                    <option value="verified">Verified</option>
                                    <option value="pending">Pending</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" id="documentTypeFilter">
                                    <option value="">All Documents</option>
                                    <option value="passport">Passport</option>
                                    <option value="license">License</option>
                                    <option value="national_id">National ID</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <select class="form-select" id="dateFilter">
                                    <option value="">All Time</option>
                                    <option value="today">Today</option>
                                    <option value="week">This Week</option>
                                    <option value="month">This Month</option>
                                </select>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-outline-secondary w-100" onclick="clearFilters()" title="Clear Filters">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Statistics Overview -->
                        <div class="row mb-4">
                            <div class="col-xl-3 col-md-6 mb-3">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="bg-primary bg-opacity-10 p-3 rounded-3">
                                                    <i class="bi bi-file-earmark-text-fill text-primary fs-4"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h4 class="mb-0 fw-bold">{{ $users->total() }}</h4>
                                                <p class="text-muted mb-0">Total Submissions</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6 mb-3">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="bg-success bg-opacity-10 p-3 rounded-3">
                                                    <i class="bi bi-check-circle-fill text-success fs-4"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h4 class="mb-0 fw-bold">{{ $users->where('verify_status', 'verified')->count() }}</h4>
                                                <p class="text-muted mb-0">Verified</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6 mb-3">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="bg-warning bg-opacity-10 p-3 rounded-3">
                                                    <i class="bi bi-clock-fill text-warning fs-4"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h4 class="mb-0 fw-bold">{{ $users->where('verify_status', 'pending')->count() }}</h4>
                                                <p class="text-muted mb-0">Pending Review</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6 mb-3">
                                <div class="card border-0 shadow-sm h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center">
                                            <div class="flex-shrink-0">
                                                <div class="bg-info bg-opacity-10 p-3 rounded-3">
                                                    <i class="bi bi-calendar-fill text-info fs-4"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h4 class="mb-0 fw-bold">{{ $users->where('created_at', '>=', now()->subDays(7))->count() }}</h4>
                                                <p class="text-muted mb-0">This Week</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Document Verification Table -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="verificationsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" class="fw-semibold border-0">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="selectAll">
                                            </div>
                                        </th>
                                        <th scope="col" class="fw-semibold border-0">
                                            <i class="bi bi-person-fill me-2"></i>User Details
                                        </th>
                                        <th scope="col" class="fw-semibold border-0">
                                            <i class="bi bi-shield-fill me-2"></i>Account Info
                                        </th>
                                        <th scope="col" class="fw-semibold border-0">
                                            <i class="bi bi-file-earmark-fill me-2"></i>Document Details
                                        </th>
                                        <th scope="col" class="fw-semibold border-0">
                                            <i class="bi bi-paperclip me-2"></i>Documents
                                        </th>
                                        <th scope="col" class="fw-semibold border-0 text-center">
                                            <i class="bi bi-patch-check-fill me-2"></i>Status
                                        </th>
                                        <th scope="col" class="fw-semibold border-0">
                                            <i class="bi bi-calendar-fill me-2"></i>Submitted
                                        </th>
                                        <th scope="col" class="fw-semibold border-0 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $row)
                                        <tr class="border-bottom verification-row" data-status="{{ $row->verify_status }}">
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input row-select" type="checkbox" value="{{ $row->user_id }}">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-container me-3">
                                                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                            <i class="bi bi-person-fill text-primary fs-5"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-semibold">{{ $row->user->name }}</h6>
                                                        <small class="text-muted">{{ $row->user->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="mb-2">
                                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1">
                                                        {{ ucfirst($row->user->acc_type) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <span class="badge {{ $row->user->verified === 'yes' ? 'bg-success bg-opacity-10 text-success border border-success border-opacity-25' : 'bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25' }} px-2 py-1">
                                                        <i class="bi bi-{{ $row->user->verified === 'yes' ? 'check-circle' : 'x-circle' }} me-1"></i>
                                                        {{ ucfirst($row->user->verified) }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="mb-2">
                                                    <strong class="text-dark">{{ ucfirst(str_replace('_', ' ', $row->document_type)) }}</strong>
                                                </div>
                                                <div class="text-muted">
                                                    <i class="bi bi-hash me-1"></i>{{ $row->document_number }}
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column gap-2">
                                                @if($row->hasDocumentFile())
                                                    <a href="{{ $row->document_file_url }}"
                                                       target="_blank"
                                                       class="btn btn-outline-primary btn-sm d-flex align-items-center"
                                                       data-bs-toggle="tooltip" title="View Identity Document">
                                                        <i class="bi bi-file-earmark-image me-1"></i>
                                                        <span class="d-none d-lg-inline">ID Document</span>
                                                    </a>
                                                @else
                                                    <span class="badge bg-light text-muted border d-flex align-items-center p-2">
                                                        <i class="bi bi-file-earmark-x me-1"></i>
                                                        <span class="d-none d-lg-inline">No ID Document</span>
                                                    </span>
                                                @endif

                                                 @if($row->hasProofAddress())
                                                    <a href="{{ $row->proof_address_url }}"
                                                       target="_blank"
                                                       class="btn btn-outline-secondary btn-sm d-flex align-items-center"
                                                       data-bs-toggle="tooltip" title="View Address Proof">
                                                        <i class="bi bi-house-door me-1"></i>
                                                        <span class="d-none d-lg-inline">Address Proof</span>
                                                    </a>
                                                @else
                                                    <span class="badge bg-light text-muted border d-flex align-items-center p-2">
                                                        <i class="bi bi-house-x me-1"></i>
                                                        <span class="d-none d-lg-inline">No Address Proof</span>
                                                    </span>
                                                @endif
                                            </div>
                                            </td>
                                            <td class="text-center">
                                                @if($row->verify_status === 'verified')
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">
                                                        <i class="bi bi-check-circle-fill me-1"></i>Verified
                                                    </span>
                                                @elseif($row->verify_status === 'pending')
                                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2">
                                                        <i class="bi bi-clock-fill me-1"></i>Pending
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2">
                                                        <i class="bi bi-x-circle-fill me-1"></i>Rejected
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div>
                                                    <span class="fw-medium">{{ date('M j, Y', strtotime($row->created_at)) }}</span>
                                                    <small class="d-block text-muted">{{ date('g:i A', strtotime($row->created_at)) }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-1">
                                                    <a href="/admin/view-user/{{ $row->user_id }}"
                                                       class="btn btn-outline-primary btn-sm"
                                                       data-bs-toggle="tooltip" title="View User Details">
                                                        <i class="bi bi-eye"></i>
                                                    </a>

                                                    @if($row->verify_status === "verified")
                                                        <button type="button"
                                                                class="btn btn-outline-warning btn-sm"
                                                                onclick="confirmVerificationAction('revoke', '{{ $row->user_id }}', '{{ $row->user->name }}')"
                                                                data-bs-toggle="tooltip" title="Revoke Verification">
                                                            <i class="bi bi-shield-x"></i>
                                                        </button>
                                                    @else
                                                        <button type="button"
                                                                class="btn btn-outline-success btn-sm"
                                                                onclick="confirmVerificationAction('verify', '{{ $row->user_id }}', '{{ $row->user->name }}')"
                                                                data-bs-toggle="tooltip" title="Verify Documents">
                                                            <i class="bi bi-shield-check"></i>
                                                        </button>
                                                    @endif


                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center">
                                                    <div class="bg-light rounded-circle p-4 mb-3">
                                                        <i class="bi bi-file-earmark-x display-4 text-muted"></i>
                                                    </div>
                                                    <h5 class="text-muted mb-2">No Document Submissions Found</h5>
                                                    <p class="text-muted mb-0">There are no document verification requests matching your filters.</p>
                                                    <button type="button" class="btn btn-outline-primary mt-3" onclick="clearFilters()">
                                                        Clear Filters
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($users->hasPages())
                            <div class="row align-items-center mt-4 pt-3 border-top">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center text-muted">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <span>Showing <strong>{{ $users->firstItem() }}</strong> to <strong>{{ $users->lastItem() }}</strong> of <strong>{{ $users->total() }}</strong> results</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-end">
                                        {{ $users->links('pagination::bootstrap-4') }}
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

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold" id="confirmationModalLabel">
                    <i class="bi bi-exclamation-triangle text-warning me-2"></i>Confirm Action
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3" id="modalIcon">
                            <i class="bi bi-exclamation-triangle-fill text-warning fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="mb-0 fw-medium" id="confirmationMessage"></p>
                        <small class="text-muted" id="confirmationSubtext">This action will update the verification status.</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmButton">
                    <i class="bi bi-check-circle me-1"></i>Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Document Preview Modal -->
<div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="documentModalLabel">
                    <i class="bi bi-file-earmark-image me-2"></i>Document Preview
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="documentContent">
                <!-- Document content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Bulk Actions Modal -->
<div class="modal fade" id="bulkActionModal" tabindex="-1" aria-labelledby="bulkActionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bulkActionModalLabel">
                    <i class="bi bi-check-all me-2"></i>Bulk Actions
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Select an action to apply to all selected users:</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-success" onclick="bulkAction('verify')">
                        <i class="bi bi-check-circle me-2"></i>Verify Selected
                    </button>
                    <button type="button" class="btn btn-outline-warning" onclick="bulkAction('revoke')">
                        <i class="bi bi-x-shield me-2"></i>Revoke Selected
                    </button>
                    <button type="button" class="btn btn-outline-danger" onclick="bulkAction('reject')">
                        <i class="bi bi-x-circle me-2"></i>Reject Selected
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.table th {
    font-weight: 600;
    color: #495057;
    text-transform: uppercase;
    font-size: 0.75rem;
    letter-spacing: 0.5px;
}

.avatar-container {
    position: relative;
}

.avatar-container::before {
    content: '';
    position: absolute;
    top: -2px;
    left: -2px;
    right: -2px;
    bottom: -2px;
    border-radius: 50%;
    background: linear-gradient(45deg, #667eea, #764ba2);
    z-index: -1;
}

.btn:hover {
    transform: translateY(-1px);
    transition: all 0.2s ease;
}

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
}

.verification-row:hover {
    background-color: rgba(102, 126, 234, 0.05);
}
</style>

<script>
// Enhanced confirmation function for verification actions
function confirmVerificationAction(action, userId, userName) {
    const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
    const confirmButton = document.getElementById('confirmButton');
    const message = document.getElementById('confirmationMessage');
    const subtext = document.getElementById('confirmationSubtext');
    const modalTitle = document.getElementById('confirmationModalLabel');
    const modalIcon = document.getElementById('modalIcon');

    let actionUrl, buttonClass, iconClass, actionText, subtextMessage;

    switch(action) {
        case 'verify':
            actionUrl = `/admin/verify-status/${userId}/verified/yes`;
            buttonClass = 'btn-success';
            iconClass = 'bi-check-shield-fill text-success';
            actionText = 'Verify Documents';
            subtextMessage = 'This will mark the user\'s documents as verified and grant them full account access.';
            break;
        case 'revoke':
            actionUrl = `/admin/verify-status/${userId}/pending/no`;
            buttonClass = 'btn-warning';
            iconClass = 'bi-x-shield-fill text-warning';
            actionText = 'Revoke Verification';
            subtextMessage = 'This will revoke the user\'s verification status and may limit their account access.';
            break;
        case 'reject':
            actionUrl = `/admin/verify-status/${userId}/rejected/no`;
            buttonClass = 'btn-danger';
            iconClass = 'bi-x-circle-fill text-danger';
            actionText = 'Reject Documents';
            subtextMessage = 'This will reject the user\'s documents and they will need to resubmit.';
            break;
    }

    modalTitle.innerHTML = `<i class="${iconClass} me-2"></i>${actionText}`;
    modalIcon.innerHTML = `<i class="${iconClass} fs-4"></i>`;
    message.textContent = `Are you sure you want to ${action} the documents for "${userName}"?`;
    subtext.textContent = subtextMessage;
    confirmButton.className = `btn ${buttonClass}`;
    confirmButton.innerHTML = `<i class="bi bi-check-circle me-1"></i>${actionText}`;

    confirmButton.onclick = function() {
        confirmButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';
        confirmButton.disabled = true;

        setTimeout(() => {
            window.location.href = actionUrl;
        }, 500);
    };

    modal.show();
}

// Document preview function
function openDocumentModal(userId) {
    // Implementation for document preview
    alert('Document preview functionality would be implemented here');
}

// Download documents function
function downloadDocuments(userId) {
    // Implementation for document download
    alert('Document download functionality would be implemented here');
}

// Bulk actions
function bulkAction(action) {
    const selectedIds = [];
    document.querySelectorAll('.row-select:checked').forEach(checkbox => {
        selectedIds.push(checkbox.value);
    });

    if (selectedIds.length === 0) {
        alert('Please select at least one user');
        return;
    }

    alert(`${action} action would be applied to ${selectedIds.length} selected users`);
    bootstrap.Modal.getInstance(document.getElementById('bulkActionModal')).hide();
}

// Select all functionality
document.getElementById('selectAll').addEventListener('change', function() {
    document.querySelectorAll('.row-select').forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});

// Table filtering functionality
function filterTable() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const accountType = document.getElementById('accountTypeFilter').value.toLowerCase();
    const verification = document.getElementById('verificationFilter').value.toLowerCase();
        const documentType = document.getElementById('documentTypeFilter').value.toLowerCase();
    const dateFilter = document.getElementById('dateFilter').value.toLowerCase();

    const rows = document.querySelectorAll('.verification-row');

    rows.forEach(row => {
        const userText = row.textContent.toLowerCase();
        const status = row.getAttribute('data-status');
        const accountTypeText = row.querySelector('.badge.bg-info').textContent.toLowerCase();
        const documentTypeText = row.querySelector('td:nth-child(4) strong').textContent.toLowerCase();
        const dateText = row.querySelector('td:nth-child(7) span').textContent.toLowerCase();

        const matchesSearch = userText.includes(searchInput);
        const matchesAccountType = accountType === '' || accountTypeText.includes(accountType);
        const matchesVerification = verification === '' || status === verification;
        const matchesDocumentType = documentType === '' || documentTypeText.includes(documentType);
        const matchesDate = checkDateFilter(dateFilter, dateText);

        if (matchesSearch && matchesAccountType && matchesVerification && matchesDocumentType && matchesDate) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

function checkDateFilter(filter, dateText) {
    if (filter === '') return true;

    const rowDate = new Date(dateText);
    const today = new Date();

    switch(filter) {
        case 'today':
            return rowDate.toDateString() === today.toDateString();
        case 'week':
            const weekStart = new Date(today);
            weekStart.setDate(today.getDate() - today.getDay());
            return rowDate >= weekStart;
        case 'month':
            return rowDate.getMonth() === today.getMonth() &&
                   rowDate.getFullYear() === today.getFullYear();
        default:
            return true;
    }
}

// Clear all filters
function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('accountTypeFilter').value = '';
    document.getElementById('verificationFilter').value = '';
    document.getElementById('documentTypeFilter').value = '';
    document.getElementById('dateFilter').value = '';
    filterTable();
}

// Export data function
function exportData() {
    alert('Export functionality would be implemented here');
    // In a real implementation, this would likely:
    // 1. Gather all filtered data
    // 2. Convert to CSV/Excel format
    // 3. Trigger download
}

// Refresh page
function refreshPage() {
    window.location.reload();
}

// Initialize event listeners for filters
document.getElementById('searchInput').addEventListener('keyup', filterTable);
document.getElementById('accountTypeFilter').addEventListener('change', filterTable);
document.getElementById('verificationFilter').addEventListener('change', filterTable);
document.getElementById('documentTypeFilter').addEventListener('change', filterTable);
document.getElementById('dateFilter').addEventListener('change', filterTable);

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Document preview modal implementation
function openDocumentModal(userId) {
    const modal = new bootstrap.Modal(document.getElementById('documentModal'));
    const modalContent = document.getElementById('documentContent');

    // Show loading state
    modalContent.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3">Loading documents...</p>
        </div>
    `;

    modal.show();

    // In a real implementation, you would fetch the documents via AJAX
    setTimeout(() => {
        // This is a mock implementation - in reality you'd fetch actual documents
        modalContent.innerHTML = `
            <div class="row">
                <div class="col-md-6 mb-4">
                    <h5 class="mb-3"><i class="bi bi-file-earmark-image me-2"></i>ID Document</h5>
                    <div class="border rounded p-2 bg-light" style="height: 500px; overflow: auto;">
                        <img src="https://via.placeholder.com/800x1000?text=ID+Document"
                             class="img-fluid"
                             alt="ID Document Preview">
                    </div>
                </div>
                <div class="col-md-6">
                    <h5 class="mb-3"><i class="bi bi-house-door me-2"></i>Address Proof</h5>
                    <div class="border rounded p-2 bg-light" style="height: 500px; overflow: auto;">
                        <img src="https://via.placeholder.com/800x1000?text=Address+Proof"
                             class="img-fluid"
                             alt="Address Proof Preview">
                    </div>
                </div>
            </div>
            <div class="mt-3 text-center">
                <button class="btn btn-primary me-2" onclick="downloadDocuments('${userId}')">
                    <i class="bi bi-download me-2"></i>Download All
                </button>
                <button class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-2"></i>Close
                </button>
            </div>
        `;
    }, 1000);
}

// Download documents implementation
function downloadDocuments(userId) {
    // In a real implementation, this would trigger a download
    alert(`Downloading all documents for user ID: ${userId}`);
    // Typically this would:
    // 1. Fetch the documents from the server
    // 2. Package them in a zip file
    // 3. Trigger the download
}
</script>

@include('admin.layouts.footer')
