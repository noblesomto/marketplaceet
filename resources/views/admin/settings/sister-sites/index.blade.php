@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Sister Sites Management</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/index">Home</a></li>
                <li class="breadcrumb-item">Settings</li>
                <li class="breadcrumb-item active">Sister Sites</li>
            </ol>
        </nav>
    </div>

    <section class="sister-sites-section">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="section-header mb-4">
                        <h2><i class="bi bi-flag"></i> Sister Sites</h2>
                        <p class="text-muted">Manage the other-country marketplace links shown in the site footer</p>
                    </div>

                    <div id="alertPlaceholder"></div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="input-group me-2" style="max-width: 300px;">
                            <span class="input-group-text"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control" id="searchInput" placeholder="Search sister sites...">
                        </div>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createSisterSiteModal">
                            <i class="bi bi-plus-circle"></i> Add Sister Site
                        </button>
                    </div>

                    <div class="card shadow-sm">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover" id="sisterSitesTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Flag</th>
                                            <th>Country</th>
                                            <th>URL</th>
                                            <th>Display Order</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($sisterSites as $site)
                                        <tr data-id="{{ $site->id }}">
                                            <td><img src="{{ asset($site->flag) }}" alt="{{ $site->country_name }}" style="width:32px;height:32px;border-radius:50%;object-fit:cover;"></td>
                                            <td class="fw-bold">{{ $site->country_name }}</td>
                                            <td><a href="{{ $site->url }}" target="_blank" rel="noopener">{{ $site->url }}</a></td>
                                            <td>{{ $site->display_order }}</td>
                                            <td>
                                                <span class="badge {{ $site->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $site->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="action-buttons">
                                                <button class="btn btn-sm btn-outline-primary edit-site me-1"
                                                        data-id="{{ $site->id }}"
                                                        data-country-name="{{ $site->country_name }}"
                                                        data-url="{{ $site->url }}"
                                                        data-display-order="{{ $site->display_order }}"
                                                        data-is-active="{{ $site->is_active ? '1' : '0' }}"
                                                        data-flag="{{ asset($site->flag) }}">
                                                    <i class="bi bi-pencil-square"></i> Edit
                                                </button>
                                                <button class="btn btn-sm btn-outline-{{ $site->is_active ? 'warning' : 'success' }} toggle-status me-1"
                                                        data-id="{{ $site->id }}">
                                                    <i class="bi bi-toggle-{{ $site->is_active ? 'on' : 'off' }}"></i>
                                                    {{ $site->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger delete-site"
                                                        data-id="{{ $site->id }}"
                                                        data-name="{{ $site->country_name }}">
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
        <div class="modal fade" id="createSisterSiteModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Sister Site</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="createSisterSiteForm">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Country Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="country_name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Site URL <span class="text-danger">*</span></label>
                                <input type="url" class="form-control" name="url" placeholder="https://marketplace.example.com" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Flag Image <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" name="flag" accept="image/*" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Display Order <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" name="display_order" value="0" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" name="is_active" value="1" checked>
                                <label class="form-check-label">Active</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Add Sister Site</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Modal -->
        <div class="modal fade" id="editSisterSiteModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Sister Site</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form id="editSisterSiteForm">
                        @csrf
                        <input type="hidden" name="id" id="edit_id">
                        <div class="modal-body">
                            <div class="mb-3">
                                <img id="edit_current_flag" src="" alt="" style="width:48px;height:48px;border-radius:50%;object-fit:cover;">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Country Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="country_name" id="edit_country_name" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Site URL <span class="text-danger">*</span></label>
                                <input type="url" class="form-control" name="url" id="edit_url" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Flag Image <small class="text-muted">(leave blank to keep current)</small></label>
                                <input type="file" class="form-control" name="flag" accept="image/*">
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
                            <button type="submit" class="btn btn-primary">Update Sister Site</button>
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
        $('#sisterSitesTable tbody tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });

    // Create sister site
    $('#createSisterSiteForm').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        $.ajax({
            url: '{{ route("admin.sister-sites.store") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                showAlert('success', response.message);
                $('#createSisterSiteModal').modal('hide');
                setTimeout(() => location.reload(), 1500);
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                let errorMsg = 'Error creating sister site: ';
                if (errors) {
                    errorMsg += Object.values(errors).flat().join(', ');
                }
                showAlert('danger', errorMsg);
            }
        });
    });

    // Edit sister site button
    $('.edit-site').on('click', function() {
        const id = $(this).data('id');

        $('#edit_id').val(id);
        $('#edit_country_name').val($(this).data('country-name'));
        $('#edit_url').val($(this).data('url'));
        $('#edit_display_order').val($(this).data('display-order'));
        $('#edit_is_active').prop('checked', $(this).data('is-active') == 1);
        $('#edit_current_flag').attr('src', $(this).data('flag'));

        $('#editSisterSiteModal').modal('show');
    });

    // Update sister site
    $('#editSisterSiteForm').on('submit', function(e) {
        e.preventDefault();
        const id = $('#edit_id').val();
        const formData = new FormData(this);

        $.ajax({
            url: `/admin/sister-sites/${id}`,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-HTTP-Method-Override': 'PUT'
            },
            success: function(response) {
                showAlert('success', response.message);
                $('#editSisterSiteModal').modal('hide');
                setTimeout(() => location.reload(), 1500);
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                let errorMsg = 'Error updating sister site: ';
                if (errors) {
                    errorMsg += Object.values(errors).flat().join(', ');
                }
                showAlert('danger', errorMsg);
            }
        });
    });

    // Toggle status
    $('.toggle-status').on('click', function() {
        const id = $(this).data('id');

        if (confirm('Are you sure you want to change the status?')) {
            $.ajax({
                url: `/admin/sister-sites/${id}/toggle`,
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

    // Delete sister site
    $('.delete-site').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');

        if (confirm(`Are you sure you want to delete "${name}"? This action cannot be undone.`)) {
            $.ajax({
                url: `/admin/sister-sites/${id}`,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    showAlert('success', response.message);
                    setTimeout(() => location.reload(), 1500);
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || 'Error deleting sister site';
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
