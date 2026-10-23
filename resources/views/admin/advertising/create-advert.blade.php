@include('admin.layouts.header')
@include('admin.layouts.nav')

<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<script src="{{ asset('backend/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('backend/js/jquery.min.js') }}"></script>
<script src="{{ asset('tinymce/tinymce.min.js') }}"></script>
<script src="{{ asset('tinymce/jquery.tinymce.min.js') }}"></script>

<style>
.action-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}
.action-buttons .btn {
    padding: 4px 8px;
    font-size: 12px;
}
.status-badge {
    font-size: 11px;
    padding: 4px 8px;
}
.advert-image {
    border-radius: 4px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.card-title {
    color: #2c3e50;
    font-weight: 600;
}
.section-divider {
    border-top: 2px solid #e9ecef;
    margin: 30px 0;
}
</style>

<main id="main" class="main">
    <div class="pagetitle">
        <h1><i class="fas fa-bullhorn me-2"></i>Advertising Management</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Advertising</li>
            </ol>
        </nav>
    </div>
@if(session('status'))
    <div class="alert alert-{{ session('status')['type'] }} alert-dismissible fade show" role="alert">
        <i class="fas fa-info-circle me-2"></i>
        {{ session('status')['text'] }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
    <!-- Adverts List Section -->
    <section class="section">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0 text-white">
                            <i class="fas fa-list me-2"></i>Current Advertisements
                        </h5>
                    </div>
                    <div class="card-body mt-4">
                        @if(count($adverts) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" class="text-center">Preview</th>
                                            <th scope="col">Company</th>
                                            <th scope="col">URL</th>
                                            <th scope="col" class="text-center">Duration</th>
                                            <th scope="col" class="text-center">Start Date</th>
                                            <th scope="col" class="text-center">Type</th>
                                            <th scope="col" class="text-center">Status</th>
                                            <th scope="col" class="text-center">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($adverts as $index => $row)
                                            <tr>
                                                <td class="text-center">
                                                    <div class="d-flex gap-1 justify-content-center align-items-end">
                                                        @if($row->image)
                                                            <div>
                                                                <div class="text-muted" style="font-size:10px;">Desktop</div>
                                                                <img width="70" height="50"
                                                                     class="img-fluid advert-image object-fit-cover"
                                                                     src="{{ asset('uploads/advertising/'.$row->image) }}"
                                                                     alt="{{ $row->company }} desktop">
                                                            </div>
                                                        @endif
                                                        @if($row->mobile_image)
                                                            <div>
                                                                <div class="text-muted" style="font-size:10px;">Mobile</div>
                                                                <img width="50" height="50"
                                                                     class="img-fluid advert-image object-fit-cover"
                                                                     src="{{ asset('uploads/advertising/'.$row->mobile_image) }}"
                                                                     alt="{{ $row->company }} mobile">
                                                            </div>
                                                        @endif
                                                        @if(!$row->image && !$row->mobile_image)
                                                            <span class="text-muted small">No image</span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <strong>{{ $row->company }}</strong>
                                                </td>
                                                <td>
                                                    <a href="{{ $row->url }}" target="_blank" class="text-decoration-none">
                                                        <i class="fas fa-external-link-alt me-1"></i>
                                                        {{ Str::limit($row->url, 30) }}
                                                    </a>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-info">{{ $row->duration }} Days</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="">Started: {{ date('M j, Y', strtotime($row->start_date)) }}</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge {{ $row->type == 'banner' ? 'bg-success' : 'bg-warning' }}">
                                                        {{ ucfirst($row->type) }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge status-badge {{ $row->status == 'active' ? 'bg-success' : 'bg-secondary' }}">
                                                        {{ ucfirst($row->status) }}
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="action-buttons">
                                                        <button type="button"
                                                                class="btn btn-outline-primary btn-sm"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#editModal{{ $row->advert_id }}"
                                                                data-bs-toggle="tooltip"
                                                                title="Edit Advertisement">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <form action="{{ route('admin.delete.advert', $row->advert_id) }}" method="POST" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                    class="btn btn-outline-danger btn-sm"
                                                                    onclick="return confirm('Are you sure you want to delete this advertisement? This action cannot be undone.');"
                                                                    data-bs-toggle="tooltip"
                                                                    title="Delete Advertisement">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>

                                            <!-- Edit Modal for each advert -->
                                            <div class="modal fade" id="editModal{{ $row->advert_id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $row->advert_id }}" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-primary text-white">
                                                            <h5 class="modal-title" id="editModalLabel{{ $row->advert_id }}">
                                                                <i class="fas fa-edit me-2"></i>Edit Advertisement - {{ $row->company }}
                                                            </h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form action="/admin/update-advert/{{ $row->advert_id }}" method="POST" enctype="multipart/form-data">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-body">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Company Name</label>
                                                                            <input type="text" name="company" class="form-control"
                                                                                   value="{{ $row->company }}" required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Advertisement URL</label>
                                                                            <input type="url" name="url" class="form-control"
                                                                                   value="{{ $row->url }}" required>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Duration</label>
                                                                            <select name="duration" class="form-select" required>
                                                                                <option value="7" {{ $row->duration == 7 ? 'selected' : '' }}>7 Days</option>
                                                                                <option value="14" {{ $row->duration == 14 ? 'selected' : '' }}>14 Days</option>
                                                                                <option value="30" {{ $row->duration == 30 ? 'selected' : '' }}>30 Days</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Advertisement Type</label>
                                                                            <select name="type" class="form-select" required>
                                                                                <option value="banner" {{ $row->type == 'banner' ? 'selected' : '' }}>Leader Banner</option>
                                                                                <option value="sidebar" {{ $row->type == 'sidebar' ? 'selected' : '' }}>Side Bar</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Status</label>
                                                                            <select name="status" class="form-select" required>
                                                                                <option value="active" {{ $row->status == 'active' ? 'selected' : '' }}>Active</option>
                                                                                <option value="inactive" {{ $row->status == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                                                <option value="pending" {{ $row->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Desktop Image</label>
                                                                            <div class="mb-2">
                                                                                <img src="{{ asset('uploads/advertising/'.$row->image) }}"
                                                                                     class="img-fluid advert-image"
                                                                                     style="max-height: 120px;"
                                                                                     alt="{{ $row->company }}">
                                                                            </div>
                                                                            <label class="form-label">Replace Desktop Image (Optional)</label>
                                                                            <input type="file" name="advert_image" class="form-control" accept="image/*">
                                                                            <small class="form-text text-muted">Wide landscape format (e.g. 1200×200px)</small>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="mb-3">
                                                                            <label class="form-label">Mobile Image</label>
                                                                            <div class="mb-2">
                                                                                @if($row->mobile_image)
                                                                                    <img src="{{ asset('uploads/advertising/'.$row->mobile_image) }}"
                                                                                         class="img-fluid advert-image"
                                                                                         style="max-height: 120px;"
                                                                                         alt="{{ $row->company }} Mobile">
                                                                                @else
                                                                                    <span class="text-muted small">No mobile image set — desktop image will be used</span>
                                                                                @endif
                                                                            </div>
                                                                            <label class="form-label">{{ $row->mobile_image ? 'Replace Mobile Image (Optional)' : 'Upload Mobile Image (Optional)' }}</label>
                                                                            <input type="file" name="advert_mobile_image" class="form-control" accept="image/*">
                                                                            <small class="form-text text-muted">Square or portrait format (e.g. 600×300px)</small>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                                <button type="submit" class="btn btn-primary">
                                                                    <i class="fas fa-save me-1"></i>Update Advertisement
                                                                </button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-bullhorn text-muted" style="font-size: 3rem;"></i>
                                <h5 class="mt-3 text-muted">No Advertisements Found</h5>
                                <p class="text-muted">Create your first advertisement below to get started.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="section-divider"></div>

    <!-- Create New Advert Section -->
    <section class="section">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0 text-white">
                            <i class="fas fa-plus-circle me-2"></i>Create New Advertisement
                        </h5>
                    </div>
                    <div class="card-body mt-5">
                        @if(session('status'))
                            <div class="alert alert-{{ session('status')['type'] }} alert-dismissible fade show" role="alert">
                                <i class="fas fa-info-circle me-2"></i>
                                {{ session('status')['text'] }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="/admin/create-advert" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-building me-1"></i>Company Name <span class="text-danger">*</span>
                                        </label>
                                        @if ($errors->has('company'))
                                            <div class="text-danger small mb-2">{{ $errors->first('company') }}</div>
                                        @endif
                                        <input type="text" name="company" class="form-control @error('company') is-invalid @enderror"
                                               placeholder="Enter company name" value="{{ old('company') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-link me-1"></i>Advertisement URL <span class="text-danger">*</span>
                                        </label>
                                        @if ($errors->has('url'))
                                            <div class="text-danger small mb-2">{{ $errors->first('url') }}</div>
                                        @endif
                                        <input type="url" name="url" class="form-control @error('url') is-invalid @enderror"
                                               placeholder="https://example.com" value="{{ old('url') }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-calendar-alt me-1"></i>Duration <span class="text-danger">*</span>
                                        </label>
                                        @if ($errors->has('duration'))
                                            <div class="text-danger small mb-2">{{ $errors->first('duration') }}</div>
                                        @endif
                                        <select name="duration" class="form-select @error('duration') is-invalid @enderror" required>
                                            <option value="">Select Duration</option>
                                            <option value="7" {{ old('duration') == '7' ? 'selected' : '' }}>7 Days</option>
                                            <option value="14" {{ old('duration') == '14' ? 'selected' : '' }}>14 Days</option>
                                            <option value="30" {{ old('duration') == '30' ? 'selected' : '' }}>30 Days</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-expand-arrows-alt me-1"></i>Advertisement Type <span class="text-danger">*</span>
                                        </label>
                                        @if ($errors->has('type'))
                                            <div class="text-danger small mb-2">{{ $errors->first('type') }}</div>
                                        @endif
                                        <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                            <option value="">Select Type</option>
                                            <option value="banner" {{ old('type') == 'banner' ? 'selected' : '' }}>Leader Banner</option>
                                            <option value="sidebar" {{ old('type') == 'sidebar' ? 'selected' : '' }}>Side Bar</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="fas fa-toggle-on me-1"></i>Status
                                        </label>
                                        <select name="status" class="form-select">
                                            <option value="pending" selected>Pending</option>
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="fas fa-desktop me-1"></i>Desktop Image <span class="text-muted">(Optional)</span>
                                    </label>
                                    @if ($errors->has('advert_image'))
                                        <div class="text-danger small mb-2">{{ $errors->first('advert_image') }}</div>
                                    @endif
                                    <input type="file" name="advert_image" class="form-control @error('advert_image') is-invalid @enderror"
                                           accept="image/*">
                                    <small class="form-text text-muted">Wide landscape format (e.g. 1200×200px). Max 2MB</small>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="fas fa-mobile-alt me-1"></i>Mobile Image <span class="text-muted">(Optional)</span>
                                    </label>
                                    @if ($errors->has('advert_mobile_image'))
                                        <div class="text-danger small mb-2">{{ $errors->first('advert_mobile_image') }}</div>
                                    @endif
                                    <input type="file" name="advert_mobile_image" class="form-control @error('advert_mobile_image') is-invalid @enderror"
                                           accept="image/*">
                                    <small class="form-text text-muted">Square or portrait format (e.g. 600×300px). If omitted, desktop image is used. Max 2MB</small>
                                </div>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-plus me-1"></i>Create Advertisement
                                </button>
                                <button type="reset" class="btn btn-outline-secondary">
                                    <i class="fas fa-undo me-1"></i>Reset Form
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);

    // Image preview for file inputs
    $('input[type="file"][accept="image/*"]').change(function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = $(this).siblings('.image-preview');
                if (preview.length) {
                    preview.attr('src', e.target.result);
                }
            }.bind(this);
            reader.readAsDataURL(file);
        }
    });
});
</script>

@include('admin.layouts.footer')
