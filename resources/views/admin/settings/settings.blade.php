@include('admin.layouts.header')
@include('admin.layouts.nav')

<script src="{{ asset('backend/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('backend/js/jquery.min.js') }}"></script>
<script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
<main id="main" class="main">

    <div class="pagetitle">
    <h1>Site Settings</h1>
  
    </div><!-- End Page Title -->
    
    <section class="section">
    <div class="row">
    <div class="col-lg-12">
    
        <div class="card">
        <div class="card-body">
            <h5 class="card-title">Edit Settings</h5>
            @if(session('status'))
                <div class="alert alert-{{session('status')['type']}}">
                    {{session('status')['text']}}
                </div>
            @endif
            <!-- General Form Elements -->
            <form action="/admin/site-settings" method="POST" role="form" class="" >
             @csrf 
    
            <div class="row mb-3">
                <label for="inputText" class="col-sm-2 col-form-label">Site Name</label>
                <div class="col-sm-10">
          
                <input type="text" name="site_name" class="form-control" placeholder="Site Name" value="{{ $post->site_name }}" required>
                </div>
            </div>

            <div class="row mb-3">
                <label for="inputText" class="col-sm-2 col-form-label">Admin Email </label>
                <div class="col-sm-10">
            
                <input type="text" name="admin_email" class="form-control" placeholder="Site Email" value="{{ $post->admin_email }}" required>
                </div>
            </div>

            <div class="row mb-3">
                <label for="inputText" class="col-sm-2 col-form-label">Referal Amount</label>
                <div class="col-sm-10">

                <input type="text" name="referal_amount" class="form-control" placeholder="Referal Amount" value="{{ $post->referal_amount }}" required>
                </div>
            </div>


            
    
    
            <div class="row mb-3">
                <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </div>
    
            </form><!-- End General Form Elements -->
    
        </div>
        </div>
    
    </div>
    
    </div>
    </section>
    
    </main><!-- End #main -->

    @include('admin.layouts.footer')