@include('public.layouts.header')
@include('public.layouts.nav')
@include('public.components.mobile.mobile-nav')
@include('public.layouts.search')

<div class="min-h-screen bg-gray-50 pt-8 pb-20 sm:py-12">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Header Section -->
    <section class="mb-10 sm:mb-16 text-center">
      <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-4 sm:mb-6">
          Our Blog
        </h1>
        <p class="text-base sm:text-lg text-gray-600 leading-relaxed">
          Explore insights, tips, and updates from Marketplace Ethiopia — your trusted source for e-commerce trends, business growth strategies, and digital innovation in Ethiopia. Stay informed, get inspired, and discover how to make the most of the online marketplace economy.
        </p>
      </div>
    </section>

    <!-- Blog Posts Grid -->
    <div class="grid gap-6 sm:gap-8 sm:grid-cols-2 lg:grid-cols-3">
      @forelse($blogs as $blog)
      <!-- Blog Post Card -->
      <article class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col">

        <!-- Image -->
        <div class="relative h-48 sm:h-56 lg:h-64">
          <a href="/blog/{{ $blog->slug }}">
              <img
                src="{{ $blog->featured_image_thumb }}"
                alt="{{ $blog->title }}"
                class="absolute inset-0 w-full h-full object-cover"
              >
          </a>
        </div>

        <!-- Content -->
        <div class="p-5 sm:p-6 flex flex-col justify-between flex-grow">
          <div>
            <!-- Category -->
            <span class="inline-block px-3 py-1 text-xs font-semibold text-dark_green bg-blue-100 rounded-full mb-2">
              {{ Str::of($blog->category)->replace('-', ' ')->title() }}
            </span>

            <!-- Title -->
            <h2 class="text-lg sm:text-xl font-bold text-dark_green mb-2 hover:text-primary transition-colors duration-200 line-clamp-2">
              <a href="/blog/{{ $blog->slug }}">{{ Str::limit($blog->title, 50) }}</a>
            </h2>

            <!-- Excerpt -->
            <p
              class="text-sm text-gray-600 leading-relaxed line-clamp-3"
              data-full-text="{{ strip_tags($blog->content) }}"
            >
              {!! Str::limit(strip_tags($blog->content), 150) !!}
            </p>
          </div>

          <!-- Meta + Read More -->
          <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-100">
            <span class="text-xs text-gray-500 flex items-center" data-nosnippet>
              <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <span class="hidden sm:inline">{{ $blog->created_at->format('M j, Y') }}</span>
              <span class="sm:hidden">{{ $blog->created_at->format('M j') }}</span>
            </span>

            <a
              href="/blog/{{ $blog->slug }}"
              class="text-xs sm:text-sm font-semibold text-dark_green hover:text-primary transition-colors flex items-center"
            >
              Read More
              <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
              </svg>
            </a>
          </div>
        </div>
      </article>
      @empty
        <p class="text-center text-gray-500 col-span-full">No blog posts available at the moment.</p>
      @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-10">
      {{ $blogs->links('pagination::tailwind') }}
    </div>

  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const previews = document.querySelectorAll('[data-full-text]');
    previews.forEach(el => {
      const fullText = el.dataset.fullText;
      const limit = window.innerWidth <= 640 ? 90 : 150; // sm breakpoint
      const truncated = fullText.length > limit ? fullText.substring(0, limit) + '…' : fullText;
      el.textContent = truncated;
    });
  });
</script>

@include('public.layouts.footer')
