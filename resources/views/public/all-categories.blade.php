@include('public.layouts.header')
@include('public.layouts.nav')
@include('public.components.mobile.mobile-nav')
@include('public.layouts.search')

<!-- Desktop Categories Grid -->
<section class="max-w-[90rem] mx-auto px-6 py-12 hidden lg:block">
    <div class="mb-10 text-center">
        <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight">Browse Categories</h2>
        <p class="text-gray-500 mt-2">Find exactly what you're looking for across our diverse marketplace</p>
    </div>

    <!-- Masonry Layout using columns-3 prevents awkward gaps from uneven heights -->
    <div class="columns-1 md:columns-2 lg:columns-3 gap-8 space-y-8">
        @foreach ($categories as $category)
            <div class="break-inside-avoid bg-white rounded-2xl border border-gray-100 shadow-sm hover:shadow-md transition-shadow duration-300 overflow-hidden">
                <!-- Category Header -->
                <div class="p-5 border-b border-gray-50 bg-gray-50/50 flex items-center gap-3">
                    <div class="w-10 h-10 flex-shrink-0 bg-white rounded-lg shadow-sm flex items-center justify-center">
                         <img class="h-6 w-6 object-contain" src="{{ asset('frontend/images/icons/' . $category->icon) }}" alt="{{ $category->category }}">
                    </div>
                    <a href="{{ url('/category/' . $category->category_slug) }}" class="group">
                        <h3 class="font-bold text-lg text-gray-800 group-hover:text-emerald-600 transition-colors">
                            {{ $category->category }}
                        </h3>
                    </a>
                </div>

                <!-- Subcategories List -->
                <div class="p-5">
                    <ul class="space-y-3">
                        {{-- Limit to 6 subcategories to keep the UI clean --}}
                        @foreach ($category->subCategories->take(6) as $subCategory)
                            <li>
                                <a href="{{ url('/category/' . $category->category_slug . '/' . $subCategory->sub_cat_slug) }}"
                                   class="text-gray-600 hover:text-emerald-600 text-[14px] transition-colors flex items-center group">
                                    <svg class="w-3 h-3 mr-2 text-gray-300 group-hover:text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    {{ $subCategory->sub_category }}
                                </a>
                            </li>
                        @endforeach
                    </ul>

                    @if($category->subCategories->count() > 6)
                        <div class="mt-4 pt-4 border-t border-gray-50">
                            <a href="{{ url('/category/' . $category->category_slug) }}"
                               class="text-xs font-bold text-emerald-600 uppercase tracking-wider hover:text-emerald-700">
                                + View {{ $category->subCategories->count() - 6 }} More Subcategories
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</section>

<!-- Mobile Categories List -->
<div class="bg-gray-50 lg:hidden min-h-screen">
    <div class="max-w-md mx-auto px-4 pt-4 pb-24">
        <h2 class="text-xl font-bold text-gray-800 mb-4 px-1">Categories</h2>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden divide-y divide-gray-100">
            <!-- All Categories Item -->
            <a href="/all-categories" class="block active:bg-gray-50 transition-colors">
                <div class="flex justify-between items-center p-4">
                    <div class="flex items-center">
                        <div class="bg-emerald-100 w-10 h-10 rounded-xl flex items-center justify-center mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </div>
                        <span class="font-bold text-gray-800">All Categories</span>
                    </div>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-300" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </a>

            <!-- Individual Categories -->
            @foreach ($categories as $category)
                <a href="{{ url('/category/' . $category->category_slug) }}" class="block active:bg-gray-50 transition-colors">
                    <div class="flex justify-between items-center p-4">
                        <div class="flex items-center">
                            <div class="bg-gray-50 w-10 h-10 rounded-xl flex items-center justify-center mr-4 border border-gray-100">
                                <img class="h-6 w-6 object-contain" src="{{ asset('frontend/images/icons/' . $category->icon) }}" alt="{{ $category->category }}">
                            </div>
                            <span class="text-gray-700 font-medium">
                                {{ $category->category }}
                            </span>
                        </div>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-300" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</div>

@include('public.layouts.footer')
