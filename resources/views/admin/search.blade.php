@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">

<div class="pagetitle">
  <h1>Payment History</h1>

</div><!-- End Page Title -->

<section class="section">
  <div class="row">


    <div class="col-lg-12">

      <div class="card">
        <div class="card-body">
          <h5 class="card-title">Payment History</h5>
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
                <th scope="col">Full Name</th>
                <th scope="col">Email</th>
                <th scope="col">Phone</th>
                <th scope="col">Status</th>

                <th scope="col">Action</th>
              </tr>
            </thead>
            <tbody>
            @foreach ( $post as $row )
              <tr>
                <td>{{ $row->first_name }} {{ $row->last_name }}</td>
                <td> {{ $row->email }}</td>
                <td> {{ $row->phone }}</td>
  

                @if($row->acc_status =="0" )
                <td><span class="text-danger">Pending</span></td>
                @else
                <td><span class="text-success">Verified</span></td>
                @endif
                <td><a href="/admin/view-user/{{ $row->user_id }}">View Details</a></td>
              </tr>
            @endforeach
              
            </tbody>
          </table>
        </div>
          <!-- End Table with stripped rows -->

<div class="pagination-box text-center mt-50">
  <br>
    <nav aria-label="Page navigation example">
        <ul class="pagination">
            {!! $post->links() !!}
        </ul>
    </nav>
</div>

        </div>
      </div>

      



    </div>
  </div>
</section>

</main><!-- End #main -->
<script>
function myFunction() {
  confirm("Are you sure you want to delete?");
}
</script>
@include('admin.layouts.footer')