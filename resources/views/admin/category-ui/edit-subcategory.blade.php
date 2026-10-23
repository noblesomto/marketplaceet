@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">

<div class="pagetitle">
  <h1>Edit Subcategory UI Configuration</h1>
  <nav>
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="/admin/index">Home</a></li>
      <li class="breadcrumb-item"><a href="/admin/category-ui">Category UI Config</a></li>
      <li class="breadcrumb-item">{{ $subcategory->category->category }}</li>
      <li class="breadcrumb-item active">{{ $subcategory->sub_category }}</li>
    </ol>
  </nav>
</div><!-- End Page Title -->

<section class="section">
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">
            <i class="bi bi-sliders"></i> {{ $subcategory->sub_category }}
          </h5>
          <p class="text-muted">
            Parent: {{ $subcategory->category->category }} |
            Subcategory ID: {{ $subcategory->id }} |
            Slug: {{ $subcategory->sub_cat_slug }}
          </p>

          <form action="{{ route('admin.category-ui.update-subcategory', $subcategory->id) }}" method="POST">
            @csrf

            <div class="row">
              <!-- Elements to Show -->
              <div class="col-md-6 mb-4">
                <div class="card border-success">
                  <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="bi bi-eye"></i> Elements to SHOW</h6>
                    <small>Select elements that should be visible</small>
                  </div>
                  <div class="card-body">
                    @foreach($availableElements as $group => $elements)
                      <h6 class="text-success">{{ $group }}</h6>
                      @foreach($elements as $key => $label)
                        <div class="form-check mb-2">
                          <input class="form-check-input"
                                 type="checkbox"
                                 id="show_{{ $key }}"
                                 name="show[]"
                                 value="{{ $key }}"
                                 {{ in_array($key, $config['show'] ?? []) ? 'checked' : '' }}>
                          <label class="form-check-label" for="show_{{ $key }}">
                            <code>{{ $key }}</code> - {{ $label }}
                          </label>
                        </div>
                      @endforeach
                      @if(!$loop->last)<hr>@endif
                    @endforeach
                  </div>
                </div>
              </div>

              <!-- Elements to Hide -->
              <div class="col-md-6 mb-4">
                <div class="card border-danger">
                  <div class="card-header bg-danger text-white">
                    <h6 class="mb-0"><i class="bi bi-eye-slash"></i> Elements to HIDE</h6>
                    <small>Select elements that should be hidden</small>
                  </div>
                  <div class="card-body">
                    @foreach($availableElements as $group => $elements)
                      <h6 class="text-danger">{{ $group }}</h6>
                      @foreach($elements as $key => $label)
                        <div class="form-check mb-2">
                          <input class="form-check-input"
                                 type="checkbox"
                                 id="hide_{{ $key }}"
                                 name="hide[]"
                                 value="{{ $key }}"
                                 {{ in_array($key, $config['hide'] ?? []) ? 'checked' : '' }}>
                          <label class="form-check-label" for="hide_{{ $key }}">
                            <code>{{ $key }}</code> - {{ $label }}
                          </label>
                        </div>
                      @endforeach
                      @if(!$loop->last)<hr>@endif
                    @endforeach
                  </div>
                </div>
              </div>
            </div>

            <!-- Label Customization -->
            <div class="card border-info mb-4">
              <div class="card-header bg-info text-white">
                <h6 class="mb-0"><i class="bi bi-tag"></i> Label Customization</h6>
                <small>Customize field labels (optional)</small>
              </div>
              <div class="card-body">
                <div class="mb-3">
                  <label for="label_brand" class="form-label">Brand/Option Label</label>
                  <input type="text"
                         class="form-control"
                         id="label_brand"
                         name="label_brand"
                         value="{{ $config['labels']['brand'] ?? 'Select Option:' }}"
                         placeholder="e.g., Brand:, Select Model:">
                  <div class="form-text">
                    Examples: "Brand:" (for Cars), "Select Option:" (default)
                  </div>
                </div>
              </div>
            </div>

            <!-- Required Fields (Subcategory Only) -->
            <div class="card border-warning mb-4">
              <div class="card-header bg-warning text-white">
                <h6 class="mb-0"><i class="bi bi-asterisk"></i> Required Fields</h6>
                <small>Mark fields as required (subcategory-specific)</small>
              </div>
              <div class="card-body">
                <div class="form-check mb-2">
                  <input class="form-check-input"
                         type="checkbox"
                         id="required_model"
                         name="required[]"
                         value="model"
                         {{ in_array('model', $config['required'] ?? []) ? 'checked' : '' }}>
                  <label class="form-check-label" for="required_model">
                    <code>model</code> - Model Dropdown (Required)
                  </label>
                </div>
                <div class="form-text">
                  Common for: Cars, Phones, etc.
                </div>
              </div>
            </div>

            <!-- Current Configuration Preview -->
            <div class="card border-secondary mb-4">
              <div class="card-header bg-secondary text-white">
                <h6 class="mb-0"><i class="bi bi-code-slash"></i> Current Configuration (JSON)</h6>
              </div>
              <div class="card-body">
                <pre class="bg-light p-3 rounded"><code>{{ json_encode($config, JSON_PRETTY_PRINT) }}</code></pre>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="mb-3">
              <button type="submit" class="btn btn-success">
                <i class="bi bi-save"></i> Save Configuration
              </button>
              <a href="{{ route('admin.category-ui.index') }}" class="btn btn-secondary">
                <i class="bi bi-x-circle"></i> Cancel
              </a>

              @if($subcategory->ui_config)
                <button type="button"
                        class="btn btn-warning float-end"
                        onclick="if(confirm('Reset to default configuration?')) document.getElementById('reset-form').submit();">
                  <i class="bi bi-arrow-clockwise"></i> Reset to Default
                </button>
              @endif
            </div>
          </form>

          <!-- Reset Form (hidden) -->
          @if($subcategory->ui_config)
            <form id="reset-form"
                  action="{{ route('admin.category-ui.delete-subcategory', $subcategory->id) }}"
                  method="POST"
                  class="d-none">
              @csrf
              @method('DELETE')
            </form>
          @endif

          <!-- Help Section -->
          <div class="alert alert-warning mt-4">
            <h6><i class="bi bi-exclamation-triangle"></i> Important Notes</h6>
            <ul class="mb-0">
              <li><strong>Subcategory rules are ADDITIVE</strong> to category rules (applied on top of category config)</li>
              <li>Changes are cached for 24 hours - clear cache after saving for immediate effect</li>
              <li>Don't select the same element in both "Show" and "Hide" - Hide takes precedence</li>
              <li>Test changes on Post Ad and Edit Ad pages after saving</li>
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
    // Prevent same element from being selected in both show and hide
    document.addEventListener('DOMContentLoaded', function() {
        const showCheckboxes = document.querySelectorAll('input[name="show[]"]');
        const hideCheckboxes = document.querySelectorAll('input[name="hide[]"]');

        showCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    const value = this.value;
                    const correspondingHide = document.querySelector(`input[name="hide[]"][value="${value}"]`);
                    if (correspondingHide) {
                        correspondingHide.checked = false;
                    }
                }
            });
        });

        hideCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    const value = this.value;
                    const correspondingShow = document.querySelector(`input[name="show[]"][value="${value}"]`);
                    if (correspondingShow) {
                        correspondingShow.checked = false;
                    }
                }
            });
        });
    });
</script>
