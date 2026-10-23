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
          <h5 class="card-title">Categories</h5>
          @if(session('status'))
                <div class="alert alert-{{session('status')['type']}}">
                    {{session('status')['text']}}
                </div>
            @endif

           <div class="container-fluid">
               <div class="row gx-2">
                @foreach ($category as $row)
                   <div class="col-sm-3 border p-2 mx-3 my-1">
                       <div class="row ">
                           <div class="col-sm-7">
                               <strong>{{ $row->category }}</strong>
                               <br>
                               @php
                                   $groupLabels = ['product' => 'Product', 'property' => 'Property', 'service' => 'Service'];
                                   $groupColors = ['product' => 'primary', 'property' => 'success', 'service' => 'warning'];
                                   $grp = $row->seo_group ?? 'product';
                               @endphp
                               <span class="badge bg-{{ $groupColors[$grp] ?? 'secondary' }} bg-opacity-10 text-{{ $groupColors[$grp] ?? 'secondary' }} border border-{{ $groupColors[$grp] ?? 'secondary' }}" style="font-size:0.7rem;">
                                   {{ $groupLabels[$grp] ?? $grp }}
                               </span>
                               @if($row->meta_title)
                                   <br><small class="text-muted">Meta: {{ Str::limit($row->meta_title, 30) }}</small>
                               @endif
                           </div>
                           <div class="col-sm-5 text-end">
                              <a href="/admin/sub-category/{{ $row->id }}" class="btn btn-sm btn-info" title="View Subcategories">
                                  <i class="fa fa-eye"></i>
                              </a>
                              <button type="button" class="btn btn-sm btn-warning edit-category"
                                      data-id="{{ $row->id }}"
                                      data-category="{{ $row->category }}"
                                      data-seo-group="{{ $row->seo_group ?? 'product' }}"
                                      data-meta-title="{{ $row->meta_title }}"
                                      data-meta-description="{{ $row->meta_description }}"
                                      data-keywords="{{ $row->keywords }}"
                                      title="Edit Category">
                                  <i class="fa fa-edit"></i>
                              </button>
                              <form action="{{ route('admin.delete.category', $row->id) }}" method="POST" style="display: inline;">
                                  @csrf
                                  @method('DELETE')
                                  <button type="submit"
                                          onclick="return confirm('Are you sure you want to delete this Category with the Subcategory and Brands?');"
                                          class="btn btn-sm btn-danger"
                                          title="Delete Category">
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
                    <h5 class="card-title" id="form-title">New Category</h5>

                    <!-- General Form Elements -->
                    <form action="/admin/category" method="POST" role="form" class="" id="category-form">
                        @csrf
                        <input type="hidden" name="category_id" id="category_id" value="">

                        <div class="row mb-3">
                            <label for="category" class="col-sm-2 col-form-label">Category Name</label>
                            <div class="col-sm-10">
                                @if ($errors->has('category'))
                                    <span class="text-danger">{{ $errors->first('category') }}</span>
                                @endif
                                <input type="text" name="category" id="category" class="form-control"
                                       placeholder="Category Name" value="{{ old('category') }}" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label for="seo_group" class="col-sm-2 col-form-label">SEO Group</label>
                            <div class="col-sm-10">
                                <select name="seo_group" id="seo_group" class="form-select">
                                    <option value="product">Product — "Buy … Online" (Electronics, Fashion, Vehicles, etc.)</option>
                                    <option value="property">Property — "… for Rent &amp; Sale" (Real Estate, Land, Shortlets)</option>
                                    <option value="service">Service — "Find …" (Jobs, Artisans, Freelancers)</option>
                                </select>
                                <small class="text-muted">Controls the SEO title &amp; description formula shown on the category page.</small>
                            </div>
                        </div>

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
                                          placeholder="Meta Description (for SEO)"
                                          rows="3"
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
                                          placeholder="Keywords (comma separated)"
                                          rows="3"
                                          maxlength="500">{{ old('keywords') }}</textarea>
                                <small class="text-muted char-counter-keywords">Max 500 characters. Example: keyword1, keyword2, keyword3</small>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <label class="col-sm-2 col-form-label"></label>
                            <div class="col-sm-10">
                                <button type="submit" class="btn btn-primary" id="submit-btn">Add Category</button>
                                <button type="button" class="btn btn-secondary" id="cancel-edit" style="display: none;">Cancel Edit</button>
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
document.addEventListener('DOMContentLoaded', function() {
    // Edit category functionality
    const editButtons = document.querySelectorAll('.edit-category');
    const formTitle = document.getElementById('form-title');
    const submitBtn = document.getElementById('submit-btn');
    const cancelBtn = document.getElementById('cancel-edit');
    const categoryForm = document.getElementById('category-form');
    const categoryIdInput = document.getElementById('category_id');
    const categoryNameInput = document.getElementById('category');
    const seoGroupInput = document.getElementById('seo_group');
    const metaTitleInput = document.getElementById('meta_title');
    const metaDescriptionInput = document.getElementById('meta_description');
    const keywordsInput = document.getElementById('keywords');

    // Edit button click handler
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const categoryId = this.getAttribute('data-id');
            const categoryName = this.getAttribute('data-category');
            const seoGroup = this.getAttribute('data-seo-group') || 'product';
            const metaTitle = this.getAttribute('data-meta-title');
            const metaDescription = this.getAttribute('data-meta-description');
            const keywords = this.getAttribute('data-keywords');

            // Fill the form with category data
            categoryIdInput.value = categoryId;
            categoryNameInput.value = categoryName;
            seoGroupInput.value = seoGroup;
            metaTitleInput.value = metaTitle || '';
            metaDescriptionInput.value = metaDescription || '';
            keywordsInput.value = keywords || '';

            // Change form title and button text
            formTitle.textContent = 'Edit Category';
            submitBtn.textContent = 'Update Category';
            submitBtn.classList.remove('btn-primary');
            submitBtn.classList.add('btn-success');
            cancelBtn.style.display = 'inline-block';

            // Scroll to form
            document.getElementById('category-form').scrollIntoView({ behavior: 'smooth' });
            categoryNameInput.focus();

            // Trigger character counters
            metaTitleInput.dispatchEvent(new Event('input'));
            metaDescriptionInput.dispatchEvent(new Event('input'));
            keywordsInput.dispatchEvent(new Event('input'));
        });
    });

    // Cancel edit button handler
    cancelBtn.addEventListener('click', function() {
        resetForm();
    });

    // Form submit success - reset form
    categoryForm.addEventListener('submit', function() {
        // Reset form after successful submission
        setTimeout(resetForm, 100);
    });

    function resetForm() {
        categoryIdInput.value = '';
        categoryNameInput.value = '';
        seoGroupInput.value = 'product';
        metaTitleInput.value = '';
        metaDescriptionInput.value = '';
        keywordsInput.value = '';
        formTitle.textContent = 'New Category';
        submitBtn.textContent = 'Add Category';
        submitBtn.classList.remove('btn-success');
        submitBtn.classList.add('btn-primary');
        cancelBtn.style.display = 'none';

        // Reset character counters
        metaTitleInput.dispatchEvent(new Event('input'));
        metaDescriptionInput.dispatchEvent(new Event('input'));
        keywordsInput.dispatchEvent(new Event('input'));
    }

    // Character counter for meta description
    metaDescriptionInput.addEventListener('input', function() {
        const charCount = this.value.length;
        const counter = this.parentElement.querySelector('.char-counter-desc');
        if (counter) {
            counter.textContent = `${charCount}/500 characters - Recommended: 150-160 characters`;

            if (charCount > 500) {
                counter.classList.add('text-danger');
                counter.classList.remove('text-muted');
            } else if (charCount > 160) {
                counter.classList.remove('text-danger');
                counter.classList.add('text-warning');
            } else {
                counter.classList.remove('text-danger', 'text-warning');
                counter.classList.add('text-muted');
            }
        }
    });

    // Character counter for meta title
    metaTitleInput.addEventListener('input', function() {
        const charCount = this.value.length;
        const counter = this.parentElement.querySelector('.char-counter-title');
        if (counter) {
            counter.textContent = `${charCount}/255 characters - Recommended: 50-60 characters`;

            if (charCount > 255) {
                counter.classList.add('text-danger');
                counter.classList.remove('text-muted');
            } else if (charCount > 60) {
                counter.classList.remove('text-danger');
                counter.classList.add('text-warning');
            } else {
                counter.classList.remove('text-danger', 'text-warning');
                counter.classList.add('text-muted');
            }
        }
    });

    // Character counter for keywords
    keywordsInput.addEventListener('input', function() {
        const charCount = this.value.length;
        const counter = this.parentElement.querySelector('.char-counter-keywords');
        if (counter) {
            counter.textContent = `${charCount}/500 characters`;

            if (charCount > 500) {
                counter.classList.add('text-danger');
                counter.classList.remove('text-muted');
            } else {
                counter.classList.remove('text-danger');
                counter.classList.add('text-muted');
            }
        }
    });

    // Trigger initial character count display
    metaDescriptionInput.dispatchEvent(new Event('input'));
    metaTitleInput.dispatchEvent(new Event('input'));
    keywordsInput.dispatchEvent(new Event('input'));
});
</script>

@include('admin.layouts.footer')
