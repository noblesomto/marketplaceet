@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">
  <div class="pagetitle">
    <h1>{{ $page_title ?? 'Blog Management' }}</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin/dashboard">Home</a></li>
        <li class="breadcrumb-item active">Blogs</li>
      </ol>
    </nav>
  </div><!-- End Page Title -->

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h5 class="card-title">All Blog Posts</h5>
              <a href="{{ route('blogs.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Create New Post
              </a>
            </div>

            <!-- Success Message -->
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="bi bi-check-circle me-2"></i>
              {{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <!-- Blogs Table -->
            <div class="table-responsive">
              <table class="table table-striped table-hover">
                <thead>
                  <tr>
                    <th scope="col">#</th>
                    <th scope="col">Featured Image</th>
                    <th scope="col">Title</th>
                    <th scope="col">Category</th>
                    <th scope="col">Status</th>
                    <th scope="col">Created</th>
                    <th scope="col">Updated</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($blogs as $blog)
                  <tr>
                    <th scope="row">{{ $loop->iteration }}</th>
                    <td>
                      <img src="{{ $blog->featured_image_thumb }}" alt="{{ $blog->title }}" width="60">

                    </td>
                    <td>
                      <strong>{{ Str::limit($blog->title, 50) }}</strong>
                    </td>
                    <td>{{ $blog->category }}</td>
                    <td>
                        <button
                            class="badge {{ $blog->status === 'published' ? 'bg-success' : 'bg-secondary' }} toggle-status-btn border-0"
                            data-id="{{ $blog->id }}"
                            data-status="{{ $blog->status }}"
                        >
                            {{ ucfirst($blog->status) }}
                        </button>
                    </td>

                    <td>{{ $blog->created_at->format('M j, Y') }}</td>
                    <td>{{ $blog->updated_at->format('M j, Y') }}</td>
                    <td>
                      <div class="btn-group" role="group">
                        <a href="{{ route('blogs.show', $blog->id) }}" class="btn btn-sm btn-outline-info" title="View">
                          <i class="bi bi-eye"></i>
                        </a>
                        <a href="{{ route('blogs.edit', $blog->id) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                          <i class="bi bi-pencil"></i>
                        </a>
                        <form action="{{ route('blogs.destroy', $blog->id) }}" method="POST" class="d-inline">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Are you sure you want to delete this blog post?')">
                            <i class="bi bi-trash"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="6" class="text-center py-4">
                      <div class="text-muted">
                        <i class="bi bi-journal-text display-4 d-block mb-2"></i>
                        No blog posts found.
                        <a href="{{ route('blogs.create') }}" class="d-block mt-2">Create your first blog post</a>
                      </div>
                    </td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>


            <!-- Pagination -->
            @if($blogs->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-3">
              <div class="text-muted">
                Showing {{ $blogs->firstItem() }} to {{ $blogs->lastItem() }} of {{ $blogs->total() }} results
              </div>
              <nav>
                {{ $blogs->links('pagination::bootstrap-4') }}
              </nav>
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.toggle-status-btn').forEach(button => {
        button.addEventListener('click', function () {
            const blogId = this.dataset.id;
            const currentStatus = this.dataset.status;

            fetch(`/blogs/${blogId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
            })
            .then(response => response.json())
            .then(data => {
                this.textContent = data.label;
                this.dataset.status = data.status;
                this.classList.remove('bg-success', 'bg-secondary');
                this.classList.add(data.badgeClass);
            })
            .catch(error => console.error('Error:', error));
        });
    });
});
</script>

@include('admin.layouts.footer')
