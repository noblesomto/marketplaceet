@include('public.layouts.header-blog')
@include('public.layouts.nav')
@include('public.components.mobile.mobile-nav')
@include('public.layouts.search')

<div class="min-h-screen bg-gray-50 py-4 sm:py-8">
    <div class="max-w-7xl mx-auto px-1 sm:px-1 lg:px-8">
          <!-- Breadcrumb -->
        <section class="bg-white py-4 border-b">
            <div class="container mx-auto px-4">
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2 text-sm">
                        <li><a href="/" class="text-blue-600 hover:text-blue-800">Home</a></li>
                        <li class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                            <a href="/blog" class="text-blue-600 hover:text-blue-800">Blog</a>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                            <span class="text-gray-500">{{ $blog->title }}</span>
                        </li>
                    </ol>
                </nav>
            </div>
        </section>

        <!-- Blog Content -->
        <section class="pb-12 pt-2">
            <div class="container mx-auto">
                <div class="max-w-4xl mx-auto">
                    <!-- Article Header -->
                    <article class="bg-white rounded-xl shadow-md overflow-hidden">
                        <div class="px-4 md:px-6 py-2 md:py-1">
                            <div class="mb-6">
                                <div class="uppercase tracking-wide text-sm text-blue-600 font-semibold">{{ Str::of($blog->category)->replace('-', ' ')->title() }}</div>
                                <h1 class="mt-2 text-3xl md:text-4xl font-bold text-gray-900">{{ $blog->title }}</h1>
                                <div class="flex flex-wrap items-center mt-4 text-gray-500 space-x-4" data-nosnippet>
                                    <div class="flex items-center mr-6 mb-2">
                                        <i class="far fa-calendar mr-2"></i>
                                        <span>{{ $blog->created_at->format('M j, Y') }}</span>
                                    </div>

                                    <div class="flex items-center mb-2 hidden">
                                        <i class="far fa-eye mr-2"></i>
                                        <span>{{ $blog->views }} views</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Featured Image -->
                            <div class="mb-8">
                                <img class="w-full h-64 lg:h-96  object-contain rounded-lg" src="{{ $blog->featured_image_webp }}" alt="{{ $blog->title }}">

                            </div>

                            <!-- Article Content -->
                            <div class="content text-gray-700 prose max-w-none">
                                {!! $blog->content !!}
                            </div>
                        </div>
                    </article>



                    <!-- Share Section -->
                    <div class="bg-white rounded-xl shadow-md overflow-hidden mt-8 p-4">
                        <h3 class="font-bold text-lg mb-2">Share this article</h3>
                        <div class="flex space-x-4">
                            @include('public.components.social-share', [
                                'url' => url()->current(),
                                'title' =>$blog->title,
                            ])
                        </div>
                    </div>

                    <!-- Related Articles -->
                    <div class="mt-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Related Articles</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Related Post-->
                            @forelse($similar as $row)
                            <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-lg transition duration-300">
                                <div class="flex">
                                    <div class="w-2/5">
                                        <a href="/blog/{{ $row->slug }}">
                                            <img class="h-32 w-full object-cover" src="{{ $row->featured_image_thumb }}" alt="{{ $blog->title }}">
                                        </a>

                                    </div>
                                    <div class="p-4 w-3/5">
                                        <div class="uppercase tracking-wide text-xs text-green-600 font-semibold">{{ Str::of($row->category)->replace('-', ' ')->title() }}</div>
                                        <a href="/blog/{{ $row->slug }}"><h3 class="font-bold text-gray-900 mt-1">{{ Str::limit($row->title, 50) }}</h3></a>
                                        <div class="mt-2">
                                            <a href="/blog/{{ $row->slug }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 text-sm font-medium">
                                                Read More
                                                <i class="fas fa-arrow-right ml-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty


                            @endforelse

                        </div>
                    </div>

                </div>
            </div>
        </section>
    </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const previews = document.querySelectorAll('.blog-preview');
    previews.forEach(el => {
      const fullText = el.dataset.fullText;
      const limit = window.innerWidth <= 640 ? 90 : 150; // sm breakpoint
      const truncated = fullText.length > limit ? fullText.substring(0, limit) + '…' : fullText;
      el.textContent = truncated;
    });
  });
</script>
@include('public.layouts.footer')
