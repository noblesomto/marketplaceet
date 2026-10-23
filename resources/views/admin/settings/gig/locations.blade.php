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
          <h5 class="card-title">GIG Locations</h5>
          @if(session('status'))
                <div class="alert alert-{{session('status')['type']}}">
                    {{session('status')['text']}}
                </div>
            @endif
          <!-- Table with stripped rows -->
          <div class="table-responsive">
          <table class="table table-striped">
              <thead>
                <tr>
                  <th scope="col">State</th>
                  <th scope="col">City</th>
                  <th scope="col">Address</th>
                  <th scope="col">Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($gig as $row)
                <tr>
                  <td>{{ $row->state->name }}</td>
                  <td>{{ $row->city }}</td>
                  <td>{{ $row->address }}</td>
                  <td>
                    <button class="btn btn-sm btn-primary edit-btn"
                            data-id="{{ $row->id }}"
                            data-state="{{ $row->state_id }}"
                            data-city="{{ $row->city }}"
                            data-address="{{ $row->address }}">
                      Edit
                    </button>
                    <form action="{{ route('settings.delete.gig.location', $row->id) }}" method="POST" style="display: inline;">
                      @csrf
                      @method('DELETE')
                      <button type="submit"
                              onclick="return confirm('Are you sure?');"
                              class="btn btn-sm btn-danger">
                        Delete
                      </button>
                    </form>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
        </div>
          <!-- End Table with stripped rows -->
    
          <div class="row">
            <div class="col-lg-12">
                <div class="pagination-box text-center mt-50">

                    <div class="row">
                        <div class="col-md-6 d-flex justify-content-start">
                            Showing {{ $gig->firstItem() }} to {{ $gig->lastItem() }} of {{ $gig->total() }} results
                        </div>
                        <div class="col-md-6 d-flex justify-content-end">
                            {{ $gig->links('pagination::bootstrap-4') }}
                        </div>
                    </div>
                  <br>
                
                </div>
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
            <h5 class="card-title">New Location</h5>
       
            <!-- General Form Elements -->
            <form action="/settings/gig-locations" method="POST" role="form" class="" enctype="multipart/form-data">
             @csrf 
    
            <div class="row mb-3">
                <label for="inputText" class="col-sm-2 col-form-label">State</label>
                <div class="col-sm-10">
                    @if ($errors->has('state'))
                        <span class="text-danger">{{ $errors->first('state') }}</span>
                    @endif
                <select name="state" class="form-select" aria-label="Default select example" required>
                    <option value="">Select State</option>
                    @foreach ( $state as $row )
                    <option value="{{ $row->id }}">{{ $row->name }}</option>
                    @endforeach
                </select>
                </div>
            </div>

            <div class="row mb-3">
                <label for="inputText" class="col-sm-2 col-form-label">Add City</label>
                <div class="col-sm-10">
                    @if ($errors->has('city'))
                        <span class="text-danger">{{ $errors->first('city') }}</span>
                    @endif
                <input type="text" name="city" class="form-control" placeholder="Enter City" value="{{ old('city') }}" required>
                </div>
            </div>

     

            <div class="row mb-3">
                <label for="inputText" class="col-sm-2 col-form-label">Address</label>
                <div class="col-sm-10">
                    @if ($errors->has('address'))
                        <span class="text-danger">{{ $errors->first('address') }}</span>
                    @endif
                <input type="text" name="address" class="form-control" placeholder="Enter Location Address" value="{{ old('address') }}" required>
                </div>
            </div>
           
    
            <div class="row mb-3">
                <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                <button type="submit" class="btn btn-primary">Submit</button>
                </div>
            </div>
    
            </form><!-- End General Form Elements -->
    
        </div>
        </div>
    
    </div>
    
    </div>
    </section>

<!-- Edit Location Modal -->
<div class="modal fade" id="editLocationModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Location</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editLocationForm">
        <div class="modal-body">
          @csrf
          <input type="hidden" name="id" id="edit_id">

          <div class="row mb-3">
            <label class="col-sm-3 col-form-label">State</label>
            <div class="col-sm-9">
              <select name="state" id="edit_state" class="form-select" required>
                <option value="">Select State</option>
                @foreach ($state as $s)
                <option value="{{ $s->id }}">{{ $s->name }}</option>
                @endforeach
              </select>
              <div class="invalid-feedback" id="state_error"></div>
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-sm-3 col-form-label">City</label>
            <div class="col-sm-9">
              <input type="text" name="city" id="edit_city" class="form-control" required>
              <div class="invalid-feedback" id="city_error"></div>
            </div>
          </div>

          <div class="row mb-3">
            <label class="col-sm-3 col-form-label">Address</label>
            <div class="col-sm-9">
              <input type="text" name="address" id="edit_address" class="form-control" required>
              <div class="invalid-feedback" id="address_error"></div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

</main><!-- End #main -->
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
function myFunction() {
  confirm("Are you sure you want to delete?");
}
</script>
<script>
    $(document).ready(function() {
  // Initialize Bootstrap modal
  const editModal = new bootstrap.Modal(document.getElementById('editLocationModal'));

  // When edit button is clicked
  $('.edit-btn').click(function() {
    const id = $(this).data('id');
    const state = $(this).data('state');
    const city = $(this).data('city');
    const address = $(this).data('address');

    // Set values in modal form
    $('#edit_id').val(id);
    $('#edit_state').val(state);
    $('#edit_city').val(city);
    $('#edit_address').val(address);

    // Clear previous validation errors
    $('#edit_state, #edit_city, #edit_address').removeClass('is-invalid');
    $('.invalid-feedback').text('');

    // Show modal
    editModal.show();
  });

  // Handle form submission
  $('#editLocationForm').submit(function(e) {
    e.preventDefault();

    // Clear previous errors
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').text('');

    $.ajax({
      url: '/settings/update-gig-location',
      method: 'POST',
      data: $(this).serialize(),
      success: function(response) {
        if(response.success) {
          // Close modal
          editModal.hide();

          // Show success message
          alert('Location updated successfully!');

          // Reload the page to see changes
          location.reload();
        }
      },
      error: function(xhr) {
        if(xhr.status === 422) {
          // Validation errors
          const errors = xhr.responseJSON.errors;
          for(const field in errors) {
            $(`#edit_${field}`).addClass('is-invalid');
            $(`#${field}_error`).text(errors[field][0]);
          }
        } else {
          alert('Error updating location: ' + xhr.responseJSON.message);
        }
      }
    });
  });
});
</script>
@include('admin.layouts.footer')
