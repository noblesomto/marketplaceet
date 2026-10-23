@include('admin.layouts.header')
@include('admin.layouts.nav')

<!-- Include custom styles -->
<style>
    .management-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 20px rgba(0,0,0,0.08);
        margin-bottom: 2rem;
        transition: all 0.3s ease;
    }

    .management-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 25px rgba(0,0,0,0.12);
    }

    .card-header-custom {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px 12px 0 0 !important;
        padding: 1.25rem 1.5rem;
        font-weight: 600;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .card-body-custom {
        padding: 2rem 1.5rem;
    }

    .btn-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 8px;
        padding: 0.75rem 1.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-gradient:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }

    .form-control-modern {
        border: 2px solid #e1e5e9;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
        font-size: 0.95rem;
    }

    .form-control-modern:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .form-select-modern {
        border: 2px solid #e1e5e9;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
    }

    .form-select-modern:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .permission-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
        margin: 1rem 0;
    }

    .permission-item {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 8px;
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .permission-item:hover {
        background: #e3f2fd;
        border-color: #2196f3;
    }

    .form-check-input-custom {
        width: 1.2em;
        height: 1.2em;
        margin-right: 0.5rem;
    }

    .role-section {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border: 1px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .role-section:hover {
        box-shadow: 0 2px 15px rgba(0,0,0,0.08);
    }

    .role-title {
        color: #495057;
        font-size: 1.2rem;
        font-weight: 600;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .role-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .admin-table-modern {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 20px rgba(0,0,0,0.08);
    }

    .admin-table-modern thead th {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        padding: 1rem 1.25rem;
        font-weight: 600;
        font-size: 0.95rem;
    }

    .admin-table-modern tbody td {
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e9ecef;
        vertical-align: middle;
    }

    .admin-table-modern tbody tr:hover {
        background: #f8f9fa;
    }

    .badge-role {
        background: #e3f2fd;
        color: #1976d2;
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 500;
        margin-right: 0.5rem;
        display: inline-block;
        margin-bottom: 0.25rem;
    }

    .alert-modern {
        border: none;
        border-radius: 12px;
        padding: 1rem 1.5rem;
        margin-bottom: 2rem;
        font-weight: 500;
    }

    .section-title {
        color: #2c3e50;
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .section-subtitle {
        color: #6c757d;
        font-size: 1.1rem;
        margin-bottom: 2rem;
    }

    .icon-wrapper {
        width: 40px;
        height: 40px;
        background: rgba(255,255,255,0.2);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
    }

    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        text-align: center;
    }

    .stats-number {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .loading-spinner {
        display: none;
        margin-left: 0.5rem;
    }

    .btn-loading .loading-spinner {
        display: inline-block;
    }
</style>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>{{ $title }}</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/dashboard">Home</a></li>
                <li class="breadcrumb-item active">Role & Permission Management</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="container-fluid">
            <!-- Page Header -->
            <div class="row mb-4">
                <div class="col-12">
                    <h2 class="section-title">Role & Permission Management</h2>
                    <p class="section-subtitle">Manage system roles, permissions, and user access controls</p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-number">{{ $roles->count() }}</div>
                        <div>Total Roles</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-number">{{ $permissions->count() }}</div>
                        <div>Total Permissions</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-number">{{ $admins->count() }}</div>
                        <div>Total Admins</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-card">
                        <div class="stats-number">{{ $admins->filter(function($admin) { return $admin->roles->isNotEmpty(); })->count() }}</div>
                        <div>Assigned Admins</div>
                    </div>
                </div>
            </div>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-modern">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                </div>
            @endif

            {{-- Quick Actions Row --}}
            <div class="row mb-4">
                <!-- Add New Role -->
                <div class="col-md-6">
                    <div class="card management-card">
                        <div class="card-header card-header-custom">
                            <div class="d-flex align-items-center">
                                <div class="icon-wrapper">
                                    <i class="fas fa-user-tag"></i>
                                </div>
                                Add New Role
                            </div>
                        </div>
                        <div class="card-body card-body-custom">
                            <form method="POST" action="{{ route('admin.roles.store') }}" class="role-form">
                                @csrf
                                <div class="mb-3">
                                    <label for="role-name" class="form-label fw-semibold">Role Name</label>
                                    <input type="text"
                                           name="name"
                                           id="role-name"
                                           class="form-control form-control-modern"
                                           placeholder="Enter role name (e.g., Editor, Manager)"
                                           required>
                                </div>
                                <button type="submit" class="btn btn-gradient w-100">
                                    <i class="fas fa-plus me-2"></i>Create Role
                                    <div class="spinner-border spinner-border-sm loading-spinner" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Add New Permission -->
                <div class="col-md-6">
                    <div class="card management-card">
                        <div class="card-header card-header-custom">
                            <div class="d-flex align-items-center">
                                <div class="icon-wrapper">
                                    <i class="fas fa-key"></i>
                                </div>
                                Add New Permission
                            </div>
                        </div>
                        <div class="card-body card-body-custom">
                            <form method="POST" action="{{ route('admin.permissions.store') }}" class="permission-form">
                                @csrf
                                <div class="mb-3">
                                    <label for="permission-name" class="form-label fw-semibold">Permission Name</label>
                                    <input type="text"
                                           name="name"
                                           id="permission-name"
                                           class="form-control form-control-modern"
                                           placeholder="Enter permission name (e.g., create-posts, manage-users)"
                                           required>
                                </div>
                                <button type="submit" class="btn btn-gradient w-100">
                                    <i class="fas fa-plus me-2"></i>Create Permission
                                    <div class="spinner-border spinner-border-sm loading-spinner" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Role Permissions Management --}}
            <div class="card management-card">
                <div class="card-header card-header-custom">
                    <div class="d-flex align-items-center">
                        <div class="icon-wrapper">
                            <i class="fas fa-cogs"></i>
                        </div>
                        Assign Permissions to Roles
                    </div>
                    <small>Configure what each role can do in the system</small>
                </div>
                <div class="card-body card-body-custom">
                    @forelse($roles as $role)
                        <div class="role-section">
                            <form method="POST" action="{{ route('admin.roles.assign', $role->id) }}" class="role-permission-form">
                                @csrf
                                <div class="role-title">
                                    <i class="fas fa-user-shield text-primary"></i>
                                    {{ ucfirst($role->name) }}
                                    <span class="role-badge">{{ $role->permissions->count() }} permissions</span>
                                </div>

                                <div class="permission-grid">
                                    @foreach($permissions as $perm)
                                        <div class="permission-item">
                                            <div class="form-check">
                                                <input class="form-check-input form-check-input-custom"
                                                       type="checkbox"
                                                       name="permissions[]"
                                                       value="{{ $perm->name }}"
                                                       id="perm{{ $role->id }}-{{ $perm->id }}"
                                                       {{ $role->permissions->contains($perm->id) ? 'checked' : '' }}>
                                                <label class="form-check-label fw-medium" for="perm{{ $role->id }}-{{ $perm->id }}">
                                                    {{ ucfirst(str_replace('-', ' ', $perm->name)) }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <div>
                                        <button type="button" class="btn btn-outline-secondary btn-sm select-all" data-role="{{ $role->id }}">
                                            <i class="fas fa-check-double me-1"></i>Select All
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm clear-all ms-2" data-role="{{ $role->id }}">
                                            <i class="fas fa-times me-1"></i>Clear All
                                        </button>
                                    </div>
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-save me-2"></i>Update Permissions
                                        <div class="spinner-border spinner-border-sm loading-spinner" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </button>
                                </div>
                            </form>
                        </div>
                    @empty
                        <div class="text-center py-4">
                            <i class="fas fa-user-plus fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No roles created yet</h5>
                            <p class="text-muted">Create your first role using the form above</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Admin Role Assignment --}}
            <div class="card management-card">
                <div class="card-header card-header-custom">
                    <div class="d-flex align-items-center">
                        <div class="icon-wrapper">
                            <i class="fas fa-users-cog"></i>
                        </div>
                        Assign Roles to Admins
                    </div>
                    <small>Control admin access levels by assigning roles</small>
                </div>
                <div class="card-body card-body-custom p-0">
                    <div class="table-responsive">
                        <table class="table admin-table-modern mb-0">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-user me-2"></i>Admin</th>
                                    <th><i class="fas fa-tags me-2"></i>Current Roles</th>
                                    <th><i class="fas fa-edit me-2"></i>Manage Roles</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($admins as $admin)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3">
                                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 40px; height: 40px;">
                                                        {{ strtoupper(substr($admin->username, 0, 2)) }}
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $admin->username }}</div>
                                                    <small class="text-muted">Admin ID: {{ $admin->id }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($admin->roles->isNotEmpty())
                                                @foreach($admin->roles as $r)
                                                    <span class="badge-role">{{ ucfirst($r->name) }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted fst-italic">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>No roles assigned
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <form method="POST" action="{{ route('admin.admins.assign', $admin->id) }}" class="admin-role-form">
                                                @csrf
                                                <div class="input-group">
                                                    <select name="roles[]" class="form-select form-select-modern" multiple style="min-height: 45px;">
                                                        @foreach($roles as $role)
                                                            <option value="{{ $role->name }}"
                                                                {{ $admin->roles->contains($role->id) ? 'selected' : '' }}>
                                                                {{ ucfirst($role->name) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fas fa-sync-alt me-1"></i>Update
                                                        <div class="spinner-border spinner-border-sm loading-spinner" role="status">
                                                            <span class="visually-hidden">Loading...</span>
                                                        </div>
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4">
                                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">No admins found</h5>
                                            <p class="text-muted">Create admin accounts to assign roles</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Include custom JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Loading states for forms
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const button = form.querySelector('button[type="submit"]');
            if (button) {
                button.classList.add('btn-loading');
                button.disabled = true;
            }
        });
    });

    // Select All / Clear All functionality
    document.querySelectorAll('.select-all').forEach(button => {
        button.addEventListener('click', function() {
            const roleId = this.dataset.role;
            const checkboxes = document.querySelectorAll(`input[id*="perm${roleId}-"]`);
            checkboxes.forEach(checkbox => checkbox.checked = true);
        });
    });

    document.querySelectorAll('.clear-all').forEach(button => {
        button.addEventListener('click', function() {
            const roleId = this.dataset.role;
            const checkboxes = document.querySelectorAll(`input[id*="perm${roleId}-"]`);
            checkboxes.forEach(checkbox => checkbox.checked = false);
        });
    });

    // Enhanced multi-select for admin roles
    document.querySelectorAll('select[multiple]').forEach(select => {
        select.addEventListener('change', function() {
            const selectedCount = this.selectedOptions.length;
            const placeholder = selectedCount > 0 ? `${selectedCount} role(s) selected` : 'Select roles...';
            // You can add a custom placeholder display if needed
        });
    });

    // Auto-hide flash messages
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    }, 5000);
});
</script>

@include('admin.layouts.footer')
