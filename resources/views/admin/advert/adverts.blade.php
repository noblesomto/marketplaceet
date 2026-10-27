@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">
  <div class="pagetitle">
    <h1>{{ $page_title }}</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin/dashboard">Home</a></li>
        <li class="breadcrumb-item active">Adverts</li>
      </ol>
    </nav>
  </div><!-- End Page Title -->

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
                  <h5 class="card-title mb-0">{{ $page_title }}</h5>
                  <div class="search-bar" style="max-width: 300px;">
                    <form class="d-flex" method="GET" action="{{ url()->current() }}">
                      <div class="input-group">
                        <input type="text"
                               class="form-control form-control-sm rounded-start"
                               name="query"
                               placeholder="Search adverts..."
                               title="Enter search keyword"
                               value="{{ request('query') }}">

                        <button class="btn btn-sm btn-primary" type="submit" title="Search">
                          <i class="bi bi-search"></i>
                        </button>

                        @if(request('query'))
                          <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-secondary" title="Clear search">
                            <i class="bi bi-x-circle"></i>
                          </a>
                        @endif
                      </div>
                    </form>
                  </div>
                </div>

            @if(session('status'))
              <div class="alert alert-{{session('status')['type']}} alert-dismissible fade show mt-3">
                {{session('status')['text']}}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            @endif

            @if(request('query'))
              <div class="alert alert-info mt-3">
                <i class="bi bi-info-circle"></i> Showing results for: <strong>{{ request('query') }}</strong>
                ({{ $adverts->total() }} {{ Str::plural('result', $adverts->total()) }})
              </div>
            @endif

            <div class="table-responsive mt-3">
              <table class="table table-hover">
                <thead class="table-light">
                  <tr>
                    <th scope="col">Image</th>
                    <th scope="col">Advert</th>
                    <th scope="col">Owner</th>
                    <th scope="col">Price</th>
                    <th scope="col">Location</th>
                    <th scope="col">{{ ($date_column ?? null) === 'disabled' ? 'Disabled' : 'Published' }}</th>
                    <th scope="col">Status</th>
                    <th scope="col">Sold</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($adverts as $row)
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
                    <td class="fw-bold"><a href="{{ url($row->state_slug . '/' . $row->title_slug .'/'. $row->ad_id) }}" target="_blank">{{ Str::limit($row->ad_title, 30) }}</a> </td>
                    <td>
                      <a href="/admin/view-user/{{ $row->user->user_id }}" class="text-primary">
                        {{ $row->user->name }}
                      </a>
                    </td>

                    @if($row->category==3)
                        <td>{{ $row->salary }}</td>
                    @elseif($row->category==18)
                        <td>{{ $row->expected_salary }}</td>
                    @elseif($row->contact_price=="yes")
                        <td>Contact For Price</td>
                    @else
                        <td>Br {{ number_format(floatval($row->price ?? 0), 2) }}</td>
                    @endif

                    <td>{{ $row->state }}</td>
                    <td>{{ date('j M Y', strtotime(($date_column ?? null) === 'disabled' ? $row->updated_at : $row->created_at)) }}</td>
                    <td>
                      @if($row->ad_status == 'active')
                        <span class="badge bg-success">Active</span>
                      @elseif($row->ad_status == 'disabled')
                        <span class="badge bg-warning text-dark">Disabled</span>
                      @elseif($row->ad_status == 'pending_review')
                        <span class="badge bg-info text-dark">Pending Review</span>
                      @else
                        <span class="badge bg-danger">Banned</span>
                      @endif
                    </td>
                    <td>
                      @if($row->sold == "Yes")
                        <span class="badge bg-danger">Sold</span>
                      @else
                        <span class="badge bg-info">Available</span>
                      @endif
                    </td>
                    <td>
                      <div class="btn-group" role="group">
                        @if($row->ad_status == 'active')
                          <button type="button" class="btn btn-sm btn-outline-warning"
                                  onclick="showBanModal('{{ $row->id }}', {{ Js::from(Str::limit($row->ad_title, 40)) }})"
                                  data-bs-toggle="tooltip" title="Disable/Ban">
                            <i class="bi bi-x-circle"></i>
                          </button>
                        @elseif($row->ad_status == 'banned')
                          <button type="button" class="btn btn-sm btn-outline-success"
                                  onclick="return confirm('Re-enable this advert? It will become visible to the public again.') && submitAdvertAction('{{ route('admin.advert.approve', $row->id) }}')"
                                  data-bs-toggle="tooltip" title="Approve / Enable">
                            <i class="bi bi-check-all"></i>
                          </button>
                        @elseif($row->ad_status == 'pending_review')
                          <a href="{{ route('admin.pending.review.adverts') }}" class="btn btn-sm btn-outline-info"
                             data-bs-toggle="tooltip" title="Review in Pending Review queue">
                            <i class="bi bi-eye"></i>
                          </a>
                        @else
                          <a href="/admin/advert-status/{{ $row->id }}/active" class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip" title="Enable">
                            <i class="bi bi-check-all"></i>
                          </a>
                        @endif

                        <a href="/admin/edit-ad/{{ $row->id }}" class="btn btn-sm btn-outline-dark"
                           data-bs-toggle="tooltip" title="Edit">
                          <i class="bi bi-pencil"></i>
                        </a>

                        @if($row->sold == "No")
                          <a href="/admin/sold-status/{{ $row->id }}/Yes" class="btn btn-sm btn-outline-danger"
                             onclick="return confirm('Mark this advert as sold?')" data-bs-toggle="tooltip" title="Mark Sold">
                            <i class="bi bi-check2-circle"></i>
                          </a>
                        @else
                          <a href="/admin/sold-status/{{ $row->id }}/No" class="btn btn-sm btn-outline-info"
                             onclick="return confirm('Mark this advert as available?')" data-bs-toggle="tooltip" title="Mark Available">
                            <i class="bi bi-x-square"></i>
                          </a>
                        @endif

                        @if($row->redirect == "No")
                          <a href="/admin/redirect-status/{{ $row->id }}/Yes" class="btn btn-sm btn-outline-info"
                             onclick="return confirm('Redirect to Home page?')" data-bs-toggle="tooltip" title="Redirect Home Page">
                            <i class="bi bi-link"></i>
                          </a>
                        @else
                          <a href="/admin/redirect-status/{{ $row->id }}/No" class="btn btn-sm btn-outline-warning"
                             onclick="return confirm('Redirect to Ad Page?')" data-bs-toggle="tooltip" title="Redirect Ad Page">
                            <i class="bi bi-link-45deg"></i>
                          </a>
                        @endif

                        <form action="{{ route('admin.delete.ad', $row->id) }}" method="POST" style="display: inline;">
                          @csrf
                          @method('DELETE')
                          <button type="submit" class="btn btn-sm btn-outline-dark"
                                  onclick="return confirm('Delete this advert permanently?')"
                                  data-bs-toggle="tooltip" title="Delete">
                            <i class="bi bi-trash"></i>
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="9" class="text-center py-4">
                      <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                      <p class="mt-2 text-muted">
                        @if(request('query'))
                          No adverts found matching "{{ request('query') }}"
                        @else
                          No adverts available
                        @endif
                      </p>
                    </td>
                  </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            @if($adverts->total() > 0)
            <div class="row mt-3">
              <div class="col-md-6">
                <div class="text-muted">
                  Showing {{ $adverts->firstItem() }} to {{ $adverts->lastItem() }} of {{ $adverts->total() }} entries
                </div>
              </div>
              <div class="col-md-6">
                <div class="float-end">
                  {{ $adverts->appends(['query' => request('query')])->links('pagination::bootstrap-4') }}
                </div>
              </div>
            </div>
            @endif

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

  function showBanModal(advertId, advertTitle) {
    document.getElementById('banModalAdvertTitle').textContent = advertTitle;
    document.getElementById('banForm').action = '/admin/advert-ban/' + advertId;
    document.getElementById('banReasonCategory').value = '';
    document.getElementById('banReasonNote').value = '';
    new bootstrap.Modal(document.getElementById('banModal')).show();
  }

  function validateBanForm() {
    const category = document.getElementById('banReasonCategory').value;
    const note = document.getElementById('banReasonNote');
    if (category === 'other' && !note.value.trim()) {
      note.focus();
      alert('Please add a note describing the reason when selecting "Other".');
      return false;
    }
    return true;
  }

  function submitAdvertAction(url) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}">`;
    document.body.appendChild(form);
    form.submit();
    return false;
  }
</script>

{{-- Ban/disable advert modal --}}
<div class="modal fade" id="banModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" id="banForm" action="" onsubmit="return validateBanForm()">
        @csrf
        <div class="modal-header border-0">
          <h6 class="modal-title text-danger"><i class="bi bi-x-circle me-2"></i>Disable / Ban Advert</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body pt-0">
          <p class="text-muted mb-3">Disabling <strong id="banModalAdvertTitle"></strong>. The seller will be emailed the reason below and can fix and resubmit it for review.</p>

          <label class="form-label fw-semibold">Reason</label>
          <select name="reason_category" id="banReasonCategory" class="form-select mb-3" required>
            <option value="" disabled selected>Select a reason...</option>
            @foreach(\App\Models\AdvertModerationLog::REASON_CATEGORIES as $key => $label)
              <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
          </select>

          <label class="form-label fw-semibold">Note <span class="text-muted fw-normal">(shown to the seller)</span></label>
          <textarea name="reason_note" id="banReasonNote" class="form-control" rows="3"
                    placeholder="Add specific details to help the seller fix the issue..." maxlength="1000"></textarea>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class="bi bi-x-circle me-1"></i>Disable Advert
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@include('admin.layouts.footer')
