@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Boost Types Management</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/index">Home</a></li>
                <li class="breadcrumb-item">Settings</li>
                <li class="breadcrumb-item active">Boost Types</li>
            </ol>
        </nav>
    </div>

    <section class="boost-types-section">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="section-header mb-4">
                        <h2><i class="bi bi-rocket-takeoff"></i> Boost Types</h2>
                        <p class="text-muted">Manage boost types and pricing</p>
                    </div>

                    <div id="alertPlaceholder"></div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="input-group me-2" style="max-width: 300px;">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="searchInput" placeholder="Search boost types...">
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBoostTypeModal">
                            <i class="bi bi-plus-circle"></i> Create New Boost Type
                        </button>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="boostTypesTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Name</th>
                                            <th>Daily Rate (Br)</th>
                                            <th>Display Order</th>
                                            <th>Status</th>
                                            <th>Description</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($boostTypes as $type)
                                        <tr data-id="{{ $type->id }}">
                                            <td class="fw-bold">{{ $type->name }}</td>
                                            <td>Br {{ number_format($type->daily_rate, 2) }}</td>
                                            <td>{{ $type->display_order }}</td>
                                            <td>
                                                <span class="badge {{ $type->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $type->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td>{{ Str::limit($type->description, 50) }}</td>
                                            <td class="action-buttons">
                                                <button class="btn btn-sm btn-outline-primary edit-type me-1"
                                                        data-id="{{ $type->id }}"
                                                        data-name="{{ $type->name }}"
                                                        data-daily-rate="{{ $type->daily_rate }}"
                                                        data-display-order="{{ $type->display_order }}"
                                                        data-is-active="{{ $type->is_active ? '1' : '0' }}"
                                                        data-description="{{ $type->description }}">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </button>
                                                <button class="btn btn-sm btn-outline-{{ $type->is_active ? 'warning' : 'success' }} toggle-status me-1"
                                                        data-id="{{ $type->id }}">
                                                    <i class="bi bi-toggle-{{ $type->is_active ? 'on' : 'off' }}"></i>
                                                    {{ $type->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger delete-type"
                                                        data-id="{{ $type->id }}"
                                                        data-name="{{ $type->name }}">
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

        <!-- Create Modal -->
        <div class="modal fade" id="createBoostTypeModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create New Boost Type</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="createBoostTypeForm">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Daily Rate (Br) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" name="daily_rate" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Display Order <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="display_order" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" rows="3"></textarea>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" name="is_active" value="1" checked>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Create Boost Type</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal fade" id="editBoostTypeModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Boost Type</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="editBoostTypeForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" id="edit_id">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="name" id="edit_name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Daily Rate (Br) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" name="daily_rate" id="edit_daily_rate" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Display Order <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="display_order" id="edit_display_order" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" name="description" id="edit_description" rows="3"></textarea>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" name="is_active" id="edit_is_active" value="1">
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Update Boost Type</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</main>

@include('admin.layouts.footer')

<script>
$(document).ready(function() {
    // Search functionality
    $('#searchInput').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#boostTypesTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // Create boost type
    $('#createBoostTypeForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        $.ajax({
            url: '{{ route("admin.boost-types.store") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                showAlert('success', response.message);
                $('#createBoostTypeModal').modal('hide');
                setTimeout(() => location.reload(), 1500);
            },
            error: function(xhr) {
                const errors = xhr.responseJSON.errors;
                let errorMsg = 'Error creating boost type: ';
                if (errors) {
                    errorMsg += Object.values(errors).flat().join(', ');
                }
                showAlert('danger', errorMsg);
            }
        });
    });

    // Edit boost type button
    $('.edit-type').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const dailyRate = $(this).data('daily-rate');
        const displayOrder = $(this).data('display-order');
        const isActive = $(this).data('is-active');
        const description = $(this).data('description');

        $('#edit_id').val(id);
        $('#edit_name').val(name);
        $('#edit_daily_rate').val(dailyRate);
        $('#edit_display_order').val(displayOrder);
        $('#edit_description').val(description);
        $('#edit_is_active').prop('checked', isActive == 1);

        $('#editBoostTypeModal').modal('show');
    });

    // Update boost type
    $('#editBoostTypeForm').on('submit', function(e) {
        e.preventDefault();
        const id = $('#edit_id').val();
        const formData = new FormData(this);

        $.ajax({
            url: `/admin/boost-settings/types/${id}`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-HTTP-Method-Override': 'PUT'
            },
            success: function(response) {
                showAlert('success', response.message);
                $('#editBoostTypeModal').modal('hide');
                setTimeout(() => location.reload(), 1500);
            },
            error: function(xhr) {
                showAlert('danger', 'Error updating boost type');
            }
        });
    });

    // Toggle status
    $('.toggle-status').on('click', function() {
        const id = $(this).data('id');

        if (confirm('Are you sure you want to change the status?')) {
            $.ajax({
                url: `/admin/boost-settings/types/${id}/toggle`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    showAlert('success', response.message);
                    setTimeout(() => location.reload(), 1500);
                },
                error: function() {
                    showAlert('danger', 'Error toggling status');
                }
            });
        }
    });

    // Delete boost type
    $('.delete-type').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');

        if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
            $.ajax({
                url: `/admin/boost-settings/types/${id}`,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    showAlert('success', response.message);
                    setTimeout(() => location.reload(), 1500);
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Error deleting boost type';
                    showAlert('danger', message);
                }
            });
        }
    });

    function showAlert(type, message) {
        const alert = `
            <div class="alert alert-${type} alert-dismissible fade show">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        $('#alertPlaceholder').html(alert);
        setTimeout(() => $('.alert').alert('close'), 3000);
    }
});
</script>
