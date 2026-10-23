<!-- Modal Structure -->
<div id="locationModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center p-4 z-50">
  <div class="bg-white rounded-lg p-6 max-w-4xl w-full max-h-[80vh] overflow-y-auto">
    <div class="flex justify-between items-center mb-4 sticky top-0 bg-white py-2">
      @php
          $totalCatAds = getAdvertCount(['brand' => $brand->id]);
      @endphp
      <h3 class="text-lg font-semibold"><a href="#" class="hover:underline">All Ethiopia</a> <span class="bg-gray-100 py-1 px-3 rounded">{{ $totalCatAds }} ads</span> </h3>
      <button id="closelocationModal" class="text-gray-500 hover:text-gray-700 text-2xl font-light">
        &times;
      </button>
    </div>

    <div class="space-y-4">
      @php
          $states = \App\Models\State::orderBy('name')->pluck('name');

          $advertsByState = collect(getAdvertsGroupedByState(['brand' => $brand->id]))->keyBy('state');
      @endphp

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3">
        @foreach ($states as $state)
          <div class="bg-white border border-gray-200 rounded-lg hover:shadow-sm transition duration-200">
            <a class="flex items-center justify-between px-4 py-3 w-full" href="/{{ $state }}/{{ $brand->brand_slug }}">
                <span class="font-medium text-gray-800">{{ $state }}</span>
                <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">
                  {{ $advertsByState[$state]->total ?? 0 }} ads
                </span>
            </a>

          </div>
        @endforeach
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Get elements
    const locationButton = document.getElementById('locationButton');
    const filterlocationButton = document.getElementById('filterlocationButton');
    const locationModal = document.getElementById('locationModal');
    const closeModal = document.getElementById('closelocationModal');

    // Function to open modal (to avoid code duplication)
    function openLocationModal(e) {
      e.preventDefault();
      locationModal.classList.remove('hidden');
      document.body.style.overflow = 'hidden'; // Prevent scrolling when modal is open
    }

    // Function to close modal
    function closeLocationModal(e) {
      e.preventDefault();
      locationModal.classList.add('hidden');
      document.body.style.overflow = ''; // Restore scrolling
    }

    // Open modal when buttons are clicked
    if (locationButton) {
      locationButton.addEventListener('click', openLocationModal);
    }

    if (filterlocationButton) {
      filterlocationButton.addEventListener('click', openLocationModal);
    }

    // Close modal when X is clicked
    if (closeModal) {
      closeModal.addEventListener('click', closeLocationModal);
    }

    // Close modal when clicking outside the modal content
    window.addEventListener('click', (event) => {
      if (event.target === locationModal) {
        closeLocationModal(event);
      }
    });
  });
</script>
