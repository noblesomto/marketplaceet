@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">

<div class="pagetitle">
  <h1>Dashboard</h1>
</div>

<section class="section">
  <div class="row">
    <div class="col-lg-12">
      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Sub Categories for {{ $cat->category }}</h5>
          @if(session('status'))
            <div class="alert alert-{{ session('status')['type'] }}">
                {{ session('status')['text'] }}
            </div>
          @endif

          <div class="container-fluid">
            <div class="row gx-2">
              @foreach ($subcat as $row)
              <div class="col-sm-3 border p-2 mx-3 my-1">
                <div class="row align-items-center">

                  {{-- Icon preview --}}
                  <div class="col-sm-2">
                    @if($row->icon)
                      <div style="position:relative;display:inline-block;">
                        <img src="{{ asset('frontend/images/subcategory-icons/' . $row->icon) }}"
                             alt="{{ $row->sub_category }}"
                             style="width:36px;height:36px;object-fit:cover;border-radius:6px;">
                        <form action="{{ route('admin.delete.subcategory.icon', $row->id) }}" method="POST" style="display:inline;"
                              onsubmit="return confirm('Remove this icon?');">
                          @csrf
                          @method('DELETE')
                          <button type="submit" title="Remove icon"
                                  style="position:absolute;top:-6px;right:-6px;width:16px;height:16px;border-radius:50%;background:#dc3545;border:none;color:#fff;font-size:9px;line-height:16px;padding:0;cursor:pointer;">
                            &times;
                          </button>
                        </form>
                      </div>
                    @else
                      <div style="width:36px;height:36px;background:#f0f0f0;border-radius:6px;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-image text-muted" style="font-size:1rem;"></i>
                      </div>
                    @endif
                  </div>

                  <div class="col-sm-5">
                    <strong>{{ $row->sub_category }}</strong>
                    @if($row->meta_title)
                      <br><small class="text-muted">{{ Str::limit($row->meta_title, 28) }}</small>
                    @endif
                  </div>

                  <div class="col-sm-5 text-end">
                    <a href="/admin/brand/{{ $row->id }}" class="btn btn-sm btn-info" title="View Brands">
                      <i class="fa fa-eye"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-warning edit-subcategory"
                            data-id="{{ $row->id }}"
                            data-subcategory="{{ $row->sub_category }}"
                            data-icon="{{ $row->icon }}"
                            data-meta-title="{{ $row->meta_title }}"
                            data-meta-description="{{ $row->meta_description }}"
                            data-keywords="{{ $row->keywords }}"
                            title="Edit Sub Category">
                      <i class="fa fa-edit"></i>
                    </button>
                    <form action="{{ route('admin.delete.subcategory', [$row->id, $row->cat_id]) }}" method="POST" style="display:inline;">
                      @csrf
                      @method('DELETE')
                      <button type="submit"
                              onclick="return confirm('Are you sure you want to delete this Sub Category and its Brands?');"
                              class="btn btn-sm btn-danger"
                              title="Delete Sub Category">
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
          <h5 class="card-title" id="form-title">New Sub Category for {{ $cat->category }}</h5>

          <form action="/admin/sub-category/{{ $cat->id }}" method="POST" id="subcategory-form" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="subcategory_id" id="subcategory_id" value="">

            <div class="row mb-3">
              <label class="col-sm-2 col-form-label">Category</label>
              <div class="col-sm-10">
                <input type="hidden" name="category" id="category" value="{{ $cat->id }}">
                <input type="text" class="form-control" value="{{ $cat->category }}" disabled readonly>
                <small class="text-muted">This subcategory belongs to {{ $cat->category }}</small>
              </div>
            </div>

            <div class="row mb-3">
              <label for="sub_category" class="col-sm-2 col-form-label">Sub Category *</label>
              <div class="col-sm-10">
                @if ($errors->has('sub_category'))
                  <span class="text-danger">{{ $errors->first('sub_category') }}</span>
                @endif
                <input type="text" name="sub_category" id="sub_category" class="form-control"
                       placeholder="Sub-Category Name" value="{{ old('sub_category') }}" required>
              </div>
            </div>

            <div class="row mb-3">
              <label for="icon" class="col-sm-2 col-form-label">Icon</label>
              <div class="col-sm-10">
                @if ($errors->has('icon'))
                  <span class="text-danger">{{ $errors->first('icon') }}</span>
                @endif
                <input type="file" name="icon" id="icon" class="form-control"
                       accept="image/jpeg,image/png,image/gif,image/webp,image/svg+xml">
                <small class="text-muted">PNG/WebP recommended. Max 2MB. Displayed as 60×60px on mobile.</small>
                <div id="current-icon-preview" class="mt-2" style="display:none;">
                  <small class="text-muted d-block mb-1">Current icon:</small>
                  <img id="icon-preview-img" src="" alt="Current icon"
                       style="width:48px;height:48px;object-fit:cover;border-radius:6px;border:1px solid #dee2e6;">
                  <small class="text-muted ms-2" id="icon-preview-name"></small>
                  <div class="mt-1">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="remove_icon" id="remove_icon" value="1">
                      <label class="form-check-label text-danger small" for="remove_icon">
                        Remove existing icon
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="row mb-3">
              <label for="meta_title" class="col-sm-2 col-form-label">Meta Title</label>
              <div class="col-sm-10">
                @if ($errors->has('meta_title'))
                  <span class="text-danger">{{ $errors->first('meta_title') }}</span>
                @endif
                <input type="text" name="meta_title" id="meta_title" class="form-control"
                       placeholder="Meta Title (for SEO)" value="{{ old('meta_title') }}" maxlength="255">
                <small class="text-muted char-counter-title">Max 255 characters. Recommended: 50-60 characters</small>
              </div>
            </div>

            <div class="row mb-3">
              <label for="meta_description" class="col-sm-2 col-form-label">Meta Description</label>
              <div class="col-sm-10">
                @if ($errors->has('meta_description'))
                  <span class="text-danger">{{ $errors->first('meta_description') }}</span>
                @endif
                <textarea name="meta_description" id="meta_description" class="form-control"
                          placeholder="Meta Description (for SEO)" rows="3"
                          maxlength="500">{{ old('meta_description') }}</textarea>
                <small class="text-muted char-counter-desc">Max 500 characters. Recommended: 150-160 characters</small>
              </div>
            </div>

            <div class="row mb-3">
              <label for="keywords" class="col-sm-2 col-form-label">Meta Keywords</label>
              <div class="col-sm-10">
                @if ($errors->has('keywords'))
                  <span class="text-danger">{{ $errors->first('keywords') }}</span>
                @endif
                <textarea name="keywords" id="keywords" class="form-control"
                          placeholder="Keywords (comma separated)" rows="3"
                          maxlength="500">{{ old('keywords') }}</textarea>
                <small class="text-muted char-counter-keywords">Max 500 characters.</small>
              </div>
            </div>

            <div class="row mb-3">
              <label class="col-sm-2 col-form-label"></label>
              <div class="col-sm-10">
                <button type="submit" class="btn btn-primary" id="submit-btn">Add Sub Category</button>
                <button type="button" class="btn btn-secondary" id="cancel-edit" style="display:none;">Cancel Edit</button>
              </div>
            </div>

          </form>
        </div>
      </div>
    </div>
  </div>
</section>

</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editButtons      = document.querySelectorAll('.edit-subcategory');
    const formTitle        = document.getElementById('form-title');
    const submitBtn        = document.getElementById('submit-btn');
    const cancelBtn        = document.getElementById('cancel-edit');
    const subcategoryIdInput   = document.getElementById('subcategory_id');
    const subcategoryNameInput = document.getElementById('sub_category');
    const metaTitleInput       = document.getElementById('meta_title');
    const metaDescInput        = document.getElementById('meta_description');
    const keywordsInput        = document.getElementById('keywords');
    const iconPreviewWrap  = document.getElementById('current-icon-preview');
    const iconPreviewImg   = document.getElementById('icon-preview-img');
    const iconPreviewName  = document.getElementById('icon-preview-name');

    editButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            subcategoryIdInput.value   = this.dataset.id;
            subcategoryNameInput.value = this.dataset.subcategory;
            metaTitleInput.value       = this.dataset.metaTitle || '';
            metaDescInput.value        = this.dataset.metaDescription || '';
            keywordsInput.value        = this.dataset.keywords || '';

            // Show existing icon preview
            if (this.dataset.icon) {
                iconPreviewImg.src  = '{{ asset("frontend/images/subcategory-icons") }}/' + this.dataset.icon;
                iconPreviewName.textContent = this.dataset.icon;
                iconPreviewWrap.style.display = 'block';
            } else {
                iconPreviewWrap.style.display = 'none';
            }

            formTitle.textContent = 'Edit Sub Category for {{ $cat->category }}';
            submitBtn.textContent = 'Update Sub Category';
            submitBtn.classList.replace('btn-primary', 'btn-success');
            cancelBtn.style.display = 'inline-block';

            document.getElementById('subcategory-form').scrollIntoView({ behavior: 'smooth' });
            subcategoryNameInput.focus();

            [metaTitleInput, metaDescInput, keywordsInput].forEach(el => el.dispatchEvent(new Event('input')));
        });
    });

    cancelBtn.addEventListener('click', resetForm);

    function resetForm() {
        subcategoryIdInput.value   = '';
        subcategoryNameInput.value = '';
        document.getElementById('remove_icon').checked = false;
        metaTitleInput.value       = '';
        metaDescInput.value        = '';
        keywordsInput.value        = '';
        iconPreviewWrap.style.display = 'none';
        formTitle.textContent = 'New Sub Category for {{ $cat->category }}';
        submitBtn.textContent = 'Add Sub Category';
        submitBtn.classList.replace('btn-success', 'btn-primary');
        cancelBtn.style.display = 'none';
        [metaTitleInput, metaDescInput, keywordsInput].forEach(el => el.dispatchEvent(new Event('input')));
    }

    // Character counters
    function attachCounter(el, counterClass, max, recommended) {
        el.addEventListener('input', function () {
            const n = this.value.length;
            const counter = this.parentElement.querySelector('.' + counterClass);
            if (!counter) return;
            counter.textContent = `${n}/${max} characters${recommended ? ' - Recommended: ' + recommended : ''}`;
            counter.className = counterClass + ' text-muted small ' + (n > max ? 'text-danger' : (recommended && n > parseInt(recommended) ? 'text-warning' : ''));
        });
        el.dispatchEvent(new Event('input'));
    }

    attachCounter(metaTitleInput, 'char-counter-title', 255, '50-60');
    attachCounter(metaDescInput,  'char-counter-desc',  500, '150-160');
    attachCounter(keywordsInput,  'char-counter-keywords', 500, null);
});
</script>

@include('admin.layouts.footer')
