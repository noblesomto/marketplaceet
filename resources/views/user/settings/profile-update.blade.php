@include('user.layouts.header')
@include('user.layouts.back-nav')
@include('user.layouts.search')

<section class="w-full md:w-3/6 bg-white mx-auto p-3 text-sm pb-20">
    <div class="border-b-2 border-b-gray-200 pt-4 px-2 font-bold text-dark_green mb-2">
        Profile Section
        @include('public.components.flash-message')
    </div>

    {{-- Context banner shown when redirected by RequireProfileComplete middleware --}}
    @if(session('profile_required'))
    <div class="mx-2 mt-4 flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 p-4">
        <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <div>
            <p class="font-semibold text-amber-800">Action required</p>
            <p class="mt-0.5 text-amber-700">{{ session('profile_required') }}</p>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-10 gap-5">
        <div class="col-span-10 lg:col-span-6">
            <div class="">
                <div class="max-w-2xl mx-auto bg-white p-3 md:p-10 mt-4 md:mt-10 mb-20 rounded-lg">
                    <form method="POST" action="{{ route('user.profile.update') }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mt-1 font-semibold text-xl">Personal Details:</div>

                        <div class="mb-4 mt-4">
                            <label class="text-sm font-semibold">Name *</label>
                            @if ($errors->has('name'))
                                <span class="text-red-700 py-1">{{ $errors->first('name') }}</span>
                            @endif
                            <input type="text" name="name" placeholder="Name" value="{{ old('name', $user->name) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        </div>

                        <div class="mb-4 mt-4">
                            <label class="text-sm font-semibold">Phone *</label>
                            @if ($errors->has('phone'))
                                <span class="text-red-700 py-1">{{ $errors->first('phone') }}</span>
                            @endif
                            <input type="text" name="phone" placeholder="Phone Number" value="{{ old('phone', $user->phone) }}"
                                class="w-full px-3 py-2 border {{ $errors->has('phone') ? 'border-red-400' : 'border-gray-300' }} rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                        </div>

                        <div class="mb-6 mt-4">
                            <label class="text-sm font-semibold">Email *</label>
                            @if ($errors->has('email'))
                                <span class="text-red-900 my-1">{{ $errors->first('email') }}</span>
                            @endif
                            <input type="email" name="email" placeholder="Enter your email" value="{{ $user->email }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gray-50" readonly>
                        </div>

                        <div class="mb-6 mt-4">
                            <label for="profile_image" class="block text-gray-700 text-sm font-bold mb-2">Profile Image</label>
                            @if ($errors->has('profile_image'))
                                <span class="text-red-900 my-1">{{ $errors->first('profile_image') }}</span>
                            @endif
                            <input type="file" name="profile_image" id="profile_image"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>

                        <div class="mt-8">
                            <button type="submit" class="btn btn-primary py-1 text-lg flex justify-center items-center">
                                <span>Save & Continue</span>
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
        </div>
        <div class="col-span-10 lg:col-span-4">
            @include('user.components.user-sidebar')
        </div>
    </div>

</section>

@include('user.layouts.footer')
