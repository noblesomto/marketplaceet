@php
$sections = [
    ['title' => "Discover what's trending", 'link' => '/user/post-ad',                       'linkLabel' => 'Post Ad',  'items' => $gallery, 'aria' => 'Trending'],
    ['title' => 'Vehicles',                  'link' => '/category/vehicles',                  'linkLabel' => 'See all',  'items' => $cars,    'aria' => 'vehicles'],
    ['title' => 'Phones & Tablets',          'link' => '/category/mobile-phones-and-tablets', 'linkLabel' => 'See all',  'items' => $phones,  'aria' => 'Phone and tablets'],
    ['title' => 'Fashion & Beauty',          'link' => '/category/fashion',                   'linkLabel' => 'See all',  'items' => $fashion, 'aria' => 'fashion and beauty'],
];
@endphp

@foreach ($sections as $section)
  @include('public.components.home.gallery-section', $section)
@endforeach

<script>
  document.addEventListener('DOMContentLoaded', function() {
    const galleries = document.querySelectorAll('.gallery-container');

    galleries.forEach(function(gallery) {
      const slider = gallery.querySelector('.cardSlider');
      const prevButton = gallery.querySelector('.prevButton');
      const nextButton = gallery.querySelector('.nextButton');

      let currentIndex = 0;
      let isTransitioning = false;
      const originalCards = Array.from(slider.children);
      const totalCards = originalCards.length;

      // Exit if not enough cards
      if (totalCards <= 1) return;

      // For desktop: 5 cards visible at once
      const VISIBLE_CARDS = 5;
      const CARDS_TO_CLONE = VISIBLE_CARDS;

      // Clone first 5 cards and append to end for seamless loop
      for (let i = 0; i < CARDS_TO_CLONE; i++) {
        const clone = originalCards[i].cloneNode(true);
        clone.classList.add('cloned');
        slider.appendChild(clone);
      }

      // Clone last 5 cards and prepend to beginning for reverse loop
      for (let i = totalCards - 1; i >= totalCards - CARDS_TO_CLONE; i--) {
        const clone = originalCards[i].cloneNode(true);
        clone.classList.add('cloned');
        slider.insertBefore(clone, slider.firstChild);
      }

      // Start at the first real card (after prepended clones)
      currentIndex = CARDS_TO_CLONE;

      function getCardWidth() {
        return slider.querySelector('div').offsetWidth;
      }

      function updateSliderPosition(withTransition = true) {
        slider.style.transition = withTransition ? 'transform 500ms ease-in-out' : 'none';
        const cardWidth = getCardWidth();
        slider.style.transform = `translateX(-${currentIndex * cardWidth}px)`;
      }

      function handleTransitionEnd() {
        // Jump to real card if we're on a clone
        if (currentIndex >= totalCards + CARDS_TO_CLONE) {
          // At end clones, jump to beginning
          currentIndex = CARDS_TO_CLONE;
          updateSliderPosition(false);
        } else if (currentIndex < CARDS_TO_CLONE) {
          // At beginning clones, jump to end
          currentIndex = totalCards + CARDS_TO_CLONE - 1;
          updateSliderPosition(false);
        }
      }

      slider.addEventListener('transitionend', handleTransitionEnd);

      // Set initial position without transition
      updateSliderPosition(false);

      // Previous button - go backwards in the loop
      prevButton.addEventListener('click', function() {
        if (isTransitioning) return;
        isTransitioning = true;
        currentIndex--;
        updateSliderPosition(true);
        setTimeout(() => { isTransitioning = false; }, 500);
      });

      // Next button - go forward in the loop
      nextButton.addEventListener('click', function() {
        if (isTransitioning) return;
        isTransitioning = true;
        currentIndex++;
        updateSliderPosition(true);
        setTimeout(() => { isTransitioning = false; }, 500);
      });

      // Handle window resize
      let resizeTimeout;
      window.addEventListener('resize', function() {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
          updateSliderPosition(false);
        }, 100);
      });

      // Optional: Auto-play carousel (uncomment to enable)
      /*
      let autoPlayInterval = setInterval(() => {
        if (!isTransitioning) {
          currentIndex++;
          updateSliderPosition(true);
        }
      }, 4000); // Auto-advance every 4 seconds

      // Pause on hover
      gallery.addEventListener('mouseenter', () => clearInterval(autoPlayInterval));

      gallery.addEventListener('mouseleave', () => {
        autoPlayInterval = setInterval(() => {
          if (!isTransitioning) {
            currentIndex++;
            updateSliderPosition(true);
          }
        }, 4000);
      });
      */
    });
  });
</script>
