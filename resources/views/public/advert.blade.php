@include('public.layouts.header-adverts')
@include('public.layouts.nav')
@include('public.layouts.product-nav')
@include('public.layouts.search')

<section class="bg-gray-50 min-h-screen pb-20 font-sans">

    <!-- Top Banner (Hidden on Mobile) -->
    <div class="container mx-auto max-w-7xl px-4 pt-6 hidden lg:block">
        @include('public.components.advert.banner-advert')
    </div>

    <div class="container mx-auto max-w-7xl px-1 mt-2 pt-1">

        <!-- Breadcrumbs -->
        <nav class="flex text-sm text-gray-500 mb-2 overflow-x-auto whitespace-nowrap no-scrollbar hidden lg:block" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="/" class="inline-flex items-center hover:text-dark_green transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                        Home
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <a href="{{ url('/category/'.$cat->category_slug) }}" class="ml-1 hover:text-dark_green md:ml-2">{{ $cat->category }}</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                        <span class="ml-1 text-gray-700 md:ml-2 font-medium">{{ $sub_cat->sub_category }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

            <!-- LEFT COLUMN: Slider & Ad Details (8 Cols) -->
            <div class="lg:col-span-8 space-y-6">

                <!-- Image Slider Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    @include('public.components.advert.slider')
                </div>

                <!-- Ad Body (Title, Specs, Description) -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100">
                    @include('public.components.advert.ad-body')
                </div>

            </div>

            <!-- RIGHT COLUMN: Sidebar (4 Cols) -->
            <div class="lg:col-span-4">
                <div class="sticky top-24 space-y-6">
                    @include('public.components.advert.sidebar')
                </div>
            </div>

        </div>

        <!-- Similar Ads Section -->
        <div class="mt-16">
            @include('public.components.advert.similar-ad')
        </div>

    </div>
</section>

<!-- Footer -->
@include('public.layouts.footer')

<!-- Modals & Scripts included at the bottom of the structure -->
@include('public.components.advert.scripts')
