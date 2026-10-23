@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">
  <div class="pagetitle">
    <h1>{{ $page_title }}</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin/dashboard">Home</a></li>
        <li class="breadcrumb-item active">Pending Review</li>
      </ol>
    </nav>
  </div><!-- End Page Title -->

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <div>
                <h5 class="card-title mb-0">{{ $page_title }}</h5>
                <p class="text-muted mb-0">Adverts a seller has fixed and resubmitted after being disabled — review and approve or reject again.</p>
              </div>
              <div class="search-bar" style="max-width: 300px;">
                <form class="d-flex" method="GET" action="{{ url()->current() }}">
                  <div class="input-group">
                    <input type="text"
                           class="form-control form-control-sm rounded-start"
                           name="query"
                           placeholder="Search adverts..."
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

            <div class="table-responsive mt-3">
              <table class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th scope="col">Image</th>
                    <th scope="col">Advert</th>
                    <th scope="col">Owner</th>
                    <th scope="col">Original ban reason</th>
                    <th scope="col">Resubmitted</th>
                    <th scope="col">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse($adverts as $row)
                  @php
                      $lastReason = $row->moderationLogs->first(fn($log) => in_array($log->action, ['banned', 'rejected']));
                  @endphp
                  <tr>
                    <td>
                      <img src="{{ $row->hasMedia('images') ? $row->getFirstMediaUrl('images', 'thumbnail') : asset('frontend/images/default.png') }}"
                           class="img-thumbnail rounded"
                           style="width: 60px; height: 50px; object-fit: cover;"
                           alt="Advert Image">
                    </td>
                    <td class="fw-bold">
                      <a href="{{ url($row->state_slug . '/' . $row->title_slug .'/'. $row->ad_id) }}" target="_blank">
                        {{ Str::limit($row->ad_title, 30) }}
                      </a>
                      <div>
                        <a href="/admin/edit-ad/{{ $row->id }}" class="small">Review content <i class="bi bi-box-arrow-up-right"></i></a>
                      </div>
                    </td>
                    <td>
                      <a href="/admin/view-user/{{ $row->user->user_id }}" class="text-primary">
                        {{ $row->user->name }}
                      </a>
                    </td>
                    <td style="max-width: 260px;">
                      @if($lastReason)
                        <span class="badge bg-danger mb-1">{{ $lastReason->reason_category_label }}</span>
                        @if($lastReason->reason_note)
                          <div class="small text-muted">{{ Str::limit($lastReason->reason_note, 100) }}</div>
                        @endif
                        <div class="small text-muted">by {{ $lastReason->admin->username ?? 'Admin' }} · {{ $lastReason->created_at->diffForHumans() }}</div>
                      @else
                        <span class="text-muted small">No history found</span>
                      @endif
                    </td>
                    <td>{{ $row->resubmitted_at ? $row->resubmitted_at->diffForHumans() : '—' }}</td>
                    <td>
                      <div class="btn-group" role="group">
                        <button type="button" class="btn btn-sm btn-outline-success"
                                onclick="return confirm('Approve this advert? It will become visible to the public again.') && submitReviewAction('{{ route('admin.advert.approve', $row->id) }}')"
                                data-bs-toggle="tooltip" title="Approve">
                          <i class="bi bi-check-circle"></i> Approve
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger"
                                onclick="showRejectModal('{{ $row->id }}', {{ Js::from(Str::limit($row->ad_title, 40)) }})"
                                data-bs-toggle="tooltip" title="Reject">
                          <i class="bi bi-x-circle"></i> Reject
                        </button>
                      </div>
                    </td>
                  </tr>
                  @empty
                  <tr>
                    <td colspan="6" class="text-center py-4">
                      <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                      <p class="mt-2 text-muted">Nothing waiting for review right now.</p>
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
  document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function (el) { return new bootstrap.Tooltip(el) });
  });

  function submitReviewAction(url) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}">`;
    document.body.appendChild(form);
    form.submit();
    return false;
  }

  function showRejectModal(advertId, advertTitle) {
    document.getElementById('rejectModalAdvertTitle').textContent = advertTitle;
    document.getElementById('rejectForm').action = '/admin/advert-reject/' + advertId;
    document.getElementById('rejectReasonCategory').value = '';
    document.getElementById('rejectReasonNote').value = '';
    new bootstrap.Modal(document.getElementById('rejectModal')).show();
  }

  function validateRejectForm() {
    const category = document.getElementById('rejectReasonCategory').value;
    const note = document.getElementById('rejectReasonNote');
    if (category === 'other' && !note.value.trim()) {
      note.focus();
      alert('Please add a note describing the reason when selecting "Other".');
      return false;
    }
    return true;
  }
</script>

{{-- Reject resubmission modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" id="rejectForm" action="" onsubmit="return validateRejectForm()">
        @csrf
        <div class="modal-header border-0">
          <h6 class="modal-title text-danger"><i class="bi bi-x-circle me-2"></i>Reject Resubmission</h6>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body pt-0">
          <p class="text-muted mb-3">Rejecting <strong id="rejectModalAdvertTitle"></strong>. The seller will be emailed the reason below and can fix it and resubmit again.</p>

          <label class="form-label fw-semibold">Reason</label>
          <select name="reason_category" id="rejectReasonCategory" class="form-select mb-3" required>
            <option value="" disabled selected>Select a reason...</option>
            @foreach(\App\Models\AdvertModerationLog::REASON_CATEGORIES as $key => $label)
              <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
          </select>

          <label class="form-label fw-semibold">Note <span class="text-muted fw-normal">(shown to the seller)</span></label>
          <textarea name="reason_note" id="rejectReasonNote" class="form-control" rows="3"
                    placeholder="Add specific details to help the seller fix the issue..." maxlength="1000"></textarea>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger">
            <i class="bi bi-x-circle me-1"></i>Reject
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@include('admin.layouts.footer')
