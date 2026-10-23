
<section class="w-full max-w-7xl mx-auto px-2 h-12 py-2 mt-4  hidden lg:block">
    <div class="text-sm">
        <div class="grid grid-cols-6 gap-1 md:gap-3">
          <div class="col-span-3 md:col-span-4 flex items-center">
              <a href="/" aria-label="Go to homepage"><img src="{{ asset('frontend/images/logo.png') }}" alt="homepage Marketplace Ethiopia" class="h-12"></a>
          </div>
          
          <div class="col-span-3 md:col-span-2 flex items-center justify-end space-x-4">


            @if(session()->get('user_id') =='')
            <a href="/login" class=" font-semibold text-dark_green  py-2 rounded-lg transition-colors duration-200 flex items-center text-base whitespace-nowrap">
              <span class="mx-1">Sign in or Register</span>
            </a>

            <a href="/user/post-ad"
                class="font-semibold px-3  mr-5 space-x-1 btn btn-secondary py-2 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span class="hidden xl:inline">Place Your Ad</span>
            </a>
            @else
              <a href="/user/post-ad"
                class="ffont-semibold px-3  mr-5 space-x-1 btn btn-secondary py-2 flex items-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span class="hidden xl:inline">Place Your Ad</span>
            </a>
            @endif

          </div>
         
        </div>
    </div>
</section>

