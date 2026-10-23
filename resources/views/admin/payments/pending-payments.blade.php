@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">
  <div class="pagetitle">
    <h1>{{ $page_title }}</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin/dashboard">Home</a></li>
        <li class="breadcrumb-item active">{{ $page_title }}</li>
      </ol>
    </nav>
  </div><!-- End Page Title -->
   @if(session('status'))
      <div class="alert alert-{{session('status')['type']}} alert-dismissible fade show">
        {{session('status')['text']}}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    @endif
  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-center">
              <h5 class="card-title mb-0">{{ $page_title }}</h5>
              <div class="d-flex">
                <input type="text" class="form-control me-2" id="searchInput" placeholder="Search..." style="width: 200px;">
                <button class="btn btn-primary">Export</button>
              </div>
            </div>

            @if(session('status'))
              <div class="alert alert-{{session('status')['type']}} alert-dismissible fade show mt-3">
                {{session('status')['text']}}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            @endif

            <div class="table-responsive mt-3">
              <table class="table table-striped table-hover">
                <thead class="table-light">
                  <tr>
                    <th scope="col" width="50">#</th>
                    <th scope="col">Advert</th>
                    <th scope="col">Buyer</th>
                    <th scope="col">Seller</th>
                    <th scope="col">Price</th>
                    <th scope="col">Commission</th>
                    <th scope="col">Paid</th>
                    <th scope="col">Date</th>
                    <th scope="col">Status</th>
                    <th scope="col" width="180">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @forelse ($payments as $row)
                    <tr>
                      <td>{{ $loop->iteration + ($payments->currentPage() - 1) * $payments->perPage() }}</td>
                      <td>
                        <a href="{{ url($row->advert->state_slug . '/' . $row->advert->title_slug . '/' . $row->advert->ad_id) }}"
                           target="_blank" rel="noopener"
                           class="d-flex align-items-center text-body text-decoration-none">
                          @if($row->advert && $row->advert->hasMedia('images'))
                            <img width="50" height="50" src="{{ $row->advert->getFirstMediaUrl('images', 'thumbnail') }}"
                                 class="rounded me-2 object-fit-cover" alt="Ad Image">
                          @else
                            <img width="50" height="50" src="{{ asset('frontend/images/default.png') }}"
                                 class="rounded me-2 object-fit-cover" alt="Default Image">
                          @endif
                          <span class="text-truncate" style="max-width: 150px;">{{ $row->advert->ad_title }}</span>
                        </a>
                      </td>
                      <td>
                        <div class="d-flex align-items-center">
                          <span class="avatar-initial avatar-buyer me-2">{{ strtoupper(substr($row->user->name ?? '?', 0, 1)) }}</span>
                          <a href="/admin/view-user/{{ $row->user->user_id }}" class="text-primary">
                            {{ $row->user->name }}
                          </a>
                        </div>
                      </td>
                      <td>
                        @if($row->advert->owner)
                          <div class="d-flex align-items-center">
                            <span class="avatar-initial avatar-seller me-2">{{ strtoupper(substr($row->advert->owner->name, 0, 1)) }}</span>
                            <a href="/admin/view-user/{{ $row->advert->owner->user_id }}" class="text-primary">
                              {{ $row->advert->owner->name }}
                            </a>
                          </div>
                        @else
                          <span class="text-muted">&mdash;</span>
                        @endif
                      </td>
                      <td>Br {{ number_format($row->advert->price, 2) }}</td>
                      <td>Br {{ number_format($row->commission, 2) }}</td>
                      <td>Br {{ number_format($row->amount_paid, 2) }}</td>
                      <td>{{ date('M j, Y', strtotime($row->created_at)) }}</td>
                      <td>
                        <span class="badge rounded-pill bg-{{ $row->payment_status == 'paid' ? 'success' : 'danger' }}">
                          {{ ucfirst($row->payment_status) }}
                        </span>
                      </td>
                      <td>
                        <div class="d-flex align-items-center gap-1">
                          <button type="button"
                                  class="btn btn-sm btn-outline-secondary contact-btn"
                                  data-bs-toggle="modal"
                                  data-bs-target="#contactModal"
                                  data-buyer-name="{{ $row->user->name ?? 'N/A' }}"
                                  data-buyer-phone="{{ $row->user->phone ?? '' }}"
                                  data-buyer-email="{{ $row->user->email ?? '' }}"
                                  data-seller-name="{{ $row->advert->owner->name ?? 'N/A' }}"
                                  data-seller-phone="{{ $row->advert->owner->phone ?? '' }}"
                                  data-seller-email="{{ $row->advert->owner->email ?? '' }}"
                                  title="View buyer & seller contact details">
                            <i class="bi bi-person-lines-fill"></i>
                          </button>
                          @if($row->payment_status == "paid")
                            <span class="text-success">Confirmed</span>
                          @else
                            <button class="btn btn-sm btn-outline-primary confirm-btn"
                                    data-id="{{ $row->id }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#confirmModal">
                              <i class="bi bi-check-circle"></i> Confirm
                            </button>
                          @endif
                        </div>
                      </td>
                    </tr>
                  @empty
                    <tr>
                      <td colspan="10" class="text-center py-4">No payment records found</td>
                    </tr>
                  @endforelse
                </tbody>
              </table>
            </div>

            <div class="row mt-3">
              <div class="col-md-6">
                <div class="text-muted">
                  Showing {{ $payments->firstItem() }} to {{ $payments->lastItem() }} of {{ $payments->total() }} entries
                </div>
              </div>
              <div class="col-md-6">
                <div class="d-flex justify-content-end">
                  {{ $payments->links('pagination::bootstrap-4') }}
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirm Payment</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>Are you sure you want to confirm this payment?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <form id="confirmForm" method="POST">
          @csrf
          <button type="submit" class="btn btn-primary">Confirm Payment</button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Contact Details Modal -->
<div class="modal fade" id="contactModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-person-lines-fill me-2"></i>Contact Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="row g-3">
          <div class="col-md-6">
            <div class="contact-card">
              <div class="contact-card-label text-primary"><i class="bi bi-bag-check me-1"></i> Buyer</div>
              <div class="fw-semibold mb-3" id="contactBuyerName">&mdash;</div>
              <div class="contact-row mb-2">
                <i class="bi bi-telephone text-muted"></i>
                <a href="#" id="contactBuyerPhone" class="text-decoration-none">&mdash;</a>
              </div>
              <div class="contact-row">
                <i class="bi bi-envelope text-muted"></i>
                <a href="#" id="contactBuyerEmail" class="text-decoration-none">&mdash;</a>
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="contact-card">
              <div class="contact-card-label text-success"><i class="bi bi-shop me-1"></i> Seller</div>
              <div class="fw-semibold mb-3" id="contactSellerName">&mdash;</div>
              <div class="contact-row mb-2">
                <i class="bi bi-telephone text-muted"></i>
                <a href="#" id="contactSellerPhone" class="text-decoration-none">&mdash;</a>
              </div>
              <div class="contact-row">
                <i class="bi bi-envelope text-muted"></i>
                <a href="#" id="contactSellerEmail" class="text-decoration-none">&mdash;</a>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<style>
  .avatar-initial {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    font-size: 0.75rem;
    font-weight: 600;
    color: #fff;
    flex-shrink: 0;
  }
  .avatar-buyer { background-color: #4154f1; }
  .avatar-seller { background-color: #2eca6a; }
  .contact-card {
    background-color: #f6f9ff;
    border: 1px solid #eceefe;
    border-radius: 8px;
    padding: 16px;
    height: 100%;
  }
  .contact-card-label {
    text-transform: uppercase;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    margin-bottom: 6px;
  }
  .contact-row {
    display: flex;
    align-items: flex-start;
    gap: 10px;
  }
  .contact-row i {
    flex-shrink: 0;
    margin-top: 0.2rem;
  }
  .contact-row a {
    min-width: 0;
    overflow-wrap: anywhere;
  }
</style>

@include('admin.layouts.footer')

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Handle confirm button click
    document.querySelectorAll('.confirm-btn').forEach(button => {
      button.addEventListener('click', function() {
        const paymentId = this.getAttribute('data-id');
        document.getElementById('confirmForm').action = `/admin/confirm-payment/${paymentId}`;
      });
    });

    // Handle contact details button click
    function setContactField(nameEl, linkEl, value, hrefPrefix) {
      if (value) {
        linkEl.textContent = value;
        linkEl.href = hrefPrefix + value;
        linkEl.classList.remove('text-muted');
      } else {
        linkEl.textContent = 'Not provided';
        linkEl.removeAttribute('href');
        linkEl.classList.add('text-muted');
      }
    }

    document.querySelectorAll('.contact-btn').forEach(button => {
      button.addEventListener('click', function() {
        document.getElementById('contactBuyerName').textContent = this.getAttribute('data-buyer-name') || 'N/A';
        setContactField(null, document.getElementById('contactBuyerPhone'), this.getAttribute('data-buyer-phone'), 'tel:');
        setContactField(null, document.getElementById('contactBuyerEmail'), this.getAttribute('data-buyer-email'), 'mailto:');

        document.getElementById('contactSellerName').textContent = this.getAttribute('data-seller-name') || 'N/A';
        setContactField(null, document.getElementById('contactSellerPhone'), this.getAttribute('data-seller-phone'), 'tel:');
        setContactField(null, document.getElementById('contactSellerEmail'), this.getAttribute('data-seller-email'), 'mailto:');
      });
    });

    // Simple search functionality
    document.getElementById('searchInput').addEventListener('keyup', function() {
      const input = this.value.toLowerCase();
      const rows = document.querySelectorAll('tbody tr');

      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(input) ? '' : 'none';
      });
    });
  });
</script>
