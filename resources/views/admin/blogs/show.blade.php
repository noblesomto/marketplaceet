@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">
  <div class="pagetitle">
    <h1>{{ $blog->title }}</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin/dashboard">Home</a></li>
        <li class="breadcrumb-item"><a href="{{ route('blogs.index') }}">Blogs</a></li>
        <li class="breadcrumb-item active">View</li>
      </ol>
    </nav>
  </div><!-- End Page Title -->

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h5 class="card-title">Blog Post Details</h5>
              <div class="btn-group">
                <a href="{{ route('blogs.edit', $blog->id) }}" class="btn btn-primary">
                  <i class="bi bi-pencil me-2"></i>Edit
                </a>
                <a href="{{ route('blogs.index') }}" class="btn btn-secondary">
                  <i class="bi bi-arrow-left me-2"></i>Back to Blogs
                </a>
              </div>
            </div>

            <!-- Success Message -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="bi bi-check-circle me-2"></i>
              {{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <div class="row">
              <!-- Featured Image -->
              @if($blog->featured_image_thumb)
              <div class="col-md-4 mb-4">
                <div class="card">
                  <div class="card-header">
                    <h6 class="card-title mb-0">Featured Image</h6>
                  </div>
                  <div class="card-body text-center">
                    <img src="{{ $blog->featured_image_thumb }}" alt="Featured Image" class="img-fluid rounded">
                  </div>
                </div>
              </div>
              @endif

              <!-- Blog Details -->
              <div class="{{ $blog->featured_image_thumb ? 'col-md-8' : 'col-12' }}">
                <div class="card">
                  <div class="card-header">
                    <h6 class="card-title mb-0">Post Information</h6>
                  </div>
                  <div class="card-body">
                    <table class="table table-borderless">
                      <tr>
                        <th width="30%">Title:</th>
                        <td>{{ $blog->title }}</td>
                      </tr>
                      <tr>
                        <th>Category:</th>
                        <td>{{ $blog->category }}</td>
                      </tr>
                      <tr>
                        <th>Created:</th>
                        <td>{{ $blog->created_at->format('F j, Y \a\t g:i A') }}</td>
                      </tr>
                      <tr>
                        <th>Last Updated:</th>
                        <td>{{ $blog->updated_at->format('F j, Y \a\t g:i A') }}</td>
                      </tr>
                      <tr>
                        <th>Status:</th>
                        <td>
                          <span class="badge badge {{ $blog->status === 'published' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($blog->status) }}</span>
                        </td>
                      </tr>
                    </table>
                  </div>
                </div>
              </div>
            </div>

            <!-- Content -->
            <div class="card mt-4">
              <div class="card-header">
                <h6 class="card-title mb-0">Content</h6>
              </div>
              <div class="card-body">
                <div class="blog-content">
                  {!! $blog->content !!}
                </div>
              </div>
            </div>

             <!-- Content -->
            <div class="card mt-4">
              <div class="card-header">
                <h6 class="card-title mb-0">Keywords</h6>
              </div>
              <div class="card-body">
                <div class="blog-content">
                  {!! $blog->keywords !!}
                </div>
              </div>
            </div>

             <!-- Content -->
            <div class="card mt-4">
              <div class="card-header">
                <h6 class="card-title mb-0">Meta Description</h6>
              </div>
              <div class="card-body">
                <div class="blog-content">
                  {!! $blog->meta_description !!}
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
              <div>
                <form action="{{ route('blogs.destroy', $blog->id) }}" method="POST" class="d-inline">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-outline-danger" onclick="return confirm('Are you sure you want to delete this blog post? This action cannot be undone.')">
                    <i class="bi bi-trash me-2"></i>Delete Post
                  </button>
                </form>
              </div>
              <div class="btn-group">
                <a href="{{ route('blogs.edit', $blog->id) }}" class="btn btn-primary">
                  <i class="bi bi-pencil me-2"></i>Edit Post
                </a>
                <a href="{{ route('blogs.index') }}" class="btn btn-secondary">
                  <i class="bi bi-list-ul me-2"></i>All Posts
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<style>
.blog-content {
  line-height: 1.6;
}

.blog-content img {
  max-width: 100%;
  height: auto;
  border-radius: 0.375rem;
}

.blog-content h1, .blog-content h2, .blog-content h3, .blog-content h4, .blog-content h5, .blog-content h6 {
  margin-top: 1.5rem;
  margin-bottom: 1rem;
}

.blog-content p {
  margin-bottom: 1rem;
}

.blog-content ul, .blog-content ol {
  margin-bottom: 1rem;
  padding-left: 2rem;
}

.blog-content blockquote {
  border-left: 4px solid #dee2e6;
  padding-left: 1rem;
  margin: 1rem 0;
  font-style: italic;
  color: #6c757d;
}
</style>

@include('admin.layouts.footer')
