<div class="relative bg-white">
<!-- Button to open the search -->
    <button aria-label="Search Modal" id="openSearch" class="text-gray-700 rounded flex justify-start items-center">
        <span class="text-gray-400 mr-2">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
          <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
        </span>
        <span class="text-sm">What are you looking for?</span>
    </button>

    <!-- Overlay and Search -->
    <div id="searchOverlay" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden z-40 flex items-center justify-center">
        <div class="bg-white p-6 rounded-lg shadow-lg max-w-2xl w-full z-50 h-screen">
            <button id="closeSearch" class="text-red-500 text-lg font-semibold mb-4 mt-10">Close</button>
            <h2 class="text-xl font-bold mb-4">What are you looking for?</h2>
            <form action="/search" method="GET" class="">
            <div class="flex flex-col">
                
                  <div class="col-span-3 mt-2">
                      <div class="flex flex-wrap  mb-3">
                        <div class="w-full mb-2">
                          <input class="appearance-none block w-full h-12 text-gray-700 border border-gray-200 rounded py-1 px-4 leading-tight focus:outline-none focus:bg-white focus:border-gray-500 rounded-l-lg" id="grid-city" type="text" name="product" placeholder="What are you looking for?" >
                        </div>
                        <div class="w-full ">
                          <div class="relative">
                            <select name="category" class="block appearance-none w-full h-12 bg-gray-200 border border-gray-200 text-gray-700 py-1 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-gray-200 focus:border-gray-500 rounded-r-lg text-sm" id="grid-state">
                                    <option value="">Select Category</option>
                                @foreach(getCategories() as $category)
                                    <option value="{{ $category->id }}">{{ $category->category }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                              <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                            </div>
                          </div>
                        </div>
                    </div>
                  </div>
                  <div class="col-span-1 ">
                      <div class="flex flex-wrap  mb-2">
                      
                        <div class="w-full mb-3">
                          <div class="relative">
                            @php
                                $states = \App\Models\State::orderBy('name')->pluck('name');
                            @endphp

                            <select name="location" class="block appearance-none w-full h-12 bg-gray-200 border border-gray-200 text-gray-700 py-1 px-4 pr-8 rounded leading-tight focus:outline-none focus:bg-gray-200 focus:border-gray-500 text-sm">
                                <option value="" selected="selected">Select Location</option>
                                @foreach ($states as $state)
                                    <option value="{{ $state }}">{{ $state }}</option>
                                @endforeach
                            </select>

                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                              <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z"/></svg>
                            </div>
                          </div>
                        </div>
                    </div>
                  </div>
                  <div class="mt-3">
                      <button aria-label="Search Button" class="bg-dark_green hover:bg-white hover:text-dark_green font-semibold text-white w-full py-4 px-4 rounded-full text-sm">
                          <i class="fa fa-search"></i> <span class="px-1">Search</span>
                        </button>
                  </div>
            </div>
            </form>
        </div>
    </div>
</div>

    
