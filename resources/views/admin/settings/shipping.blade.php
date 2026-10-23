@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">
    <div class="pagetitle">
        <div class="d-flex justify-content-between align-items-center">
            <h1>Shipping Management</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">Shipping Companies</li>
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
                            <i class="bi bi-truck me-2"></i>Shipping Companies
                        </h5>
                    </div>

                    <div class="card-body">
                        @if(session('status'))
                            <div class="alert alert-{{ session('status')['type'] }} alert-dismissible fade show">
                                <div class="d-flex align-items-center">
                                    @if(session('status')['type'] === 'success')
                                        <i class="bi bi-check-circle-fill me-2"></i>
                                    @else
                                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    @endif
                                    <div>{{ session('status')['text'] }}</div>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" width="80px">Logo</th>
                                        <th scope="col">Company</th>
                                        <th scope="col">Details</th>
                                        <th scope="col">Credentials</th>
                                        <th scope="col" class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($shippings as $row)
                                        <tr>
                                            <td>
                                                <img src="{{ asset('uploads/shipping/'.$row->logo) }}"
                                                     class="rounded border"
                                                     width="60"
                                                     height="60"
                                                     alt="{{ $row->company }}"
                                                     style="object-fit: contain;">
                                            </td>
                                            <td>
                                                <h6 class="mb-0 fw-semibold">{{ $row->company }}</h6>
                                                <small class="text-muted">Max {{ $row->weight }} Kg</small>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    <div class="fw-bold text-success">Br {{ number_format($row->price, 2) }}</div>
                                                    <div class="text-muted">{{ $row->description }}</div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="small">
                                                    <div><span class="fw-medium">User:</span> {{ $row->username }}</div>
                                                    <div><span class="fw-medium">Pass:</span> {{ $row->show_password }}</div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex gap-2 justify-content-center">
                                                    <button class="btn btn-sm btn-outline-primary"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#editShippingModal"
                                                            data-id="{{ $row->id }}"
                                                            data-company="{{ $row->company }}"
                                                            data-weight="{{ $row->weight }}"
                                                            data-price="{{ $row->price }}"
                                                            data-description="{{ $row->description }}"
                                                            data-username="{{ $row->username }}"
                                                            data-password="{{ $row->show_password }}"
                                                            title="Edit Shipping">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <form action="/settings/delete-shipping/{{ $row->id }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                                                onclick="return confirm('Are you sure you want to delete this shipping company?')"
                                                                title="Delete Shipping">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
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
    </section>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title text-white mb-0">
                            <i class="bi bi-plus-circle me-2"></i>Add New Shipping Company
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="/settings/setup-shipping" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row mb-3">
                                <label class="col-sm-3 col-form-label">Company Name</label>
                                <div class="col-sm-9">
                                    <input type="text" name="company" class="form-control"
                                           value="{{ old('company') }}" required>
                                    @error('company')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-3 col-form-label">Max Weight (Kg)</label>
                                <div class="col-sm-9">
                                    <input type="number" name="weight" class="form-control"
                                           value="{{ old('weight') }}" required>
                                    @error('weight')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-3 col-form-label">Price (Br)</label>
                                <div class="col-sm-9">
                                    <input type="number" name="price" class="form-control"
                                           value="{{ old('price') }}" required>
                                    @error('price')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-3 col-form-label">Description</label>
                                <div class="col-sm-9">
                                    <input type="text" name="description" class="form-control"
                                           value="{{ old('description') }}" required>
                                    @error('description')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-3 col-form-label">Company Logo</label>
                                <div class="col-sm-9">
                                    <input type="file" name="logo" class="form-control" required>
                                    @error('logo')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-3 col-form-label">Username</label>
                                <div class="col-sm-9">
                                    <input type="text" name="username" class="form-control"
                                           value="{{ old('username') }}" required>
                                    @error('username')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label class="col-sm-3 col-form-label">Password</label>
                                <div class="col-sm-9">
                                    <input type="text" name="password" class="form-control"
                                           value="{{ old('password') }}" required>
                                    @error('password')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-sm-12 text-end">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bi bi-save me-1"></i> Save Shipping Company
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<!-- Edit Shipping Modal -->
<div class="modal fade" id="editShippingModal" tabindex="-1" aria-labelledby="editShippingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title text-white" id="editShippingModalLabel">
                    <i class="bi bi-pencil me-2"></i>Edit Shipping Company
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editShippingForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Company Name</label>
                        <div class="col-sm-9">
                            <input type="text" name="company" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Max Weight (Kg)</label>
                        <div class="col-sm-9">
                            <input type="number" name="weight" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Price (Br)</label>
                        <div class="col-sm-9">
                            <input type="number" name="price" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Description</label>
                        <div class="col-sm-9">
                            <input type="text" name="description" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Current Logo</label>
                        <div class="col-sm-9">
                            <img id="currentLogo" src="" class="img-thumbnail" width="100" style="display: none;">
                            <div class="form-text">Leave blank to keep current logo</div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">New Logo</label>
                        <div class="col-sm-9">
                            <input type="file" name="logo" class="form-control">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Username</label>
                        <div class="col-sm-9">
                            <input type="text" name="username" class="form-control" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-sm-3 col-form-label">Password</label>
                        <div class="col-sm-9">
                            <input type="text" name="password" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Initialize edit modal with data
    document.addEventListener('DOMContentLoaded', function() {
        const editModal = document.getElementById('editShippingModal');
        if (editModal) {
            editModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const id = button.getAttribute('data-id');
                const form = document.getElementById('editShippingForm');

                form.action = '/settings/update-shipping/' + id;
                form.querySelector('[name="company"]').value = button.getAttribute('data-company');
                form.querySelector('[name="weight"]').value = button.getAttribute('data-weight');
                form.querySelector('[name="price"]').value = button.getAttribute('data-price');
                form.querySelector('[name="description"]').value = button.getAttribute('data-description');
                form.querySelector('[name="username"]').value = button.getAttribute('data-username');
                form.querySelector('[name="password"]').value = button.getAttribute('data-password');

                // Show current logo
                const logoCell = button.closest('tr').querySelector('td img');
                const currentLogo = document.getElementById('currentLogo');
                currentLogo.src = logoCell.src;
                currentLogo.style.display = 'block';
            });
        }
    });
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
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
    }
    .form-control {
        border-radius: 0.375rem;
    }
    .btn {
        border-radius: 0.375rem;
    }
</style>

@include('admin.layouts.footer')
