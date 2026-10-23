@include('user.layouts.header')
@include('user.layouts.back-nav')
@include('user.layouts.search')

<section class="w-full md:w-3/6 bg-white mx-auto p-3 text-sm pb-20">
    <div class="border-b-2 border-b-gray-200 pt-4 px-2 font-bold text-dark_green mb-2">
        Profile Name & Address
        @include('public.components.flash-message')
    </div>
  
		            
		        	<div class="max-w-2xl mx-auto bg-white p-3 rounded-lg">
				        <form method="POST" action="{{ route('user.profile.address') }}" enctype="multipart/form-data">
				            @csrf
				       
				        <div class="mb-4 mt-1">
				            
				            <label class="text-sm font-semibold">Name *</label>
				            @if ($errors->has('name'))
				                <span class="text-red-700 py-1">{{ $errors->first('name') }}</span>
				            @endif
				            <input type="text" id="name" name="name" placeholder="Name" value="{{ $user->name }}" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
				        </div>

				        <div class="mb-4 mt-4">
				            <label class="text-sm font-semibold">Address</label>
				            @if ($errors->has('address'))
				                <span class="text-red-700 py-1">{{ $errors->first('address') }}</span>
				            @endif
				            <input type="text" id="name" name="address" placeholder="Address" value="{{ $user->address }}" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
				        </div>

				        <div class="mb-4 mt-4">
				            <label class="text-sm font-semibold">City</label>
				            @if ($errors->has('city'))
				                <span class="text-red-700 py-1">{{ $errors->first('city') }}</span>
				            @endif
				            <input type="text" id="name" name="city" placeholder="City" value="{{ $user->city }}" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" >
				        </div>


				        <div class="mb-4 mt-4">
				            <label class="text-sm font-semibold">State</label>
				            @if ($errors->has('state'))
				                <span class="text-red-700 py-1">{{ $errors->first('state') }}</span>
				            @endif
<select name="state" id="state" class="w-full bg-body-100 px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
    <option value="" {{ old('state', $user->state ?? '') == '' ? 'selected' : '' }}>- Select Region -</option>
    @foreach($states as $stateOption)
        <option value="{{ $stateOption->name }}" {{ old('state', $user->state ?? '') == $stateOption->name ? 'selected' : '' }}>{{ $stateOption->name }}</option>
    @endforeach
</select>
				        </div>

				        
				        <div class="mb-6 mt-4">
                            <div class="flex items-start space-x-4">
                                <!-- Profile Thumbnail -->
                                <div class="flex-shrink-0">
                                    <img
                                        class="w-16 h-16 rounded-full object-cover border"
                                        src="{{ $user->profile_thumbnail_url }}"
                                        alt="{{ $user->name }}">
                                </div>

                                <!-- Upload Input -->
                                <div class="flex-1">
                                    <label for="profile_image" class="block text-gray-700 text-sm font-bold mb-2">
                                        Profile Image
                                    </label>

                                    @if ($errors->has('profile_image'))
                                        <span class="text-red-600 text-xs block mb-2">
                                            {{ $errors->first('profile_image') }}
                                        </span>
                                    @endif

                                    <input type="file" name="profile_image" id="profile_image"
                                        class="block w-full text-sm text-gray-700 border border-gray-300 rounded-lg cursor-pointer focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                                </div>
                            </div>
                        </div>
				

				          <div class="mt-8">
				            <button type="submit" class="btn btn-primary py-1 text-lg flex justify-center items-center">
				                <span>Update</span>
				                <span class="ml-2">
				                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
				                      <path fill-rule="evenodd" d="M16.72 7.72a.75.75 0 0 1 1.06 0l3.75 3.75a.75.75 0 0 1 0 1.06l-3.75 3.75a.75.75 0 1 1-1.06-1.06l2.47-2.47H3a.75.75 0 0 1 0-1.5h16.19l-2.47-2.47a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
				                    </svg>
				                </span>
				            </button>
				          </div>
				     				        
				        </form>
				        
				    </div>
		
 
</div>

    
</section>



@include('user.layouts.footer')
