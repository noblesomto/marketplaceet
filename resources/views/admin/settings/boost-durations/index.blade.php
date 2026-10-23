@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Boost Durations Management</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/index">Home</a></li>
                <li class="breadcrumb-item">Settings</li>
                <li class="breadcrumb-item active">Boost Durations</li>
            </ol>
        </nav>
    </div>

    <section class="boost-durations-section">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="section-header mb-4">
                        <h2><i class="bi bi-clock-history"></i> Boost Durations</h2>
                        <p class="text-muted">Manage boost durations and discount percentages</p>
                    </div>

                    <div id="alertPlaceholder"></div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="input-group me-2" style="max-width: 300px;">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="searchInput" placeholder="Search durations...">
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createDurationModal">
                            <i class="bi bi-plus-circle"></i> Create New Duration
                        </button>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="durationsTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Days</th>
                                            <th>Label</th>
                                            <th>Discount (%)</th>
                                            <th>Display Order</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($durations as $duration)
                                        <tr data-id="{{ $duration->id }}">
                                            <td class="fw-bold">{{ $duration->days }} Days</td>
                                            <td>{{ $duration->label }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ $duration->discount_percentage }}%</span>
                                            </td>
                                            <td>{{ $duration->display_order }}</td>
                                            <td>
                                                <span class="badge {{ $duration->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $duration->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="action-buttons">
                                                <button class="btn btn-sm btn-outline-primary edit-duration me-1"
                                                        data-id="{{ $duration->id }}"
                                                        data-days="{{ $duration->days }}"
                                                        data-label="{{ $duration->label }}"
                                                        data-discount="{{ $duration->discount_percentage }}"
                                                        data-display-order="{{ $duration->display_order }}"
                                                        data-is-active="{{ $duration->is_active ? '1' : '0' }}">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </button>
                                                <button class="btn btn-sm btn-outline-{{ $duration->is_active ? 'warning' : 'success' }} toggle-status me-1"
                                                        data-id="{{ $duration->id }}">
                                                    <i class="bi bi-toggle-{{ $duration->is_active ? 'on' : 'off' }}"></i>
                                                    {{ $duration->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger delete-duration"
                                                        data-id="{{ $duration->id }}"
                                                        data-days="{{ $duration->days }}">
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
        <div class="modal fade" id="createDurationModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Create New Duration</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="createDurationForm">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Days <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="days" required min="1">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Label <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="label" required placeholder="e.g., 7 Days">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Discount Percentage <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" name="discount_percentage" required min="0" max="100">
                                <small class="text-muted">Enter discount percentage (0-100)</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Display Order <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="display_order" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" name="is_active" value="1" checked>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Create Duration</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal fade" id="editDurationModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Duration</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="editDurationForm">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="id" id="edit_id">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Days <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="days" id="edit_days" required min="1">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Label <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="label" id="edit_label" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Discount Percentage <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" name="discount_percentage" id="edit_discount" required min="0" max="100">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Display Order <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="display_order" id="edit_display_order" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" name="is_active" id="edit_is_active" value="1">
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Update Duration</button>
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
    $('#searchInput').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $('#durationsTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    $('#createDurationForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        $.ajax({
            url: '{{ route("admin.boost-durations.store") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                showAlert('success', response.message);
                $('#createDurationModal').modal('hide');
                setTimeout(() => location.reload(), 1500);
            },
            error: function(xhr) {
                showAlert('danger', 'Error creating duration');
            }
        });
    });

    $('.edit-duration').on('click', function() {
        $('#edit_id').val($(this).data('id'));
        $('#edit_days').val($(this).data('days'));
        $('#edit_label').val($(this).data('label'));
        $('#edit_discount').val($(this).data('discount'));
        $('#edit_display_order').val($(this).data('display-order'));
        $('#edit_is_active').prop('checked', $(this).data('is-active') == 1);
        $('#editDurationModal').modal('show');
    });

    $('#editDurationForm').on('submit', function(e) {
        e.preventDefault();
        const id = $('#edit_id').val();
        const formData = new FormData(this);

        $.ajax({
            url: `/admin/boost-settings/durations/${id}`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {'X-HTTP-Method-Override': 'PUT'},
            success: function(response) {
                showAlert('success', response.message);
                $('#editDurationModal').modal('hide');
                setTimeout(() => location.reload(), 1500);
            },
            error: function() {
                showAlert('danger', 'Error updating duration');
            }
        });
    });

    $('.toggle-status').on('click', function() {
        const id = $(this).data('id');
        if (confirm('Are you sure you want to change the status?')) {
            $.ajax({
                url: `/admin/boost-settings/durations/${id}/toggle`,
                method: 'POST',
                data: {_token: '{{ csrf_token() }}'},
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

    $('.delete-duration').on('click', function() {
        const id = $(this).data('id');
        const days = $(this).data('days');
        if (confirm(`Are you sure you want to delete "${days} Days"? This action cannot be undone.`)) {
            $.ajax({
                url: `/admin/boost-settings/durations/${id}`,
                method: 'DELETE',
                data: {_token: '{{ csrf_token() }}'},
                success: function(response) {
                    showAlert('success', response.message);
                    setTimeout(() => location.reload(), 1500);
                },
                error: function(xhr) {
                    showAlert('danger', xhr.responseJSON?.message || 'Error deleting duration');
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
