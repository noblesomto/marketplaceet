@include('admin.layouts.header')
@include('admin.layouts.nav')

<script src="{{ asset('backend/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('backend/js/jquery.min.js') }}"></script>
<script src="{{ asset('ckeditor/ckeditor.js') }}"></script>
<main id="main" class="main">

    <div class="pagetitle">
    <h1>Investment Plans</h1>
  
    </div><!-- End Page Title -->
    
    <section class="section">
    <div class="row">
    <div class="col-lg-12">
    
        <div class="card">
        <div class="card-body">
            <h5 class="card-title">Edit Plan</h5>
            @if(session('status'))
                <div class="alert alert-{{session('status')['type']}}">
                    {{session('status')['text']}}
                </div>
            @endif
            <!-- General Form Elements -->
            <form action="/plan/edit-plan/{{ $post->plan_id }}" method="POST" role="form" class="" >
             @csrf 
             @method('PUT') 
    
            <div class="row mb-3">
                <label for="inputText" class="col-sm-2 col-form-label">Plan Name</label>
                <div class="col-sm-10">
                    @if ($errors->has('plan'))
                        <span class="text-danger">{{ $errors->first('plan') }}</span>
                    @endif
                <input type="text" name="plan" class="form-control" placeholder="Plan Name" value="{{ $post->plan }}" required>
                </div>
            </div>

            <div class="row mb-3">
                <label for="inputText" class="col-sm-2 col-form-label">Min Deposit </label>
                <div class="col-sm-10">
                    @if ($errors->has('min_deposit'))
                        <span class="text-danger">{{ $errors->first('min_deposit') }}</span>
                    @endif
                <input type="text" name="min_deposit" class="form-control" placeholder="Min Deposit" value="{{ $post->min_deposit }}" required>
                </div>
            </div>

            <div class="row mb-3">
                <label for="inputText" class="col-sm-2 col-form-label">Max Deposit</label>
                <div class="col-sm-10">
                    @if ($errors->has('max_deposit'))
                        <span class="text-danger">{{ $errors->first('max_deposit') }}</span>
                    @endif
                <input type="text" name="max_deposit" class="form-control" placeholder="Max Deposit" value="{{ $post->max_deposit }}" required>
                </div>
            </div>


            <div class="row mb-3">
                <label for="inputText" class="col-sm-2 col-form-label">Interest Rate</label>
                <div class="col-sm-10">
                    @if ($errors->has('interest'))
                        <span class="text-danger">{{ $errors->first('interest') }}</span>
                    @endif
                <input type="text" name="interest" class="form-control" placeholder="Interest Rate" value="{{ $post->interest }}" required>
                </div>
            </div>


            <div class="row mb-3">
                <label for="inputText" class="col-sm-2 col-form-label">Total Return</label>
                <div class="col-sm-10">
                    @if ($errors->has('total_return'))
                        <span class="text-danger">{{ $errors->first('total_return') }}</span>
                    @endif
                <input type="text" name="total_return" class="form-control" placeholder="Total Return" value="{{ $post->total_return }}" required>
                </div>
            </div>


            <div class="row mb-3">
                <label for="inputText" class="col-sm-2 col-form-label">Num Days</label>
                <div class="col-sm-10">
                    @if ($errors->has('num_days'))
                        <span class="text-danger">{{ $errors->first('num_days') }}</span>
                    @endif
                <input type="text" name="num_days" class="form-control" placeholder="Num Days" value="{{ $post->num_days }}" required>
                </div>
            </div>
    
            
    
    
            <div class="row mb-3">
                <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10">
                <button type="submit" class="btn btn-primary">Edit</button>
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