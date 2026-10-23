@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">

<div class="pagetitle">
  <h1>Category UI Configuration</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="/admin/index">Home</a></li>
      <li class="breadcrumb-item active">Category UI Configuration</li>
    </ol>
  </nav>
</div><!-- End Page Title -->

<section class="section">
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">
            <i class="bi bi-sliders"></i> Manage UI Configuration
          </h5>
          <p class="text-muted">Manage show/hide rules for Post Ad and Edit Ad forms without touching code</p>

          @if(session('status'))
            <div class="alert alert-{{session('status')['type']}} alert-dismissible fade show" role="alert">
                {{session('status')['text']}}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          <!-- Categories Section -->
          <div class="mb-5">
            <h6 class="mb-3">
              <i class="bi bi-folder-fill text-primary"></i> Categories
              <span class="badge bg-info">{{ $categories->count() }} total</span>
            </h6>

            <div class="table-responsive">
              <table class="table table-bordered table-hover">
                <thead class="table-light">
                  <tr>
                    <th>ID</th>
                    <th>Category Name</th>
                    <th>Status</th>
                    <th>Configuration</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($categories as $category)
                    <tr>
                      <td>{{ $category->id }}</td>
                      <td>
                        <strong>{{ $category->category }}</strong>
                        <br>
                        <small class="text-muted">{{ $category->category_slug }}</small>
                      </td>
                      <td>
                        @if($category->has_config)
                          <span class="badge bg-success">
                            <i class="bi bi-check-circle"></i> Configured
                          </span>
                        @else
                          <span class="badge bg-secondary">
                            <i class="bi bi-gear"></i> Using Default
                          </span>
                        @endif
                      </td>
                      <td>
                        @if($category->has_config)
                          <small class="text-muted">{{ $category->config_count }} rules defined</small>
                        @else
                          <small class="text-muted">No custom rules</small>
                        @endif
                      </td>
                      <td>
                        <a href="{{ route('admin.category-ui.edit-category', $category->id) }}"
                           class="btn btn-sm btn-primary"
                           title="Edit UI Configuration">
                          <i class="bi bi-pencil"></i> Edit
                        </a>

                        @if($category->has_config)
                          <form action="{{ route('admin.category-ui.delete-category', $category->id) }}"
                                method="POST"
                                class="d-inline"
                                onsubmit="return confirm('Remove custom configuration and use default?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-warning" title="Reset to Default">
                              <i class="bi bi-arrow-clockwise"></i> Reset
                            </button>
                          </form>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>

          <!-- Subcategories Section -->
          <div class="mb-4">
            <h6 class="mb-3">
              <i class="bi bi-layers-fill text-success"></i> Subcategories
              <span class="badge bg-info">{{ $subcategories->count() }} total</span>
            </h6>

            <div class="table-responsive">
              <table class="table table-bordered table-hover">
                <thead class="table-light">
                  <tr>
                    <th>ID</th>
                    <th>Parent Category</th>
                    <th>Subcategory Name</th>
                    <th>Status</th>
                    <th>Configuration</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($subcategories as $subcategory)
                    <tr>
                      <td>{{ $subcategory->id }}</td>
                      <td>
                        <small class="text-muted">{{ $subcategory->category->category }}</small>
                      </td>
                      <td>
                        <strong>{{ $subcategory->sub_category }}</strong>
                        <br>
                        <small class="text-muted">{{ $subcategory->sub_cat_slug }}</small>
                      </td>
                      <td>
                        @if($subcategory->has_config)
                          <span class="badge bg-success">
                            <i class="bi bi-check-circle"></i> Configured
                          </span>
                        @else
                          <span class="badge bg-secondary">
                            <i class="bi bi-gear"></i> Using Default
                          </span>
                        @endif
                      </td>
                      <td>
                        @if($subcategory->has_config)
                          <small class="text-muted">{{ $subcategory->config_count }} rules defined</small>
                        @else
                          <small class="text-muted">No custom rules</small>
                        @endif
                      </td>
                      <td>
                        <a href="{{ route('admin.category-ui.edit-subcategory', $subcategory->id) }}"
                           class="btn btn-sm btn-success"
                           title="Edit UI Configuration">
                          <i class="bi bi-pencil"></i> Edit
                        </a>

                        @if($subcategory->has_config)
                          <form action="{{ route('admin.category-ui.delete-subcategory', $subcategory->id) }}"
                                method="POST"
                                class="d-inline"
                                onsubmit="return confirm('Remove custom configuration and use default?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-warning" title="Reset to Default">
                              <i class="bi bi-arrow-clockwise"></i> Reset
                            </button>
                          </form>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>

          <!-- Help Section -->
          <div class="alert alert-info">
            <h6><i class="bi bi-info-circle"></i> How It Works</h6>
            <ul class="mb-0">
              <li><strong>Show:</strong> Select elements that should be visible for this category/subcategory</li>
              <li><strong>Hide:</strong> Select elements that should be hidden</li>
              <li><strong>Labels:</strong> Customize field labels (e.g., "Select Job Type" for Jobs category)</li>
              <li><strong>Required:</strong> Mark fields as required (subcategories only)</li>
              <li><strong>Cache:</strong> Changes are automatically cached for 24 hours for performance</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

</main><!-- End #main -->

@include('admin.layouts.footer')

<script>
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').not('.alert-info').fadeOut('slow');
    }, 5000);
</script>
