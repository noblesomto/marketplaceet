@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">
  <div class="pagetitle">
    <h1>{{ $page_title }}</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin/dashboard">Home</a></li>
        <li class="breadcrumb-item active">Adverts Management</li>
      </ol>
    </nav>
  </div><!-- End Page Title -->

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-4">
              <h5 class="card-title mb-0">Adverts List</h5>
              <div class="d-flex">
                <div class="search-bar me-2" style="width: 250px;">
                  <form class="search-form d-flex align-items-center">
                    <input type="text" name="query" placeholder="Search adverts..." title="Enter search keyword">
                    <button type="submit" title="Search"><i class="bi bi-search"></i></button>
                  </form>
                </div>
                <button class="btn btn-sm btn-outline-primary">
                  <i class="bi bi-filter"></i> Filters
                </button>
              </div>
            </div>

            @if(session('status'))
              <div class="alert alert-{{session('status')['type']}} alert-dismissible fade show">
                <i class="bi bi-check-circle me-1"></i> {{session('status')['text']}}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            @endif

            <div class="table-responsive">
              <table class="table table-hover table-bordered">
                <thead class="table-light">
                  <tr>
                    <th width="80">Image</th>
                    <th>Advert</th>
                    <th>Owner</th>
                    <th>Price</th>
                    <th>Location</th>
                    <th>Ship ID</th>
                    <th>Published</th>
                    <th>Sold Date</th>
                    <th>Status</th>
                    <th colspan="3" class="text-center">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($adverts as $row)
                  <tr>
                    <td>
                      <img src="{{ $row->hasMedia('images') ? $row->getFirstMediaUrl('images', 'thumbnail') : asset('frontend/images/default.png') }}"
                           class="img-thumbnail rounded"
                           style="width: 60px; height: 50px; object-fit: cover;"
                           alt="Advert Image"
                           data-bs-toggle="tooltip"
                           data-bs-placement="top"
                           title="View image">
                    </td>
                    <td class="fw-bold">{{ Str::limit($row->ad_title, 25) }}</td>
                    <td>
                      <a href="/admin/view-user/{{ $row->user->user_id }}" class="text-primary text-decoration-none">
                        {{ Str::limit($row->user->name, 15) }}
                      </a>
                    </td>
                    <td class="text-nowrap">Br {{ number_format($row->price, 2) }}</td>
                    <td>{{ $row->state }}</td>
                    <td>
                      @if($row->ship_code)
                        <span class="badge bg-info text-dark">{{ $row->ship_code }}</span>
                      @else
                        <span class="text-muted">N/A</span>
                      @endif
                    </td>
                    <td class="text-nowrap">{{ date('j M Y', strtotime($row->created_at)) }}</td>
                    <td class="text-nowrap">
                      @if($row->sold_date)
                        {{ date('j M Y', strtotime($row->sold_date)) }}
                      @else
                        <span class="text-muted">-</span>
                      @endif
                    </td>
                    <td>
                      @if($row->ad_status == 1)
                        <span class="badge bg-success">Active</span>
                      @else
                        <span class="badge bg-secondary">Disabled</span>
                      @endif
                    </td>
                    <td class="text-center">
                      @if($row->ad_status == 1)
                        <a href="/admin/advert-status/{{ $row->id }}/0" class="btn btn-sm btn-outline-warning" data-bs-toggle="tooltip" title="Disable">
                          <i class="bi bi-x-circle"></i>
                        </a>
                      @else
                        <a href="/admin/advert-status/{{ $row->id }}/1" class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="Enable">
                          <i class="bi bi-check-circle"></i>
                        </a>
                      @endif
                    </td>
                    <td class="text-center">
                      @if($row->sold == "No")
                        <a href="/admin/sold-status/{{ $row->id }}/Yes" class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Mark this advert as sold?')" data-bs-toggle="tooltip" title="Mark Sold">
                          <i class="bi bi-check2-circle"></i>
                        </a>
                      @else
                        <a href="/admin/sold-status/{{ $row->id }}/No" class="btn btn-sm btn-outline-info"
                           onclick="return confirm('Mark this advert as available?')" data-bs-toggle="tooltip" title="Mark Available">
                          <i class="bi bi-x-circle"></i>
                        </a>
                      @endif
                    </td>
                    <td class="text-center">
                      <form action="{{ route('admin.delete.ad', $row->id) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-dark"
                                onclick="return confirm('Permanently delete this advert?')"
                                data-bs-toggle="tooltip" title="Delete">
                          <i class="bi bi-trash"></i>
                        </button>
                      </form>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>

            <div class="row mt-3">
              <div class="col-md-6">
                <div class="text-muted">
                  Showing {{ $adverts->firstItem() }} to {{ $adverts->lastItem() }} of {{ $adverts->total() }} entries
                </div>
              </div>
              <div class="col-md-6">
                <div class="float-end">
                  {{ $adverts->links('pagination::bootstrap-4') }}
                </div>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<script>
  // Enable tooltips
  document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    });
  });
</script>

@include('admin.layouts.footer')
