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
                <div class="card shadow-sm">
                    <div class="card-header bg-white py-3">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="card-title mb-0">{{ $page_title }}</h5>
                                <p class="text-muted mb-0">Manage user accounts and permissions</p>
                            </div>
                            <div class="col-auto">
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="refreshTable()">
                                        <i class="bi bi-arrow-clockwise"></i> Refresh
                                    </button>
                                    <button type="button" class="btn btn-outline-success btn-sm" data-bs-toggle="modal" data-bs-target="#exportModal">
                                        <i class="bi bi-download"></i> Export
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        @if(session('status'))
                            <div class="alert alert-{{ session('status')['type'] }} alert-dismissible fade show" role="alert">
                                <i class="bi bi-{{ session('status')['type'] === 'success' ? 'check-circle' : 'exclamation-triangle' }}-fill me-2"></i>
                                {{ session('status')['text'] }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Filter Section -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control" id="searchInput" placeholder="Search users...">
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
                                <select class="form-select" id="verificationFilter">
                                    <option value="">All Verification Status</option>
                                    <option value="yes">Verified</option>
                                    <option value="no">Not Verified</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                                    <i class="bi bi-x-circle"></i> Clear
                                </button>
                            </div>
                        </div>

                        <!-- Statistics Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white">
                                    <div class="card-body text-center">
                                        <h4 class="mb-0">{{ number_format($stats['total'] ?? 0) }}</h4>
                                        <small>Total Users</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h4 class="mb-0">{{ number_format($stats['active'] ?? 0) }}</h4>
                                        <small>Active Users</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning text-white">
                                    <div class="card-body text-center">
                                        <h4 class="mb-0">{{ number_format($users->total()) }}</h4>
                                        <small>This List</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-danger text-white">
                                    <div class="card-body text-center">
                                        <h4 class="mb-0">
                                            <a href="/admin/disabled-users" class="text-white text-decoration-none">
                                                {{ number_format($stats['disabled'] ?? 0) }}
                                            </a>
                                        </h4>
                                        <small>Disabled Users</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Users Table -->
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="usersTable">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" class="fw-semibold">
                                            <i class="bi bi-person-fill me-1"></i>Name
                                        </th>
                                        <th scope="col" class="fw-semibold">
                                            <i class="bi bi-shield-fill-check me-1"></i>Account Type
                                        </th>
                                        <th scope="col" class="fw-semibold">
                                            <i class="bi bi-envelope-fill me-1"></i>Email
                                        </th>
                                        <th scope="col" class="fw-semibold">
                                            <i class="bi bi-telephone-fill me-1"></i>Phone
                                        </th>
                                        <th scope="col" class="fw-semibold text-center">
                                            <i class="bi bi-patch-check-fill me-1"></i>Status
                                        </th>
                                        <th scope="col" class="fw-semibold">
                                            <i class="bi bi-calendar-fill me-1"></i>Date Joined
                                        </th>
                                        <th scope="col" class="fw-semibold text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $row)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                                        <i class="bi bi-person text-primary"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $row->name }}</h6>
                                                        <small class="text-muted">ID: {{ $row->user_id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info text-capitalize">
                                                    {{ $row->acc_type }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="mailto:{{ $row->email }}" class="text-decoration-none">
                                                    {{ $row->email }}
                                                </a>
                                            </td>
                                            <td>
                                                <a href="tel:{{ $row->phone }}" class="text-decoration-none">
                                                    {{ $row->phone ?? 'N/A' }}
                                                </a>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex flex-column align-items-center gap-1">
                                                    <span class="badge {{ $row->verified === 'yes' ? 'bg-success' : 'bg-secondary' }}">
                                                        <i class="bi bi-{{ $row->verified === 'yes' ? 'check-circle' : 'x-circle' }} me-1"></i>
                                                        {{ ucfirst($row->verified) }}
                                                    </span>
                                                    @if($row->disable_account === 'yes')
                                                        <span class="badge bg-danger">
                                                            <i class="bi bi-slash-circle me-1"></i>Disabled
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ date('M j, Y', strtotime($row->created_at)) }}</span>
                                                <small class="d-block text-muted">{{ date('g:i A', strtotime($row->created_at)) }}</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="/admin/view-user/{{ $row->user_id }}"
                                                       class="btn btn-outline-primary btn-sm"
                                                       title="View User Details">
                                                        <i class="bi bi-eye"></i>
                                                    </a>

                                                    @if($row->disable_account === "no")
                                                        <button type="button"
                                                                class="btn btn-outline-warning btn-sm"
                                                                onclick="confirmAction('disable', '{{ $row->user_id }}', '{{ $row->name }}')"
                                                                title="Disable User">
                                                            <i class="bi bi-lock"></i>
                                                        </button>
                                                    @else
                                                        <button type="button"
                                                                class="btn btn-outline-success btn-sm"
                                                                onclick="confirmAction('enable', '{{ $row->user_id }}', '{{ $row->name }}')"
                                                                title="Enable User">
                                                            <i class="bi bi-unlock"></i>
                                                        </button>
                                                    @endif

                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle"
                                                                data-bs-toggle="dropdown" aria-expanded="false" title="More Actions">
                                                            <i class="bi bi-three-dots"></i>
                                                        </button>
                                                        <ul class="dropdown-menu">
                                                            <li><a class="dropdown-item" href="/admin/edit-user/{{ $row->user_id }}">
                                                                <i class="bi bi-pencil me-2"></i>Edit User
                                                            </a></li>
                                                            <li><a class="dropdown-item" href="/admin/user-activity/{{ $row->user_id }}">
                                                                <i class="bi bi-activity me-2"></i>View Activity
                                                            </a></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><a class="dropdown-item text-danger" href="#"
                                                                   onclick="confirmDelete('{{ $row->user_id }}', '{{ $row->name }}')">
                                                                <i class="bi bi-trash me-2"></i>Delete User
                                                            </a></li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-5">
                                                <div class="d-flex flex-column align-items-center">
                                                    <i class="bi bi-people display-1 text-muted mb-3"></i>
                                                    <h5 class="text-muted">No users found</h5>
                                                    <p class="text-muted">There are no users matching your criteria.</p>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($users->hasPages())
                            <div class="row align-items-center mt-4">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center text-muted">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <span>Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} results</span>
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
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmationModalLabel">Confirm Action</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex align-items-center mb-3">
                    <div class="flex-shrink-0">
                        <i class="bi bi-exclamation-triangle-fill text-warning display-6"></i>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <p class="mb-0" id="confirmationMessage"></p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmButton">Confirm</button>
            </div>
        </div>
    </div>
</div>

<script>
// Global variables to track current state
let currentState = {
    page: 1,
    search: '',
    accountType: '',
    verification: ''
};

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initializeEventListeners();
    
    // Load initial data if needed (optional)
    // performSearch(1);
});

function initializeEventListeners() {
    // Search input with debounce
    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                currentState.search = this.value;
                performSearch(1);
            }, 500);
        });
    }

    // Account type filter
    const accountTypeFilter = document.getElementById('accountTypeFilter');
    if (accountTypeFilter) {
        accountTypeFilter.addEventListener('change', function() {
            currentState.accountType = this.value;
            performSearch(1);
        });
    }

    // Verification filter
    const verificationFilter = document.getElementById('verificationFilter');
    if (verificationFilter) {
        verificationFilter.addEventListener('change', function() {
            currentState.verification = this.value;
            performSearch(1);
        });
    }
}

function performSearch(page = 1) {
    currentState.page = page;
    
    showLoading();
    
    // Prepare query parameters
    const params = new URLSearchParams({
        search: currentState.search,
        account_type: currentState.accountType,
        verification: currentState.verification,
        page: page
    });
    
    params.append('context', 'active');
    fetch(`/admin/users/search?${params}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                updateTable(data.users);
                updatePagination(data.pagination);
                updateStatistics(data.pagination.total);
            } else {
                throw new Error(data.message || 'Unknown error occurred');
            }
        })
        .catch(error => {
            console.error('Error fetching search results:', error);
            showError('Failed to load users. Please try again.');
        });
}

function updateTable(users) {
    const tbody = document.querySelector('#usersTable tbody');
    if (!tbody) {
        console.error('Table body not found');
        return;
    }
    
    if (!users || users.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-5">
                    <div class="d-flex flex-column align-items-center">
                        <i class="bi bi-people display-1 text-muted mb-3"></i>
                        <h5 class="text-muted">No users found</h5>
                        <p class="text-muted">There are no users matching your criteria.</p>
                    </div>
                </td>
            </tr>
        `;
        return;
    }
    
    let html = '';
    users.forEach(user => {
        // Sanitize data to prevent XSS and handle undefined values
        const userName = escapeHtml(user.name || 'N/A');
        const userId = escapeHtml(user.user_id || '');
        const userEmail = escapeHtml(user.email || '');
        const userPhone = escapeHtml(user.phone || '');
        const accType = escapeHtml(user.acc_type || '');
        const verified = user.verified === 'yes';
        const disabled = user.disable_account === 'yes';
        const createdAt = user.created_at ? new Date(user.created_at) : new Date();
        
        html += `
            <tr>
                <td>
                    <div class="d-flex align-items-center">
                        <div class="avatar bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            <i class="bi bi-person text-primary"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">${userName}</h6>
                            <small class="text-muted">ID: ${userId}</small>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="badge bg-info text-capitalize">
                        ${accType}
                    </span>
                </td>
                <td>
                    <a href="mailto:${userEmail}" class="text-decoration-none">
                        ${userEmail}
                    </a>
                </td>
                <td>
                    ${userPhone ? 
                        `<a href="tel:${userPhone}" class="text-decoration-none">${userPhone}</a>` : 
                        'N/A'
                    }
                </td>
                <td class="text-center">
                    <div class="d-flex flex-column align-items-center gap-1">
                        <span class="badge ${verified ? 'bg-success' : 'bg-secondary'}">
                            <i class="bi bi-${verified ? 'check-circle' : 'x-circle'} me-1"></i>
                            ${verified ? 'Verified' : 'Unverified'}
                        </span>
                        ${disabled ? 
                            `<span class="badge bg-danger">
                                <i class="bi bi-slash-circle me-1"></i>Disabled
                            </span>` : ''
                        }
                    </div>
                </td>
                <td>
                    <span class="text-muted">${formatDate(createdAt)}</span>
                    <small class="d-block text-muted">${formatTime(createdAt)}</small>
                </td>
                <td>
                    <div class="btn-group" role="group">
                        <a href="/admin/view-user/${userId}"
                           class="btn btn-outline-primary btn-sm"
                           title="View User Details">
                            <i class="bi bi-eye"></i>
                        </a>

                        ${!disabled ? 
                            `<button type="button"
                                class="btn btn-outline-warning btn-sm"
                                onclick="confirmAction('disable', '${userId}', '${userName.replace(/'/g, "\\'")}')"
                                title="Disable User">
                                <i class="bi bi-lock"></i>
                            </button>` :
                            `<button type="button"
                                class="btn btn-outline-success btn-sm"
                                onclick="confirmAction('enable', '${userId}', '${userName.replace(/'/g, "\\'")}')"
                                title="Enable User">
                                <i class="bi bi-unlock"></i>
                            </button>`
                        }

                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-secondary btn-sm dropdown-toggle"
                                    data-bs-toggle="dropdown" aria-expanded="false" title="More Actions">
                                <i class="bi bi-three-dots"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="/admin/edit-user/${userId}">
                                    <i class="bi bi-pencil me-2"></i>Edit User
                                </a></li>
                                <li><a class="dropdown-item" href="/admin/user-activity/${userId}">
                                    <i class="bi bi-activity me-2"></i>View Activity
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#"
                                       onclick="confirmDelete('${userId}', '${userName.replace(/'/g, "\\'")}')">
                                    <i class="bi bi-trash me-2"></i>Delete User
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </td>
            </tr>
        `;
    });
    
    tbody.innerHTML = html;
    
    // Re-initialize tooltips for new content
    initializeTooltips();
}

function updatePagination(pagination) {
    // Remove existing pagination if it exists
    const existingPagination = document.querySelector('.pagination-container');
    if (existingPagination) {
        existingPagination.remove();
    }
    
    // Create new pagination container
    const tableContainer = document.querySelector('.table-responsive');
    if (!tableContainer || !pagination) return;
    
    const paginationContainer = document.createElement('div');
    paginationContainer.className = 'row align-items-center mt-4 pagination-container';
    paginationContainer.innerHTML = `
        <div class="col-md-6">
            <div class="d-flex align-items-center text-muted">
                <i class="bi bi-info-circle me-2"></i>
                <span id="paginationInfo">Showing ${pagination.from || 0} to ${pagination.to || 0} of ${pagination.total || 0} results</span>
            </div>
        </div>
        <div class="col-md-6">
            <div class="d-flex justify-content-end">
                <nav>
                    <ul class="pagination mb-0" id="paginationLinks"></ul>
                </nav>
            </div>
        </div>
    `;
    
    tableContainer.parentNode.insertBefore(paginationContainer, tableContainer.nextSibling);
    
    // Generate pagination links
    const paginationLinks = document.getElementById('paginationLinks');
    if (!paginationLinks) return;
    
    let paginationHtml = '';
    const currentPage = pagination.current_page;
    const lastPage = pagination.last_page;
    
    // Previous button
    if (currentPage > 1) {
        paginationHtml += `
            <li class="page-item">
                <a class="page-link" href="javascript:void(0)" onclick="performSearch(${currentPage - 1})" aria-label="Previous">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
        `;
    } else {
        paginationHtml += `
            <li class="page-item disabled">
                <span class="page-link" aria-label="Previous">
                    <i class="bi bi-chevron-left"></i>
                </span>
            </li>
        `;
    }
    
    // Page numbers - show limited range around current page
    const startPage = Math.max(1, currentPage - 2);
    const endPage = Math.min(lastPage, currentPage + 2);
    
    for (let i = startPage; i <= endPage; i++) {
        if (i === currentPage) {
            paginationHtml += `
                <li class="page-item active">
                    <span class="page-link">${i}</span>
                </li>
            `;
        } else {
            paginationHtml += `
                <li class="page-item">
                    <a class="page-link" href="javascript:void(0)" onclick="performSearch(${i})">${i}</a>
                </li>
            `;
        }
    }
    
    // Next button
    if (currentPage < lastPage) {
        paginationHtml += `
            <li class="page-item">
                <a class="page-link" href="javascript:void(0)" onclick="performSearch(${currentPage + 1})" aria-label="Next">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
        `;
    } else {
        paginationHtml += `
            <li class="page-item disabled">
                <span class="page-link" aria-label="Next">
                    <i class="bi bi-chevron-right"></i>
                </span>
            </li>
        `;
    }
    
    paginationLinks.innerHTML = paginationHtml;
}

function updateStatistics(totalUsers) {
    // This is optional - update if you have dynamic statistics
    console.log('Total users:', totalUsers);
}

function showLoading() {
    const tbody = document.querySelector('#usersTable tbody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-5">
                    <div class="d-flex justify-content-center align-items-center">
                        <div class="spinner-border text-primary me-3" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <span class="text-muted">Loading users...</span>
                    </div>
                </td>
            </tr>
        `;
    }
}

function showError(message) {
    const tbody = document.querySelector('#usersTable tbody');
    if (tbody) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center py-5">
                    <div class="alert alert-danger d-inline-block" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>${message}
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-primary btn-sm" onclick="performSearch(1)">
                            <i class="bi bi-arrow-clockwise me-1"></i>Try Again
                        </button>
                    </div>
                </td>
            </tr>
        `;
    }
}

// Helper functions
function escapeHtml(unsafe) {
    if (typeof unsafe !== 'string') return unsafe;
    return unsafe
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}

function formatDate(date) {
    return date.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric', 
        year: 'numeric' 
    });
}

function formatTime(date) {
    return date.toLocaleTimeString('en-US', { 
        hour: 'numeric', 
        minute: '2-digit', 
        hour12: true 
    });
}

function initializeTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

// Disable/enable via POST form
function confirmAction(action, userId, userName) {
    const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
    const confirmButton = document.getElementById('confirmButton');
    const message = document.getElementById('confirmationMessage');

    message.textContent = `Are you sure you want to ${action} the account for "${userName}"?`;
    confirmButton.className = `btn btn-${action === 'disable' ? 'warning' : 'success'}`;
    confirmButton.textContent = action === 'disable' ? 'Disable Account' : 'Enable Account';

    confirmButton.onclick = function () {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/disable-status/${userId}`;
        form.innerHTML = `
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="action" value="${action}">
        `;
        document.body.appendChild(form);
        form.submit();
    };

    modal.show();
}

function confirmDelete(userId, userName) {
    const modal = new bootstrap.Modal(document.getElementById('confirmationModal'));
    const confirmButton = document.getElementById('confirmButton');
    const message = document.getElementById('confirmationMessage');

    message.textContent = `Are you sure you want to permanently delete the account for "${userName}"? This action cannot be undone.`;
    confirmButton.className = 'btn btn-danger';
    confirmButton.textContent = 'Delete Account';

    confirmButton.onclick = function() {
        // Create form for DELETE request with CSRF token
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/delete-user/${userId}`;

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
    };

    modal.show();
}

function clearFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('accountTypeFilter').value = '';
    document.getElementById('verificationFilter').value = '';
    
    currentState.search = '';
    currentState.accountType = '';
    currentState.verification = '';
    
    performSearch(1);
}

function refreshTable() {
    performSearch(currentState.page);
}
</script>

@include('admin.layouts.footer')
