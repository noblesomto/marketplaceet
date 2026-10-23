@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">

<div class="pagetitle">
  <h1>Dashboard</h1>
</div><!-- End Page Title -->

<section class="section">
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Models for {{ $brand->brand }}</h5>
          @if(session('status'))
                <div class="alert alert-{{session('status')['type']}}">
                    {{session('status')['text']}}
                </div>
            @endif

           <div class="container-fluid">
               <div class="row gx-2">
                @foreach ($model as $row)
                   <div class="col-sm-3 border p-2 mx-3 my-1">
                       <div class="row">
                           <div class="col-sm-7">
                               <strong>{{ $row->model }}</strong>
                               @if($row->year_start || $row->year_end)
                                   <br><small class="text-muted">
                                       @if($row->year_start && $row->year_end)
                                           ({{ $row->year_start }}-{{ $row->year_end }})
                                       @elseif($row->year_start)
                                           (From {{ $row->year_start }})
                                       @elseif($row->year_end)
                                           (Until {{ $row->year_end }})
                                       @endif
                                   </small>
                               @endif
                               @if($row->meta_title)
                                   <br><small class="text-muted">Meta: {{ Str::limit($row->meta_title, 30) }}</small>
                               @endif
                           </div>
                           <div class="col-sm-5 text-end">
                              <button type="button" class="btn btn-sm btn-warning edit-model"
                                      data-id="{{ $row->id }}"
                                      data-model="{{ $row->model }}"
                                      data-meta-title="{{ $row->meta_title }}"
                                      data-meta-description="{{ $row->meta_description }}"
                                      data-year-start="{{ $row->year_start }}"
                                      data-year-end="{{ $row->year_end }}"
                                      title="Edit Model">
                                  <i class="fa fa-edit"></i>
                              </button>
                              <form action="{{ route('admin.delete.model', [$row->id, $brand->id]) }}" method="POST" style="display: inline;">
                                  @csrf
                                  @method('DELETE')
                                  <button type="submit"
                                          onclick="return confirm('Are you sure you want to delete this Model?');"
                                          class="btn btn-sm btn-danger"
                                          title="Delete Model">
                                      <i class="fa fa-trash"></i>
                                  </button>
                              </form>
                           </div>
                       </div>
                   </div>
                 @endforeach
               </div>
           </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section">
    <div class="row">
    <div class="col-lg-12">

        <div class="card">
        <div class="card-body">
            <h5 class="card-title" id="form-title">New Model for {{ $brand->brand }}</h5>

            <!-- General Form Elements -->
            <form action="/admin/model/{{ $brand->id }}" method="POST" role="form" class="" id="model-form" enctype="multipart/form-data">
             @csrf
             <input type="hidden" name="model_id" id="model_id" value="">

            <div class="row mb-3">
                <label for="brand" class="col-sm-2 col-form-label">Brand</label>
                <div class="col-sm-10">
                    @if ($errors->has('brand'))
                        <span class="text-danger">{{ $errors->first('brand') }}</span>
                    @endif
                    <input type="hidden" name="brand" id="brand" value="{{ $brand->id }}">
                    <input type="text" class="form-control" value="{{ $brand->brand }}" disabled readonly>
                    <small class="text-muted">This model belongs to {{ $brand->brand }}</small>
                </div>
            </div>

            <div class="row mb-3">
                <label for="model" class="col-sm-2 col-form-label">Model Name</label>
                <div class="col-sm-10">
                    @if ($errors->has('model'))
                        <span class="text-danger">{{ $errors->first('model') }}</span>
                    @endif
                    <input type="text" name="model" id="model" class="form-control"
                           placeholder="Model Name" value="{{ old('model') }}" required>
                </div>
            </div>

            <!--
            <div class="row mb-3">
                <label for="year_start" class="col-sm-2 col-form-label">Year Start</label>
                <div class="col-sm-10">
                    @if ($errors->has('year_start'))
                        <span class="text-danger">{{ $errors->first('year_start') }}</span>
                    @endif
                    <input type="number" name="year_start" id="year_start" class="form-control"
                           placeholder="Start Year (e.g., 2015)"
                           value="{{ old('year_start') }}"
                           min="1900"
                           max="{{ date('Y') }}">
                    <small class="text-muted">Optional - First year of production</small>
                </div>
            </div>

            <div class="row mb-3">
                <label for="year_end" class="col-sm-2 col-form-label">Year End</label>
                <div class="col-sm-10">
                    @if ($errors->has('year_end'))
                        <span class="text-danger">{{ $errors->first('year_end') }}</span>
                    @endif
                    <input type="number" name="year_end" id="year_end" class="form-control"
                           placeholder="End Year (e.g., 2020)"
                           value="{{ old('year_end') }}"
                           min="1900"
                           max="{{ date('Y') }}">
                    <small class="text-muted">Optional - Last year of production (leave blank if still in production)</small>
                </div>
            </div>

            -->


            <div class="row mb-3">
                <label for="meta_title" class="col-sm-2 col-form-label">Meta Title</label>
                <div class="col-sm-10">
                    @if ($errors->has('meta_title'))
                        <span class="text-danger">{{ $errors->first('meta_title') }}</span>
                    @endif
                    <input type="text" name="meta_title" id="meta_title" class="form-control"
                           placeholder="Meta Title (for SEO)"
                           value="{{ old('meta_title') }}"
                           maxlength="255">
                    <small class="text-muted">Max 255 characters. Example: "{{ $brand->brand }} [Model Name] - Specifications, Reviews, Prices"</small>
                </div>
            </div>

            <div class="row mb-3">
                <label for="meta_description" class="col-sm-2 col-form-label">Meta Description</label>
                <div class="col-sm-10">
                    @if ($errors->has('meta_description'))
                        <span class="text-danger">{{ $errors->first('meta_description') }}</span>
                    @endif
                    <textarea name="meta_description" id="meta_description" class="form-control"
                              placeholder="Meta Description (for SEO)"
                              rows="3"
                              maxlength="500">{{ old('meta_description') }}</textarea>
                    <small class="text-muted">Max 500 characters. Describe key features, specifications, and uses of this model.</small>
                </div>
            </div>

            <div class="row mb-3">
                <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                    <button type="submit" class="btn btn-primary" id="submit-btn">Add Model</button>
                    <button type="button" class="btn btn-secondary" id="cancel-edit" style="display: none;">Cancel Edit</button>
                </div>
            </div>

            </form><!-- End General Form Elements -->

        </div>
        </div>

    </div>

    </div>
</section>

</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit model functionality
    const editButtons = document.querySelectorAll('.edit-model');
    const formTitle = document.getElementById('form-title');
    const submitBtn = document.getElementById('submit-btn');
    const cancelBtn = document.getElementById('cancel-edit');
    const modelForm = document.getElementById('model-form');
    const modelIdInput = document.getElementById('model_id');
    const modelNameInput = document.getElementById('model');
    const yearStartInput = document.getElementById('year_start');
    const yearEndInput = document.getElementById('year_end');
    const metaTitleInput = document.getElementById('meta_title');
    const metaDescriptionInput = document.getElementById('meta_description');

    // Edit button click handler
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modelId = this.getAttribute('data-id');
            const modelName = this.getAttribute('data-model');
            const metaTitle = this.getAttribute('data-meta-title');
            const metaDescription = this.getAttribute('data-meta-description');
            const yearStart = this.getAttribute('data-year-start');
            const yearEnd = this.getAttribute('data-year-end');

            // Fill the form with model data
            modelIdInput.value = modelId;
            modelNameInput.value = modelName;
            metaTitleInput.value = metaTitle || '';
            metaDescriptionInput.value = metaDescription || '';
            // Note: Year fields are commented out in HTML, so we won't fill them
            // yearStartInput.value = yearStart || '';
            // yearEndInput.value = yearEnd || '';

            // Change form title and button text
            formTitle.textContent = 'Edit Model for {{ $brand->brand }}';
            submitBtn.textContent = 'Update Advert'; // Changed from 'Update Model'
            submitBtn.classList.remove('btn-primary');
            submitBtn.classList.add('btn-success');
            cancelBtn.style.display = 'inline-block';

            // Scroll to form
            document.getElementById('model-form').scrollIntoView({ behavior: 'smooth' });
            modelNameInput.focus();
        });
    });

    // Cancel edit button handler
    cancelBtn.addEventListener('click', function() {
        resetForm();
    });

    // Form submit success - reset form
    modelForm.addEventListener('submit', function() {
        // Reset form after successful submission
        setTimeout(resetForm, 100);
    });

    function resetForm() {
        modelIdInput.value = '';
        modelNameInput.value = '';
        metaTitleInput.value = '';
        metaDescriptionInput.value = '';
        // yearStartInput.value = '';
        // yearEndInput.value = '';
        formTitle.textContent = 'New Model for {{ $brand->brand }}';
        submitBtn.textContent = 'Add Model';
        submitBtn.classList.remove('btn-success');
        submitBtn.classList.add('btn-primary');
        cancelBtn.style.display = 'none';
    }

    // Character counter for meta description
    metaDescriptionInput.addEventListener('input', function() {
        const charCount = this.value.length;
        let counter = this.parentElement.querySelector('.char-counter');

        if (!counter) {
            counter = document.createElement('small');
            counter.className = 'text-muted char-counter mt-1 d-block';
            this.parentElement.appendChild(counter);
        }

        counter.textContent = `${charCount}/500 characters`;

        if (charCount > 500) {
            counter.classList.add('text-danger');
            counter.classList.remove('text-muted');
        } else {
            counter.classList.remove('text-danger');
            counter.classList.add('text-muted');
        }
    });

    // Character counter for meta title
    metaTitleInput.addEventListener('input', function() {
        const charCount = this.value.length;
        let counter = this.parentElement.querySelector('.char-counter');

        if (!counter) {
            counter = document.createElement('small');
            counter.className = 'text-muted char-counter mt-1 d-block';
            this.parentElement.appendChild(counter);
        }

        counter.textContent = `${charCount}/255 characters`;

        if (charCount > 255) {
            counter.classList.add('text-danger');
            counter.classList.remove('text-muted');
        } else {
            counter.classList.remove('text-danger');
            counter.classList.add('text-muted');
        }
    });

    // Trigger initial character count display
    if (metaDescriptionInput.value) {
        metaDescriptionInput.dispatchEvent(new Event('input'));
    }
    if (metaTitleInput.value) {
        metaTitleInput.dispatchEvent(new Event('input'));
    }
});
</script>

@include('admin.layouts.footer')
