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
            <h5 class="card-title">Change Password</h5>
            @if(session('status'))
                <div class="alert alert-{{session('status')['type']}}">
                    {{session('status')['text']}}
                </div>
            @endif
            <!-- General Form Elements -->
            <form action="/admin/change-password" method="POST" role="form" class="" >
             @csrf 
    
            <div class="row mb-3">
                <label for="inputText" class="col-sm-3 col-form-label">Current Password</label>
                <div class="col-sm-9">
                    @if ($errors->has('old_passsword'))
                        <span class="text-danger">{{ $errors->first('old_passsword') }}</span>
                    @endif
                <input type="password" name="old_password" class="form-control" placeholder="Current Password" required>
                </div>
            </div>

            <div class="row mb-3">
                <label for="inputText" class="col-sm-3 col-form-label">New Password </label>
                <div class="col-sm-9">
                @if ($errors->has('passsword'))
                        <span class="text-danger">{{ $errors->first('passsword') }}</span>
                    @endif
                <input type="password" name="password" class="form-control" placeholder="New Password"  required>
                </div>
            </div>

            <div class="row mb-3">
                <label for="inputText" class="col-sm-3 col-form-label">Confirm Password</label>
                <div class="col-sm-9">
                    @if ($errors->has('password_confirmation'))
                        <span class="text-danger">{{ $errors->first('password_confirmation') }}</span>
                    @endif
                <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm Password"  required>
                </div>
            </div>


            
    
    
            <div class="row mb-3">
                <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                <button type="submit" class="btn btn-primary">Change Password</button>
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