@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">

<div class="pagetitle">
  <h1>All Plans</h1>

</div><!-- End Page Title -->

<section class="section">
  <div class="row">


    <div class="col-lg-12">

      <div class="card">
        <div class="card-body">
          <h5 class="card-title">All Plans</h5>
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
                <th scope="col">Plan Name</th>
                <th scope="col">Min Deposit</th>
                <th scope="col">Max Deposit</th>
                <th scope="col">Edit</th>
 
              </tr>
            </thead>
            <tbody>
            @foreach ( $plan as $row )
              <tr>
                <td>{{ $row->plan }}</td>
                <td>${{ number_format($row->min_deposit, 2, '.', ',') }}</td>
                <td>${{ number_format($row->max_deposit, 2, '.', ',') }}</td>
                
                <td><a class="" href="/plan/edit-plan/{{ $row->plan_id }}">Edit Plan</a></td>
                <!--
                <td><a href="/paln/delete-plan/{{ $row->plan_id }}" onclick="return confirm('Are you sure you want to delete?');">Delete Plan</a></td>
              -->
              </tr>
            @endforeach
              
            </tbody>
          </table>
        </div>
          <!-- End Table with stripped rows -->
          <div class="row">
            <div class="col-lg-8 offset-md-2">
               
            </div>
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