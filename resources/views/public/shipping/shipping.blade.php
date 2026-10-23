@include('public.layouts.header')
@include('public.layouts.nav')
@include('public.components.mobile.mobile-nav')
@include('public.layouts.search')


<section class="h-screen flex items-center">
    
    <div class="max-w-2xl mx-auto bg-white p-3 md:p-10  pb-20 mb-5 rounded-lg">
        <div class="flex justify-center">
            <h3 class="text-2xl font-bold">Shipping Code</h3>
        </div>
        @include('public.components.flash-message')
        <form method="POST" action="/shipping">
            @csrf


            <div class="mb-4 mt-10">
                @if ($errors->has('subject'))
                    <span class="text-red-700 py-1">{{ $errors->first('subject') }}</span>
                @endif
                <label class="text-sm font-semibold">Enter 10 Digits Code*</label>
                <input type="text" id="name" name="ship_code" placeholder="WREH5768FET" max="10" min="5" class="w-full px-3 py-3 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" >
            </div>


            <div class="mt-8">
                <button type="submit" class="flex justify-center items-center bg-transparent hover:bg-primary text-dark_green font-semibold hover:text-dark_green  py-3 px-6 border-2 border-dark_green hover:border-dark_green rounded-full ">
                    <span>Submit</span>
                    <span class="ml-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                          <path fill-rule="evenodd" d="M16.72 7.72a.75.75 0 0 1 1.06 0l3.75 3.75a.75.75 0 0 1 0 1.06l-3.75 3.75a.75.75 0 1 1-1.06-1.06l2.47-2.47H3a.75.75 0 0 1 0-1.5h16.19l-2.47-2.47a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </button>
              </div>

        </form>
    </div>
</section>

@include('public.layouts.footer')


