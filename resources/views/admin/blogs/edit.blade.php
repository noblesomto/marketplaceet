@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">
  <div class="pagetitle">
    <h1>Edit Blog Post</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin/dashboard">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('blogs.index') }}">Blogs</a></li>
        <li class="breadcrumb-item active">Edit</li>
      </ol>
    </nav>
  </div><!-- End Page Title -->

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Edit Blog Post: {{ $blog->title }}</h5>

            <!-- Success Message -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="bi bi-check-circle me-2"></i>
              {{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <!-- Loading Spinner for Editor -->
            <div id="editor-loading" class="text-center py-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading editor...</span>
              </div>
              <p class="mt-2 text-muted">Loading editor...</p>
            </div>

            <form action="{{ route('blogs.update', $blog->id) }}" method="POST" enctype="multipart/form-data" id="blog-form">
              @csrf
              @method('PUT')

              <!-- Title Field -->
              <div class="mb-3">
                <label for="title" class="form-label">Post Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $blog->title) }}" placeholder="Enter a compelling title for your blog post" required>
                @error('title')
                <div class="text-danger small">{{ $message }}</div>
                @enderror
              </div>

              <div class="mb-3">
                <label for="title" class="form-label">
                    Categories <span class="text-danger">*</span>
                </label>
                <select name="category" class="form-control" required>
                    <option value="">Select Category</option>
                    @foreach ([
                        'e-commerce-tips' => 'E-Commerce Tips',
                        'business-entrepreneurship' => 'Business & Entrepreneurship',
                        'digital-marketing' => 'Digital Marketing',
                        'technology-innovation' => 'Technology & Innovation',
                        'product-industry-insights' => 'Product & Industry Insights',
                        'local-business-economy' => 'Local Business & Economy',
                        'guides-tutorials' => 'Guides & Tutorials',
                        'marketplace-news-updates' => 'Marketplace News & Updates'
                    ] as $value => $label)
                        <option value="{{ $value }}" {{ old('category', $blog->category ?? '') == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>


              <!-- Featured Image Field -->
              <div class="mb-3">
                <label for="featured_image" class="form-label">Featured Image</label>

                <!-- Current Image Preview -->
                @if($blog->featured_image_thumb)
                <div class="mb-3">
                  <p class="text-muted mb-2">Current Featured Image:</p>
                  <img src="{{ $blog->featured_image_thumb }}" alt="Current Featured Image" class="img-thumbnail" style="max-height: 200px;">
                  <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="remove_featured_image" id="remove_featured_image" value="1">
                    <label class="form-check-label text-danger" for="remove_featured_image">
                      Remove featured image
                    </label>
                  </div>
                </div>
                @endif

                <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*">
                <div class="form-text">PNG, JPG, GIF up to 10MB. Leave empty to keep current image.</div>
                @error('featured_image')
                <div class="text-danger small">{{ $message }}</div>
                @enderror

                <!-- New Image Preview -->
                <div id="image-preview" class="mt-2 d-none">
                  <p class="text-muted">New Image Preview:</p>
                  <img id="preview" class="img-thumbnail" style="max-height: 200px;">
                </div>
              </div>

              <!-- Content Field -->
              <div class="mb-3">
                <label for="tinymce-editor" class="form-label">Content <span class="text-danger">*</span></label>
                <textarea id="tinymce-editor" name="content" class="d-none">{{ old('content', $blog->content) }}</textarea>
                @error('content')
                <div class="text-danger small">{{ $message }}</div>
                @enderror
              </div>

              <!-- Keywords Field -->
              <div class="mb-3">
                <label for="keywords" class="form-label">Keywords </label>
                <input type="text" class="form-control" id="keywords" name="keywords" placeholder="" value="{{ old('keywords', $blog->keywords) }}">

              </div>

              <!-- Meta Description Field -->
              <div class="mb-3">
                <label for="meta-description" class="form-label">Meta Description </label>
                <textarea class="form-control" name="meta_description">{{ old('meta_description', $blog->meta_description) }}</textarea>
              </div>

              <!-- Action Buttons -->
              <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                <div>
                  <a href="{{ route('blogs.show', $blog->id) }}" class="btn btn-outline-secondary me-2">
                    <i class="bi bi-eye me-2"></i>View
                  </a>
                  <a href="{{ route('blogs.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-2"></i>Cancel
                  </a>
                </div>
                <div class="btn-group">
                  <button type="submit" class="btn btn-success">
                    <i class="bi bi-check-circle me-2"></i>Update Post
                  </button>
                  <a href="{{ route('blogs.create') }}" class="btn btn-outline-primary">
                    <i class="bi bi-plus-circle me-2"></i>Create New
                  </a>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<script src="https://cdn.tiny.cloud/1/lncr7awyr7i6uo7uglrisq0cw4hiscgxu46i1jieb0ksqfxx/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Image preview functionality
  const featuredImageInput = document.getElementById('featured_image');
  if (featuredImageInput) {
    featuredImageInput.addEventListener('change', function(e) {
      const file = e.target.files[0];
      const preview = document.getElementById('preview');
      const imagePreview = document.getElementById('image-preview');

      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          preview.src = e.target.result;
          imagePreview.classList.remove('d-none');
        }
        reader.readAsDataURL(file);
      } else {
        imagePreview.classList.add('d-none');
      }
    });
  }

  // Remove featured image checkbox logic
  const removeImageCheckbox = document.getElementById('remove_featured_image');
  if (removeImageCheckbox) {
    removeImageCheckbox.addEventListener('change', function() {
      if (this.checked) {
        featuredImageInput.disabled = true;
      } else {
        featuredImageInput.disabled = false;
      }
    });
  }

  // Initialize TinyMCE
  function initializeTinyMCE() {
    if (typeof tinymce === 'undefined') {
      console.error('TinyMCE not loaded');
      showFallbackEditor();
      return;
    }

    try {
      tinymce.init({
        selector: '#tinymce-editor',
        plugins: [
          'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
          'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
          'insertdatetime', 'media', 'table', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | blocks | bold italic underline | ' +
          'alignleft aligncenter alignright alignjustify | ' +
          'bullist numlist outdent indent | link image | ' +
          'removeformat | help',
        menubar: 'file edit view insert format tools table help',
        height: 500,
        promotion: false,
        branding: false,
        image_advtab: true,
        automatic_uploads: true,
        images_upload_url: '{{ route('tinymce.upload') }}',
        file_picker_types: 'image',
        images_upload_handler: function (blobInfo, progress) {
          return new Promise(function (resolve, reject) {
            let formData = new FormData();
            formData.append('file', blobInfo.blob(), blobInfo.filename());

            fetch('{{ route('tinymce.upload') }}', {
              method: 'POST',
              headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
              },
              body: formData
            })
            .then(response => {
              if (!response.ok) {
                throw new Error('Upload failed');
              }
              return response.json();
            })
            .then(result => {
              if (result.location) {
                resolve(result.location);
              } else {
                reject('Invalid response');
              }
            })
            .catch(error => {
              reject('Image upload failed: ' + error.message);
            });
          });
        },
        setup: function (editor) {
          editor.on('init', function () {
            document.getElementById('editor-loading').classList.add('d-none');
            document.getElementById('tinymce-editor').classList.remove('d-none');
          });

          editor.on('error', function (e) {
            console.error('TinyMCE error:', e);
          });
        }
      });
    } catch (error) {
      console.error('TinyMCE initialization error:', error);
      showFallbackEditor();
    }
  }

  function showFallbackEditor() {
    const editorLoading = document.getElementById('editor-loading');
    const textarea = document.getElementById('tinymce-editor');

    editorLoading.innerHTML =
      '<div class="alert alert-warning">' +
      '<h6>Editor Loading Failed</h6>' +
      '<p>Using basic text editor instead. For full features, please refresh the page.</p>' +
      '</div>';

    textarea.classList.remove('d-none');
    textarea.classList.add('form-control');
    textarea.style.height = '300px';
  }

  setTimeout(initializeTinyMCE, 100);
});

// Form validation
document.getElementById('blog-form')?.addEventListener('submit', function(e) {
  const title = document.getElementById('title').value.trim();
  const content = tinymce.get('tinymce-editor')?.getContent() || '';

  if (!title) {
    e.preventDefault();
    alert('Please enter a title for your blog post.');
    return;
  }

  if (!content.trim()) {
    e.preventDefault();
    alert('Please enter some content for your blog post.');
    return;
  }
});
</script>

@include('admin.layouts.footer')
