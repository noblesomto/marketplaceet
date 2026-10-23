

@if ($message = Session::get('success'))
<div class="bg-green-100 flex justify-center items-center w-full h-16 text-base my-2 rounded-lg p-3 ">
	<h4 class="font-semibold text-lg text-green-900">{{ $message }}</h4>
</div>
@endif


@if ($message = Session::get('error'))
<div class="bg-red-100 flex justify-center items-center w-full h-16 text-base my-2 rounded-lg p-3 ">
	<h4 class="font-semibold text-lg text-red-900">{{ $message }}</h4>
</div>
@endif


@if ($message = Session::get('warning'))
<div class="alert alert-warning alert-block">
	<button type="button" class="close" data-dismiss="alert">×</button>	
	<strong>{{ $message }}</strong>
</div>
@endif


@if ($message = Session::get('info'))
<div class="alert alert-info alert-block">
	<button type="button" class="close" data-dismiss="alert">×</button>	
	<strong>{{ $message }}</strong>
</div>
@endif


@if ($errors->any())
<div class="alert alert-danger">
	<button type="button" class="close" data-dismiss="alert">×</button>	
	Please check the form below for errors
</div>
@endif




