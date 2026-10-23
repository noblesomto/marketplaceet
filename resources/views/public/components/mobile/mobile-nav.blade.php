@php
    $count = getUserNotificationCount();
@endphp

<div id="nav-mobile" class="flex justify-between items-center bg-white h-16 py-2 border-solid border-b-8 border-b-secondary_dark block lg:hidden">
  <div class="flex justify-start items-center">
      <div class="mr-1 -ml-2 md:ml-0 md:mr-4">
        @include('public.components.mobile.mobile-side')
      </div>
      <div class="mx-1 md:mx-4">
        <a href="/"><img class="w-6 md:w-10" src="{{ asset('frontend/images/mobile-logo.png') }}" alt="Go to homepage"></a>
      </div>

      <div class="h-8 border-l-2 border-l-gray-400 pl-3 0 ml-2 pt-2">
          @include('public.components.mobile.mobile-search')
      </div>
  </div>

  <div class="">
        <a href="/user/notifications" aria-label="Notification Button">
            <div class="flex flex-col items-center mx-2 relative">
                <!-- Notification badge - hidden by default if count is 0 -->
                @if($count >= 1)
                    <div class="absolute -top-1 -right-1 bg-dark_green text-white rounded-full w-4 h-4 flex items-center justify-center text-[10px]" >
                    {{ $count > 9 ? '9+' : $count }}
                </div>
                @endif
                <div>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                    </svg>
                </div>
            </div>
        </a>
    </div>

</div>


