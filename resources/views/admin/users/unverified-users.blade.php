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
                                    <i class="bi bi-shield-check me-2"></i>{{ $page_title }}
                                </h5>
                                <p class="mb-0 opacity-75">Manage user account verification status</p>
                            </div>
                            <div class="col-auto">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-light btn-sm" onclick="refreshPage()">
                                        <i class="bi bi-arrow-clockwise"></i> Refresh
                                    </button>
                                    <button type="button" class="btn btn-outline-light btn-sm" onclick="exportData()">
                                        <i class="bi bi-download"></i> Export
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
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="bi bi-search text-muted"></i>
                                    </span>
                                    <input type="text" class="form-control border-start-0 ps-0" id="searchInput" placeholder="Search by name, email, or phone...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="accountTypeFilter">
                                    <option value="">All Account Types</option>
                                    <option value="Private">Private</option>
                                    <option value="Commercial">Commercial</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-select" id="statusFilter">
                                    <option value="">All Status</option>
                                    <option value="1">Verified</option>
                                    <option value="0">Unverified</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                                    <i class="bi bi-x-circle"></i> Clear
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
                                                    <i class="bi bi-people-fill text-primary fs-4"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h4 class="mb-0 fw-bold">{{ $users->total() }}</h4>
                                                <p class="text-muted mb-0">Total Users</p>
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
                                                    <i class="bi bi-shield-check-fill text-success fs-4"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h4 class="mb-0 fw-bold">{{ $users->where('acc_status', 1)->count() }}</h4>
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
                                                    <i class="bi bi-shield-x-fill text-warning fs-4"></i>
                                                </div>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h4 class="mb-0 fw-bold">{{ $users->where('acc_status', 0)->count() }}</h4>
                                                <p class="text-muted mb-0">Unverified</p>
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
                                                    <i class="bi bi-clock-fill text-info fs-4"></i>
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

                        <!-- Users Table -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="usersTable">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" class="fw-semibold border-0">
                                            <i class="bi bi-person-fill me-2"></i>User Details
                                        </th>
                                        <th scope="col" class="fw-semibold border-0">
                                            <i class="bi bi-shield-fill me-2"></i>Account Type
                                        </th>
                                        <th scope="col" class="fw-semibold border-0">
                                            <i class="bi bi-envelope-fill me-2"></i>Contact Info
                                        </th>
                                        <th scope="col" class="fw-semibold border-0">
                                            <i class="bi bi-calendar-fill me-2"></i>Date Joined
                                        </th>
                                        <th scope="col" class="fw-semibold border-0 text-center">
                                            <i class="bi bi-shield-check-fill me-2"></i>Verification
                                        </th>
                                        <th scope="col" class="fw-semibold border-0 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $row)
                                        <tr class="border-bottom">
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-container me-3">
                                                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                            <i class="bi bi-person-fill text-primary fs-5"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0 fw-semibold">{{ $row->name }}</h6>
                                                        <small class="text-muted">ID: {{ $row->user_id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-3 py-2">
                                                    {{ ucfirst($row->acc_type) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div>
                                                    <div class="d-flex align-items-center mb-1">
                                                        <i class="bi bi-envelope text-muted me-2"></i>
                                                        <a href="mailto:{{ $row->email }}" class="text-decoration-none text-primary">
                                                            {{ $row->email }}
                                                        </a>
                                                    </div>
                                                    <div class="d-flex align-items-center">
                                                        <i class="bi bi-telephone text-muted me-2"></i>
                                                        <a href="tel:{{ $row->phone }}" class="text-decoration-none">
                                                            {{ $row->phone ?? 'N/A' }}
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div>
                                                    <span class="fw-medium">{{ date('M j, Y', strtotime($row->created_at)) }}</span>
                                                    <small class="d-block text-muted">{{ date('g:i A', strtotime($row->created_at)) }}</small>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @if($row->acc_status == 1)
                                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">
                                                        <i class="bi bi-check-circle-fill me-1"></i>Verified
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2">
                                                        <i class="bi bi-x-circle-fill me-1"></i>Unverified
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-2">
                                                    <a href="/admin/view-user/{{ $row->user_id }}"
                                                       class="btn btn-outline-primary btn-sm d-flex align-items-center"
                                                       data-bs-toggle="tooltip" title="View User Details">
                                                        <i class="bi bi-eye"></i>
                                                        <span class="ms-1 d-none d-md-inline">View</span>
                                                    </a>

                                                    @if($row->acc_status == 1)
                                                        <button type="button"
                                                                class="btn btn-outline-warning btn-sm d-flex align-items-center"
                                                                onclick="confirmStatusChange('unverify', '{{ $row->user_id }}', '{{ $row->name }}')"
                                                                data-bs-toggle="tooltip" title="Unverify User">
                                                            <i class="bi bi-shield-x"></i>
                                                            <span class="ms-1 d-none d-lg-inline">Unverify</span>
                                                        </button>
                                                    @else
                                                        <button type="button"
                                                                class="btn btn-outline-success btn-sm d-flex align-items-center"
                                                                onclick="confirmStatusChange('verify', '{{ $row->user_id }}', '{{ $row->name }}')"
                                                                data-bs-toggle="tooltip" title="Verify User">
                                                            <i class="bi bi-shield-check"></i>
                                                            <span class="ms-1 d-none d-lg-inline">Verify</span>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center">
                                                    <div class="bg-light rounded-circle p-4 mb-3">
                                                        <i class="bi bi-people display-4 text-muted"></i>
                                                    </div>
                                                    <h5 class="text-muted mb-2">No Users Found</h5>
                                                    <p class="text-muted mb-0">There are no users matching your current filters.</p>
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
                    <i class="bi bi-shield-exclamation text-warning me-2"></i>Confirm Action
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <div class="d-flex align-items-start">
                    <div class="flex-shrink-0 me-3">
                        <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                            <i class="bi bi-exclamation-triangle-fill text-warning fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1">
                        <p class="mb-0 fw-medium" id="confirmationMessage"></p>
                        <small class="text-muted">This action will update the user's verification status.</small>
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

.card {
    transition: all 0.3s ease;
}

.card:hover {
    transform: translateY(-2px);
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

.btn-group .btn {
    transition: all 0.2s ease;
}

.btn-group .btn:hover {
    transform: translateY(-1px);
}
</style>

<script>
// Enhanced confirmation function for status changes
function confirmStatusChange(action, userId, userName) {
    const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
    const confirmButton = document.getElementById('confirmButton');
    const message = document.getElementById('confirmationMessage');
    const modalTitle = document.getElementById('confirmationModalLabel');

    let actionText = action === 'verify' ? 'verify' : 'unverify';
    let actionUrl = `/admin/user-status/${userId}/${action === 'verify' ? '1' : '0'}`;
    let buttonClass = action === 'verify' ? 'btn-success' : 'btn-warning';
    let iconClass = action === 'verify' ? 'bi-shield-check' : 'bi-shield-x';

    modalTitle.innerHTML = `<i class="bi ${iconClass} text-${action === 'verify' ? 'success' : 'warning'} me-2"></i>${action === 'verify' ? 'Verify' : 'Unverify'} User Account`;
    message.textContent = `Are you sure you want to ${actionText} the account for "${userName}"?`;
    confirmButton.className = `btn ${buttonClass}`;
    confirmButton.innerHTML = `<i class="bi ${iconClass} me-1"></i>${action === 'verify' ? 'Verify' : 'Unverify'} Account`;

    confirmButton.onclick = function() {
        // Add loading state
        confirmButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';
        confirmButton.disabled = true;

        setTimeout(() => {
            window.location.href = actionUrl;
        }, 500);
    };

    modal.show();
}

// Table filtering and search functionality
function filterTable() {
    const searchInput = document.getElementById('searchInput').value.toLowerCase();
    const accountType = document.getElementById('accountTypeFilter').value.toLowerCase();
    const status = document.getElementById('statusFilter').value;
    const table = document.getElementById('usersTable');
    const tbody = table.getElementsByTagName('tbody')[0];
    const rows = tbody.getElementsByTagName('tr');

    let visibleRows = 0;

    for (let i = 0; i < rows.length; i++) {
        const row = rows[i];
        const cells = row.getElementsByTagName('td');

        if (cells.length > 0) {
            let showRow = true;

            // Get text content for filtering
            const name = cells[0].textContent.toLowerCase();
            const accountTypeText = cells[1].textContent.toLowerCase();
            const email = cells[2].textContent.toLowerCase();
            const phone = cells[2].textContent.toLowerCase();
            const verificationStatus = cells[4].textContent.toLowerCase();

            // Search filter (name, email, phone)
            if (searchInput && !name.includes(searchInput) && !email.includes(searchInput) && !phone.includes(searchInput)) {
                showRow = false;
            }

            // Account type filter
            if (accountType && !accountTypeText.includes(accountType)) {
                showRow = false;
            }

            // Status filter
            if (status) {
                const isVerified = verificationStatus.includes('verified') && !verificationStatus.includes('unverified');
                if ((status === '1' && !isVerified) || (status === '0' && isVerified)) {
                    showRow = false;
                }
            }

            row.style.display = showRow ? '' : 'none';
            if (showRow) visibleRows++;
        }
    }

    // Show/hide empty state
    const emptyRow = tbody.querySelector('tr td[colspan="6"]');
    if (emptyRow) {
        emptyRow.parentElement.style.display = visibleRows > 0 ? 'none' : '';
    }
}

// Event listeners for filters
document.getElementById('searchInput').addEventListener('keyup', debounce(filterTable, 300));
document.getElementById('accountTypeFilter').addEventListener('change', filterTable);
document.getElementById('statusFilter').addEventListener('change', filterTable);

// Debounce function for search input
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('accountTypeFilter').value = '';
    document.getElementById('statusFilter').value = '';
    filterTable();
}

function refreshPage() {
    location.reload();
}

function exportData() {
    // You can implement export functionality here
    alert('Export functionality would be implemented here');
}

// Initialize tooltips and other Bootstrap components
document.addEventListener('DOMContentLoaded', function () {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>

@include('admin.layouts.footer')
