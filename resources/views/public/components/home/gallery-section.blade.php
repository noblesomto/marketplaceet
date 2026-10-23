<div class="gallery-container">
  <div class="flex justify-between h-16 px-2 -mb-4">
    <div class="flex items-center font-bold text-xl text-gray-800">
      {{ $title }}
    </div>
    <div class="flex justify-end">
      <div class="text-dark_green text-sm font-semibold mr-3 flex items-center">
        <a href="{{ $link }}">{{ $linkLabel }}</a>
      </div>
      <div>
        <!-- Navigation Buttons -->
        <div class="flex justify-center mt-5 text-sm">
          <button aria-label="Navigate {{ $aria }} ads left" class="prevButton text-dark_green px-3 py-1 rounded-full border-2 border-dark_green mr-3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="5" stroke="currentColor" class="size-3 font-bold">
              <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
            </svg>
          </button>
          <button aria-label="Navigate {{ $aria }} ads right" class="nextButton text-dark_green px-3 py-1 rounded-full border-2 border-dark_green">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="5" stroke="currentColor" class="size-3">
              <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>

  <div class="container mx-auto">
    <!-- Slider Container -->
    <div class="relative overflow-hidden">
      <!-- Cards Wrapper -->
      <div class="cardSlider flex transition-transform duration-500">
        @foreach ($items as $row)
          @include('public.components.home.gallery-card')
        @endforeach
      </div>
    </div>
  </div>
</div>
