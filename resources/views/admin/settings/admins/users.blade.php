@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Dashboard</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/index">Home</a></li>
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    @if(session('status'))
        <div class="alert alert-{{session('status')['type']}} alert-dismissible fade show">
            {{session('status')['text']}}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <section class="user-management-section">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="section-header mb-4">
                        <h2><i class="bi bi-people-fill"></i> User Management</h2>
                        <p class="text-muted">Create, edit, and manage admin users</p>
                    </div>

                    <!-- Alert Messages -->
                    <div id="alertPlaceholder"></div>

                    <!-- Controls -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="d-flex">
                            <div class="input-group me-2" style="max-width: 300px;">
                                <span class="input-group-text"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control" id="searchInput" placeholder="Search users...">
                            </div>
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal">
                            <i class="bi bi-plus-circle"></i> Create New User
                        </button>
                    </div>

                    <!-- User Table -->
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover user-table" id="usersTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Status</th>
                                            <th>2FA</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($admins as $row)
                                        <tr data-user-id="{{ $row->id }}">
                                            <td>{{ $row->admin_id }}</td>
                                            <td class="username">{{ $row->username }}</td>
                                            <td class="email">{{ $row->email }}</td>
                                            <td>
                                                <span class="badge {{ $row->status === 'active' ? 'bg-success' : 'bg-warning' }} status-badge">
                                                    {{ ucfirst($row->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($row->two_factor_confirmed_at)
                                                    <span class="badge bg-success"><i class="bi bi-shield-check"></i> Enrolled</span>
                                                @else
                                                    <span class="badge bg-secondary"><i class="bi bi-shield-x"></i> Not set</span>
                                                @endif
                                            </td>
                                            <td class="action-buttons">
                                                <button class="btn btn-sm btn-outline-primary edit-user me-1"
                                                        data-id="{{ $row->id }}"
                                                        data-username="{{ $row->username }}"
                                                        data-status="{{ $row->status }}"
                                                        data-email="{{ $row->email }}">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </button>
                                                @if($row->two_factor_confirmed_at && $row->id !== auth()->guard('admin')->id())
                                                <button class="btn btn-sm btn-outline-warning reset-2fa me-1"
                                                        data-id="{{ $row->id }}"
                                                        data-username="{{ $row->username }}">
                                                    <i class="bi bi-shield-slash"></i> Reset 2FA
                                                </button>
                                                @endif
                                                <button class="btn btn-sm btn-outline-danger delete-user"
                                                        data-id="{{ $row->id }}"
                                                        data-username="{{ $row->username }}">
                                                    <i class="bi bi-trash"></i> Delete
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create User Modal -->
        <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="createUserModalLabel">Create New User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="createUserForm" action="/settings/manage-admins" method="post">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="confirmPassword" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="confirmPassword" name="password_confirmation" required>
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                                Create User
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit User Modal -->
        <div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="editUserForm">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <input type="hidden" id="edit_id" name="id">
                            <div class="mb-3">
                                <label for="edit_admin_id" class="form-label">Admin ID</label>
                                <input type="text" class="form-control" id="edit_admin_id" name="admin_id" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="edit_username" class="form-label">Username <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="edit_username" name="username" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="edit_email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="edit_email" name="email" required>
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="edit_username" class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-control" id="edit_status" required>
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="edit_password" class="form-label">Password <small class="text-muted">(leave blank to keep current)</small></label>
                                <input type="password" class="form-control" id="edit_password" name="password">
                                <div class="invalid-feedback"></div>
                            </div>
                            <div class="mb-3">
                                <label for="edit_confirmPassword" class="form-label">Confirm Password</label>
                                <input type="password" class="form-control" id="edit_confirmPassword" name="password_confirmation">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reset 2FA Confirmation Modal -->
        <div class="modal fade" id="reset2faModal" tabindex="-1" aria-labelledby="reset2faModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-warning bg-opacity-10">
                        <h5 class="modal-title" id="reset2faModalLabel"><i class="bi bi-shield-slash me-2"></i>Reset Two-Factor Authentication</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>This will remove the 2FA setup for <strong id="reset2faUserName"></strong>.</p>
                        <p class="text-muted small mb-0">They will be prompted to set up 2FA again on their next login.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-warning" id="confirmReset2fa">
                            <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                            Reset 2FA
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete Confirmation Modal -->
        <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteUserModalLabel">Confirm Deletion</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete this user? This action cannot be undone.</p>
                        <p class="fw-bold">User: <span id="deleteUserName"></span></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-danger" id="confirmDelete">
                            <span class="spinner-border spinner-border-sm me-1 d-none" role="status"></span>
                            Delete User
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main><!-- End #main -->

<style>
.user-management-section {
    padding: 0;
}

.section-header h2 {
    color: #2c3e50;
    font-weight: 600;
}

.card {
    border: none;
    border-radius: 12px;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.action-buttons .btn {
    margin-right: 5px;
}

.status-badge {
    font-size: 0.75rem;
    padding: 0.5em 0.8em;
}

.required-field {
    color: #dc3545;
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
}

.btn-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}

.modal-content {
    border-radius: 12px;
}

.form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.alert {
    border-radius: 8px;
}

.input-group-text {
    background-color: #f8f9fa;
    border-color: #ced4da;
}

@media (max-width: 768px) {
    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 1rem;
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .action-buttons .btn {
        margin-right: 0;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize variables
    let currentUserId = null;

    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const usersTable = document.getElementById('usersTable');

    searchInput.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = usersTable.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const username = row.querySelector('.username').textContent.toLowerCase();
            const email = row.querySelector('.email').textContent.toLowerCase();

            if (username.includes(searchTerm) || email.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Edit user functionality
    document.querySelectorAll('.edit-user').forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            const username = this.getAttribute('data-username');
            const email = this.getAttribute('data-email');
            const status = this.getAttribute('data-status');

            // Populate edit modal
            document.getElementById('edit_id').value = userId;
            document.getElementById('edit_admin_id').value = userId;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_status').value = status;
            document.getElementById('edit_password').value = '';
            document.getElementById('edit_confirmPassword').value = '';

            // Clear previous validation states
            clearValidation('editUserForm');

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
            modal.show();
        });
    });

    // Delete user functionality
    document.querySelectorAll('.delete-user').forEach(button => {
        button.addEventListener('click', function() {
            currentUserId = this.getAttribute('data-id');
            const username = this.getAttribute('data-username');

            document.getElementById('deleteUserName').textContent = username;

            const modal = new bootstrap.Modal(document.getElementById('deleteUserModal'));
            modal.show();
        });
    });

    // Confirm delete
    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (!currentUserId) return;

        const button = this;
        const spinner = button.querySelector('.spinner-border');

        // Show loading state
        button.disabled = true;
        spinner.classList.remove('d-none');

        // Simulate API call (replace with actual AJAX call)
        fetch(`/settings/manage-admins/${currentUserId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove row from table
                const row = document.querySelector(`tr[data-user-id="${currentUserId}"]`);
                if (row) {
                    row.remove();
                }

                showAlert('User deleted successfully!', 'success');

                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('deleteUserModal')).hide();
            } else {
                showAlert('Error deleting user: ' + (data.message || 'Unknown error'), 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error deleting user. Please try again.', 'danger');
        })
        .finally(() => {
            // Reset button state
            button.disabled = false;
            spinner.classList.add('d-none');
            currentUserId = null;
        });
    });

    // Reset 2FA functionality
    document.querySelectorAll('.reset-2fa').forEach(button => {
        button.addEventListener('click', function() {
            currentUserId = this.getAttribute('data-id');
            document.getElementById('reset2faUserName').textContent = this.getAttribute('data-username');
            new bootstrap.Modal(document.getElementById('reset2faModal')).show();
        });
    });

    document.getElementById('confirmReset2fa').addEventListener('click', function() {
        if (!currentUserId) return;

        const button = this;
        const spinner = button.querySelector('.spinner-border');
        button.disabled = true;
        spinner.classList.remove('d-none');

        fetch(`/settings/manage-admins/${currentUserId}/reset-2fa`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update the 2FA badge in the row
                const row = document.querySelector(`tr[data-user-id="${currentUserId}"]`);
                if (row) {
                    const badge = row.querySelectorAll('td')[4];
                    badge.innerHTML = '<span class="badge bg-secondary"><i class="bi bi-shield-x"></i> Not set</span>';
                    // Remove the reset button from this row
                    const resetBtn = row.querySelector('.reset-2fa');
                    if (resetBtn) resetBtn.remove();
                }
                showAlert(data.message, 'success');
                bootstrap.Modal.getInstance(document.getElementById('reset2faModal')).hide();
            } else {
                showAlert(data.message || 'Error resetting 2FA.', 'danger');
            }
        })
        .catch(() => showAlert('Error resetting 2FA. Please try again.', 'danger'))
        .finally(() => {
            button.disabled = false;
            spinner.classList.add('d-none');
            currentUserId = null;
        });
    });

    // Handle create user form submission
    document.getElementById('createUserForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const form = this;
        const submitButton = form.querySelector('button[type="submit"]');
        const spinner = submitButton.querySelector('.spinner-border');

        // Clear previous validation
        clearValidation('createUserForm');

        // Validate passwords match
        const password = form.querySelector('#password').value;
        const confirmPassword = form.querySelector('#confirmPassword').value;

        if (password !== confirmPassword) {
            showFieldError('confirmPassword', 'Passwords do not match');
            return;
        }

        // Show loading state
        submitButton.disabled = true;
        spinner.classList.remove('d-none');

        // Submit form (this will use the actual form action)
        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('User created successfully!', 'success');
                bootstrap.Modal.getInstance(document.getElementById('createUserModal')).hide();
                form.reset();
                // Reload page to show new user (in a real app, you'd add the row dynamically)
                setTimeout(() => location.reload(), 1000);
            } else {
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        showFieldError(field, data.errors[field][0]);
                    });
                } else {
                    showAlert('Error creating user: ' + (data.message || 'Unknown error'), 'danger');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error creating user. Please try again.', 'danger');
        })
        .finally(() => {
            submitButton.disabled = false;
            spinner.classList.add('d-none');
        });
    });

    // Handle edit user form submission
    document.getElementById('editUserForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const form = this;
        const submitButton = form.querySelector('button[type="submit"]');
        const spinner = submitButton.querySelector('.spinner-border');
        const userId = document.getElementById('edit_id').value;

        // Clear previous validation
        clearValidation('editUserForm');

        // Validate passwords match (if password is provided)
        const password = form.querySelector('#edit_password').value;
        const confirmPassword = form.querySelector('#edit_confirmPassword').value;

        if (password && password !== confirmPassword) {
            showFieldError('edit_confirmPassword', 'Passwords do not match');
            return;
        }

        // Show loading state
        submitButton.disabled = true;
        spinner.classList.remove('d-none');

        const formData = new FormData(form);

        fetch(`/settings/manage-admins/${userId}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('User updated successfully!', 'success');
                bootstrap.Modal.getInstance(document.getElementById('editUserModal')).hide();
                // Update the table row with new data
                updateTableRow(userId, formData);
            } else {
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const fieldName = field.startsWith('edit_') ? field : 'edit_' + field;
                        showFieldError(fieldName, data.errors[field][0]);
                    });
                } else {
                    showAlert('Error updating user: ' + (data.message || 'Unknown error'), 'danger');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error updating user. Please try again.', 'danger');
        })
        .finally(() => {
            submitButton.disabled = false;
            spinner.classList.add('d-none');
        });
    });

    // Helper functions
    function showAlert(message, type) {
        const alertPlaceholder = document.getElementById('alertPlaceholder');
        const alert = document.createElement('div');
        alert.className = `alert alert-${type} alert-dismissible fade show`;
        alert.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        alertPlaceholder.appendChild(alert);

        // Auto dismiss after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }

    function showFieldError(fieldName, message) {
        const field = document.getElementById(fieldName);
        const feedback = field.nextElementSibling;

        field.classList.add('is-invalid');
        if (feedback && feedback.classList.contains('invalid-feedback')) {
            feedback.textContent = message;
        }
    }

    function clearValidation(formId) {
        const form = document.getElementById(formId);
        const invalidFields = form.querySelectorAll('.is-invalid');
        const feedbacks = form.querySelectorAll('.invalid-feedback');

        invalidFields.forEach(field => field.classList.remove('is-invalid'));
        feedbacks.forEach(feedback => feedback.textContent = '');
    }

    function updateTableRow(userId, formData) {
        const row = document.querySelector(`tr[data-user-id="${userId}"]`);
        if (row) {
            row.querySelector('.username').textContent = formData.get('username');
            row.querySelector('.email').textContent = formData.get('email');

            // Update button data attributes
            const editButton = row.querySelector('.edit-user');
            const deleteButton = row.querySelector('.delete-user');

            editButton.setAttribute('data-username', formData.get('username'));
            editButton.setAttribute('data-email', formData.get('email'));
            deleteButton.setAttribute('data-username', formData.get('username'));
        }
    }

    // Clear form when modals are closed
    document.getElementById('createUserModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('createUserForm').reset();
        clearValidation('createUserForm');
    });

    document.getElementById('editUserModal').addEventListener('hidden.bs.modal', function() {
        clearValidation('editUserForm');
    });
});
</script>

@include('admin.layouts.footer')
