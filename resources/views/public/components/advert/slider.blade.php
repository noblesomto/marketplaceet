@php
    $mediaItems = $ad->getMedia('images');
    $imageUrls = $mediaItems->map(fn($media) => $media->getUrl('optimized'))->toArray();

    // Only genuinely narrow/portrait images (taller than they are wide) get a blurred
    // backdrop. Anything square or wider crops cleanly with a plain cover fit.
    $coverAspectThreshold = 1;
@endphp

<div class="relative w-full group bg-black md:bg-gray-100 md:rounded-2xl overflow-hidden aspect-[4/3] md:aspect-[16/9]">
    <!-- Main Slider -->
    <div id="slider" class="flex h-full transition-transform duration-500 ease-out">
        @foreach($mediaItems as $index => $media)
            @php
                $dims = @getimagesize($media->getPath('large'));
                $ratio = ($dims && $dims[1]) ? $dims[0] / $dims[1] : null;
                $needsBackdrop = $ratio !== null && $ratio < $coverAspectThreshold;
            @endphp
            <div class="flex-none w-full h-full flex items-center justify-center">
                @if($needsBackdrop)
                    <x-blurred-image src="{{ $media->getUrl('large') }}"
                         alt="{{ $ad->ad_title }} - Image {{ $index + 1 }}"
                         onclick="openLightbox({{ $index }})" />
                @else
                    <img src="{{ $media->getUrl('large') }}"
                         alt="{{ $ad->ad_title }} - Image {{ $index + 1 }}"
                         class="w-full h-full object-cover"
                         onclick="openLightbox({{ $index }})">
                @endif
            </div>
        @endforeach
    </div>

    <!-- Controls -->
    @if(count($mediaItems) > 1)
    <button id="prev" class="absolute left-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 p-3 rounded-full shadow-lg backdrop-blur-sm transition opacity-0 group-hover:opacity-100 focus:opacity-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
    </button>
    <button id="next" class="absolute right-4 top-1/2 -translate-y-1/2 bg-white/80 hover:bg-white text-gray-800 p-3 rounded-full shadow-lg backdrop-blur-sm transition opacity-0 group-hover:opacity-100 focus:opacity-100">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
    </button>

    <!-- Indicators (Dots) -->
    <div id="indicators" class="absolute bottom-4 left-0 right-0 flex justify-center space-x-2">
        @foreach ($mediaItems as $index => $media)
            <button data-index="{{ $index }}" class="w-2.5 h-2.5 rounded-full bg-white transition-opacity {{ $index == 0 ? 'opacity-100 scale-110' : 'opacity-50 hover:opacity-100' }} shadow-sm"></button>
        @endforeach
    </div>
    @endif
</div>

<!-- Thumbnail Navigation (Only show if more than 2 images) -->
@if(count($mediaItems) > 2)
<div class="mt-3 md:mt-4">
    <div id="thumbnail-container" class="flex gap-2 md:gap-3 overflow-x-auto pb-2 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100">
        @foreach($mediaItems as $index => $media)
            <button
                data-thumb-index="{{ $index }}"
                class="thumbnail-btn flex-shrink-0 w-16 h-16 md:w-20 md:h-20 rounded-lg overflow-hidden border-2 transition-all duration-200 {{ $index == 0 ? 'border-blue-500 ring-2 ring-blue-200' : 'border-gray-200 hover:border-gray-400' }}"
                onclick="showSlideFromThumbnail({{ $index }})">
                <img src="{{ $media->getUrl('thumbnail') }}"
                     alt="Thumbnail {{ $index + 1 }}"
                     class="w-full h-full object-cover">
            </button>
        @endforeach
    </div>
</div>
@endif

<!-- Lightbox (Keep as is, but ensure buttons exist) -->
<div id="lightbox" class="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50 hidden">
    <div class="relative w-full h-full flex items-center justify-center overflow-hidden">
        <img id="lightbox-image"
             src=""
             alt="Fullscreen Image"
             class="max-h-[90vh] max-w-[90vw] object-contain transition-transform duration-300"
             style="transform: scale(1);">

        <button onclick="closeLightbox()" class="absolute top-4 right-4 text-white text-3xl font-bold hover:text-gray-300 z-10">✕</button>

        @if(count($mediaItems) > 1)
            <button id="lightbox-prev" class="absolute left-4 top-1/2 -translate-y-1/2 text-white bg-black/30 hover:bg-black/50 p-2 rounded-full z-10">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            </button>
            <button id="lightbox-next" class="absolute right-4 top-1/2 -translate-y-1/2 text-white bg-black/30 hover:bg-black/50 p-2 rounded-full z-10">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            </button>
        @endif

        <div class="absolute bottom-6 right-6 flex space-x-2 z-10">
            <button onclick="zoomIn()" class="bg-white text-black px-3 py-1 rounded shadow font-bold">+</button>
            <button onclick="zoomOut()" class="bg-white text-black px-3 py-1 rounded shadow font-bold">−</button>
            <button onclick="resetZoom()" class="bg-white text-black px-3 py-1 rounded shadow">Reset</button>
        </div>

        <div class="absolute bottom-6 left-6 text-white text-sm z-10">
            <span id="image-counter">1 / {{ count($mediaItems) }}</span>
        </div>
    </div>
</div>

<script>
    const imageList = @json($imageUrls);
    const slider = document.getElementById('slider');
    const indicators = document.querySelectorAll('#indicators button');
    const thumbnails = document.querySelectorAll('.thumbnail-btn');
    const slides = imageList.length;

    let index = 0;
    let currentIndex = 0;
    let zoomLevel = 1;

    function showSlide(i) {
        if (!slider) return;
        index = i;
        slider.style.transform = `translateX(-${index * 100}%)`;

        // Update indicators (dots)
        indicators.forEach((btn, idx) => {
            btn.classList.toggle('opacity-100', idx === index);
            btn.classList.toggle('scale-110', idx === index);
            btn.classList.toggle('opacity-50', idx !== index);
        });

        // Update thumbnails
        updateThumbnails();
    }

    function updateThumbnails() {
        thumbnails.forEach((thumb, idx) => {
            if (idx === index) {
                thumb.classList.remove('border-gray-200', 'hover:border-gray-400');
                thumb.classList.add('border-blue-500', 'ring-2', 'ring-blue-200');
            } else {
                thumb.classList.remove('border-blue-500', 'ring-2', 'ring-blue-200');
                thumb.classList.add('border-gray-200', 'hover:border-gray-400');
            }
        });

        // Auto-scroll thumbnail into view
        const activeThumbnail = thumbnails[index];
        if (activeThumbnail) {
            activeThumbnail.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }
    }

    function showSlideFromThumbnail(i) {
        showSlide(i);
    }

    // Safety checks for buttons
    document.getElementById('next')?.addEventListener('click', () => {
        index = (index + 1) % slides;
        showSlide(index);
    });

    document.getElementById('prev')?.addEventListener('click', () => {
        index = (index - 1 + slides) % slides;
        showSlide(index);
    });

    indicators.forEach(btn => {
        btn.addEventListener('click', () => showSlide(parseInt(btn.dataset.index)));
    });

    // Swipe support for slider
    let startX = 0;
    slider?.addEventListener('touchstart', e => startX = e.touches[0].clientX);
    slider?.addEventListener('touchend', e => {
        if (slides <= 1) return;
        const endX = e.changedTouches[0].clientX;
        if (startX - endX > 50) index = (index + 1) % slides;
        else if (endX - startX > 50) index = (index - 1 + slides) % slides;
        showSlide(index);
    });

    // === Lightbox ===
    const lightbox = document.getElementById('lightbox');
    const lightboxImage = document.getElementById('lightbox-image');

    function openLightbox(i) {
        currentIndex = i;
        zoomLevel = 1;
        updateLightbox();
        lightbox.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        lightbox.classList.add('hidden');
        lightboxImage.src = '';
        document.body.style.overflow = '';
    }

    function updateLightbox() {
        if (!lightboxImage) return;
        lightboxImage.src = imageList[currentIndex];
        lightboxImage.style.transform = `scale(${zoomLevel})`;
        document.getElementById('image-counter').textContent = `${currentIndex + 1} / ${imageList.length}`;

        // Only update slider if there are multiple images
        if (slides > 1) showSlide(currentIndex);
    }

    function showNext() {
        if (slides <= 1) return;
        currentIndex = (currentIndex + 1) % imageList.length;
        resetZoom();
        updateLightbox();
    }

    function showPrev() {
        if (slides <= 1) return;
        currentIndex = (currentIndex - 1 + imageList.length) % imageList.length;
        resetZoom();
        updateLightbox();
    }

    function zoomIn() { zoomLevel = Math.min(zoomLevel + 0.5, 4); updateLightbox(); }
    function zoomOut() { zoomLevel = Math.max(1, zoomLevel - 0.5); updateLightbox(); }
    function resetZoom() { zoomLevel = 1; updateLightbox(); }

    document.getElementById('lightbox-next')?.addEventListener('click', showNext);
    document.getElementById('lightbox-prev')?.addEventListener('click', showPrev);

    lightbox.addEventListener('click', e => { if (e.target === lightbox) closeLightbox(); });

    document.addEventListener('keydown', e => {
        if (lightbox.classList.contains('hidden')) return;
        if (e.key === 'Escape') closeLightbox();
        if (e.key === 'ArrowRight') showNext();
        if (e.key === 'ArrowLeft') showPrev();
        if (e.key === '+') zoomIn();
        if (e.key === '-') zoomOut();
    });

    // Lightbox Swipe
    let lightboxStartX = 0;
    lightboxImage?.addEventListener('touchstart', e => lightboxStartX = e.touches[0].clientX);
    lightboxImage?.addEventListener('touchend', e => {
        if (slides <= 1 || zoomLevel > 1) return; // Don't swipe if zoomed in
        const endX = e.changedTouches[0].clientX;
        const diff = lightboxStartX - endX;
        if (Math.abs(diff) > 50) {
            if (diff > 0) showNext();
            else showPrev();
        }
    });
</script>

<style>
#lightbox-image { cursor: grab; }
#lightbox-image:active { cursor: grabbing; }

/* Custom scrollbar for thumbnails */
.scrollbar-thin::-webkit-scrollbar {
    height: 6px;
}
.scrollbar-thin::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}
.scrollbar-thin::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}
.scrollbar-thin::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>
