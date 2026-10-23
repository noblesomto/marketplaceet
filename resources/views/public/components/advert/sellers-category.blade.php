<div class="w-full flex flex-col space-y-2">
    <label class="inline-flex items-center w-full py-2 px-3 rounded hover:bg-gray-50">
      <input type="radio" class="form-radio h-4 w-4 text-blue-600" name="sellers" value="all" checked>
      <span class="ml-2 text-gray-700 flex-grow">Show All</span>
    </label>

    <label class="inline-flex items-center w-full py-2 px-3 rounded hover:bg-gray-50">
      <input type="radio" class="form-radio h-4 w-4 text-blue-600" name="sellers" value="yes">
      <span class="ml-2 text-gray-700 flex-grow">
        Verified Users - {{ advert_count_by_filter('yes', $cat->id) }}
      </span>
    </label>

    <label class="inline-flex items-center w-full py-2 px-3 rounded hover:bg-gray-50">
      <input type="radio" class="form-radio h-4 w-4 text-blue-600" name="sellers" value="no">
      <span class="ml-2 text-gray-700 flex-grow">
        Unverified Users - {{ advert_count_by_filter('no', $cat->id) }}
      </span>
    </label>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
    const sellerRadios = document.querySelectorAll('input[name="sellers"]');

    // 🟢 Fetch Ads Function
    function fetchSellerAds(url = '{{ route("filter.sellers") }}') {
        const selectedSeller = document.querySelector('input[name="sellers"]:checked').value;
        const formData = new FormData();
        formData.append('sellers', selectedSeller);
        formData.append('category', '{{ $cat->id }}'); // Pass category if needed

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            const adsContainer = document.getElementById('ads-container');
            if (adsContainer) {
                adsContainer.innerHTML = data.html;
                attachPaginationEvents(); // re-bind pagination events
            }

            // Close modal if it exists
            const verifiedModal = document.getElementById('verifiedModal');
            if (verifiedModal) {
                verifiedModal.classList.add('hidden');
            }
        });
    }

    // 🟢 Radio Change Event
    sellerRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            fetchSellerAds(); // Fetch with new filter
        });
    });

    // 🟢 Handle Pagination
    function attachPaginationEvents() {
        const adsContainer = document.getElementById('ads-container');
        if (adsContainer) {
            adsContainer.querySelectorAll('.pagination a').forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    fetchSellerAds(this.href); // Load next page with current filter
                });
            });
        }
    }

    attachPaginationEvents(); // Initial binding
});
</script>
