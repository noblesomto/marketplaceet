
<div id="nav-mobile" class="flex justify-between items-center bg-white h-16 py-2 pl-0 pr-4 border-solid border-b-8 border-b-secondary_dark block lg:hidden">
    <div class="flex items-center">
        @include('public.components.mobile.mobile-side')
        <div class=" text-xl">
      <a href="{{ session('back_url_for_ad_' . $ad->ad_id, url('/')) }}" class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" class="size-5 stroke-green-800 mr-1">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
            <span class="text-base">Back</span>
        </a>
  </div>
    </div>

  @php
    $user_id = session('user_id');
    $inWishlist = false;

    if ($user_id) {
        $inWishlist = \App\Models\Wishlist::where('advert_id', $ad->id)
                                          ->where('user_id', $user_id)
                                          ->exists();
    }
@endphp
  <div class="flex justify-start items-center space-x-5">
      <button id="shareBtn">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" />
        </svg>
      </button>
      <a href="#"
       class="wishlist-toggle"
       data-ad-id="{{ $ad->id }}"
       data-in-wishlist="{{ $inWishlist ? 'true' : 'false' }}">
        <svg xmlns="http://www.w3.org/2000/svg"
             viewBox="0 0 24 24"
             stroke-width="1.5"
             stroke="currentColor"
             class="size-6 transition-colors duration-200 {{ $inWishlist ? 'fill-dark_green text-dark_green' : 'fill-none text-gray-600 hover:text-dark_green' }}">
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
        </svg>
    </a>

    <button id="openNavModal" class="">
        <i class="bi bi-three-dots-vertical text-xl text-gray-600"></i>
    </button>

  </div>



</div>


<!-- Overlay -->
  <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-40 transition-opacity"></div>

  <!-- Bottom Modal -->
  <div id="modal" class="fixed bottom-0 left-0 right-0 bg-white rounded-t-3xl shadow-lg transform translate-y-full transition-transform duration-300 z-50">
    <div class="p-6 space-y-3">

      <a href="/user/post-ad" class="">
            <div class="flex items-center mx-2 shadow-sm p-4">
                <div class="mr-2">
                    <svg viewBox="0 0 24 24" fill="none" data-title="createAdOutline" stroke="none" role="img" aria-hidden="true" focusable="false" class="shrink-0 fill-current  block align-middle size-5"><path d="M4.65457 10.3114L13.8284 19.4853L19.4853 13.8284L18.7624 13.1056C18.3835 12.7267 18.3931 12.1146 18.7758 11.7395C19.172 11.3513 19.8166 11.3313 20.2087 11.7234L20.8995 12.4142C21.6806 13.1953 21.6806 14.4616 20.8995 15.2427L15.2427 20.8995C14.4616 21.6806 13.1953 21.6806 12.4142 20.8995L3.24035 11.7256C2.78484 11.2701 2.57662 10.6231 2.68099 9.9874L3.55647 4.65491C3.60162 4.37991 3.7319 4.12601 3.92895 3.92895C4.12601 3.7319 4.37991 3.60162 4.65491 3.55647L9.9874 2.68099C10.6231 2.57662 11.2701 2.78484 11.7256 3.24035L12.4934 4.00813C12.8856 4.4003 12.8655 5.04487 12.4773 5.441C12.1023 5.82375 11.4902 5.83334 11.1113 5.45442L10.3114 4.65457L5.45233 5.45233L4.65457 10.3114Z" fill="currentColor"></path><path d="M9.58582 9.58587C10.1716 9.00008 10.1716 8.05033 9.58582 7.46455 9.00003 6.87876 8.05029 6.87876 7.4645 7.46455 6.87871 8.05033 6.87871 9.00008 7.4645 9.58587 8.05029 10.1717 9.00003 10.1717 9.58582 9.58587ZM15.0001 4.99994C15.0001 4.44765 15.4478 3.99994 16.0001 3.99994 16.5523 3.99994 17.0001 4.44765 17.0001 4.99994V6.99994H19.0001C19.5523 6.99994 20.0001 7.44765 20.0001 7.99994 20.0001 8.55222 19.5523 8.99994 19.0001 8.99994H17.0001V10.9999C17.0001 11.5522 16.5523 11.9999 16.0001 11.9999 15.4478 11.9999 15.0001 11.5522 15.0001 10.9999V8.99994H13.0001C12.4478 8.99994 12.0001 8.55222 12.0001 7.99994 12.0001 7.44765 12.4478 6.99994 13.0001 6.99994H15.0001V4.99994Z" fill="currentColor"></path></svg>
                </div>
                <div class="text-base">Sell similar item</div>
            </div>
        </a>
        <a href="/report-ad/{{ $ad->id }}" >
            <div class="flex justify-start items-center mx-2 mx-2 shadow-sm p-4">
                <span class="mr-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3"
                        stroke="currentColor" class="size-4">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                    </svg>
                </span>
        <span>
            Report this Ad
        </span>
            </div>
        </a>
      <button id="closeNavModal" class="w-full py-4 px-6 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl font-medium transition-colors mt-2">
        Cancel
      </button>
    </div>
  </div>
