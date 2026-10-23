@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">
  <div class="pagetitle">
    <h1>{{ $page_title }}</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin/dashboard">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('blogs.index') }}">Blogs</a></li>
        <li class="breadcrumb-item active">Create</li>
      </ol>
    </nav>
  </div><!-- End Page Title -->

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title">Create New Blog Post</h5>

            <!-- Loading Spinner for Editor -->
            <div id="editor-loading" class="text-center py-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading editor...</span>
              </div>
              <p class="mt-2 text-muted">Loading editor...</p>
            </div>

            <form action="{{ route('blogs.store') }}" method="POST" enctype="multipart/form-data" id="blog-form">
              @csrf

              <!-- Title Field -->
              <div class="mb-3">
                <label for="title" class="form-label">Post Title <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="title" name="title" placeholder="Enter a compelling title for your blog post" required>
                <div class="form-text">A good title captures attention and summarizes your content.</div>
              </div>

              <!-- Category Field -->
              <div class="mb-3">
                <label for="title" class="form-label">Categories <span class="text-danger">*</span></label>
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
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>

              </div>

              <!-- Featured Image Field -->
              <div class="mb-3">
                <label for="featured_image" class="form-label">Featured Image</label>
                <input type="file" class="form-control" id="featured_image" name="featured_image" accept="image/*">
                <div class="form-text">PNG, JPG, GIF up to 10MB. Recommended size: 1200x630px.</div>
                <div id="image-preview" class="mt-2 d-none">
                  <p class="text-muted">Preview:</p>
                  <img id="preview" class="img-thumbnail" style="max-height: 200px;">
                </div>
              </div>

              <!-- Content Field -->
              <div class="mb-3">
                <label for="tinymce-editor" class="form-label">Content <span class="text-danger">*</span></label>
                <textarea id="tinymce-editor" name="content" class="d-none"></textarea>
                <div class="form-text">Write your blog content using the editor above. You can add images, format text, and more.</div>
              </div>

              <!-- Keywords Field -->
              <div class="mb-3">
                <label for="title" class="form-label">Keywords </label>
                <input type="text" class="form-control" id="title" name="keywords" placeholder="">

              </div>

              <!-- Meta Description Field -->
              <div class="mb-3">
                <label for="meta-description" class="form-label">Meta Description </label>
                <textarea class="form-control" name="meta_description"></textarea>
              </div>

              <!-- Action Buttons -->
              <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                <a href="{{ route('blogs.index') }}" class="btn btn-secondary">
                  <i class="bi bi-arrow-left me-2"></i>Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                  <i class="bi bi-send me-2"></i>Publish Blog Post
                </button>
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

  // Initialize TinyMCE with simpler configuration
  function initializeTinyMCE() {
    // Check if TinyMCE is available
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
        promotion: false, // Remove TinyMCE promotion
        branding: false, // Remove TinyMCE branding
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
            // Hide loading spinner and show editor
            document.getElementById('editor-loading').classList.add('d-none');
            document.getElementById('tinymce-editor').classList.remove('d-none');
            console.log('TinyMCE initialized successfully');
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

  // Fallback to textarea if TinyMCE fails
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

  // Initialize TinyMCE after a short delay to ensure DOM is ready
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
