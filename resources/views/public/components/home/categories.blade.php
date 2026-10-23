<section class="p-6 bg-white rounded-xl shadow-sm border border-gray-100 mb-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div class="font-bold text-xl text-gray-800">Popular Categories</div>
        <div>
            <a class="text-dark_green font-semibold text-sm hover:underline flex items-center" href="/all-categories">
                Show All
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 ml-1">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                </svg>
            </a>
        </div>
    </div>

  <div class="max-w-7xl mx-auto px-4">
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-4">
        @foreach ($categories as $category)
        <!-- Category Item -->
        <a href="{{ url('/category/' . $category->category_slug) }}"
           class="group block h-full">

            <div class="h-full flex flex-col items-center justify-center bg-gray-100 rounded-xl p-4 transition-all duration-300 group-hover:-translate-y-1 group-hover:shadow-lg group-hover:bg-white border border-transparent group-hover:border-gray-100">

                <!-- Icon Container (Fixed height for alignment) -->
                <div class="w-16 h-16 mb-3 flex items-center justify-center">
                    <img src="{{ asset('frontend/images/icons/' . $category->icon) }}"
                         alt="{{ $category->category }}"
                         class="w-full h-full object-contain transition-transform duration-300 group-hover:scale-110">
                </div>

                <!-- Text (Centered & clamped to 2 lines) -->
                <h2 class="text-sm font-semibold text-gray-700 text-center leading-tight group-hover:text-green-600 line-clamp-2">
                    {{ $category->category }}
                </h2>
            </div>
        </a>
        @endforeach
    </div>
</div>


    <!-- Grid Container -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-3 pb-10 hidden">
        @foreach ($categories as $category)
            <!-- Individual Category Card -->
            <div class="">
                <!-- Category Title & Icon -->
                <a href="{{ url('/category/' . $category->category_slug) }}" class="flex items-center mb-2 group">
                    <div class="bg-white rounded-lg shadow-sm flex items-center justify-center mr-1  transition-colors duration-200">

                        <img src="{{ asset('frontend/images/icons/' . $category->icon) }}"
                             alt="{{ $category->category }}"
                             class="w-4 h-4 object-contain duration-200">
                    </div>
                    <h2 class="font-bold text-gray-900 text-sm group-hover:text-dark_green transition-colors">{{ $category->category }}</h2>
                </a>

                @php
                    $subCategories = $category->subCategories;
                    $limit = 3;
                @endphp

                <!-- Subcategories List -->
                <ul id="subcat-{{ $category->id }}" class="space-y-2.5">
                    @foreach ($subCategories->take($limit) as $subCategory)
                        <li class="text-sm text-gray-600 hover:text-dark_green transition-colors pl-1">
                            <a href="{{ url('/category/' . $category->category_slug . '/' . $subCategory->sub_cat_slug) }}" class="flex items-center">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-300 mr-2"></span>
                                {{ $subCategory->sub_category }}
                            </a>
                        </li>
                    @endforeach

                    <!-- Hidden Items -->
                    @if ($subCategories->count() > $limit)
                        @foreach ($subCategories->slice($limit) as $subCategory)
                            <li class="text-sm text-gray-600 hover:text-dark_green transition-colors hidden extra-{{ $category->id }} pl-1">
                                <a href="{{ url('/category/' . $category->category_slug . '/' . $subCategory->sub_cat_slug) }}" class="flex items-center">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-300 mr-2"></span>
                                    {{ $subCategory->sub_category }}
                                </a>
                            </li>
                        @endforeach

                        <!-- Toggle Button -->
                        <li class="pt-1 pl-1">
                            <a href="/category/{{ $category->category_slug }}"

                                class="text-xs font-bold text-dark_green hover:text-green-700  tracking-wide focus:outline-none flex items-center"
                            >
                                See all in {{ $category->category }}
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3 h-3 font-semibold">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>

                            </a>
                        </li>
                    @endif
                </ul>
            </div>
        @endforeach
    </div>
</section>



