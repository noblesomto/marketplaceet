<div class="w-full flex items-center space-y-2">
    @php
        $total = getAdvertCount(['category' => $cat->id, 'buy_direct' => 'Yes'], true);
    @endphp

    <div>
       <button id="buyDirect" class="text-dark_green cursor-pointer">Active: ( {{ $total }} )</button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Get the button elements
    const buyDirectButton = document.getElementById('buyDirect');
    const buyDirectDesktopButton = document.getElementById('buyDirectDesktop'); // Fixed typo

    // Add click event listeners
    if (buyDirectButton) {
        buyDirectButton.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent default button behavior
            fetchSellerAds(); // Don't pass the event object
        });
    }
    if (buyDirectDesktopButton) {
        buyDirectDesktopButton.addEventListener('click', function(e) {
            e.preventDefault(); // Prevent default button behavior
            fetchSellerAds(); // Don't pass the event object
        });
    }


    // Fetch Ads Function
    function fetchSellerAds(url = '{{ route("filter.buydirect") }}') {
        // Show loading state (optional)
        const resultsContainer = document.getElementById('advert-results');
        if (resultsContainer) {
            resultsContainer.innerHTML = '<div>Loading...</div>';
        }

        const formData = new FormData();
        formData.append('buy_direct', 'Yes');
        formData.append('category', '{{ $cat->id }}');

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            const advertResults = document.getElementById('advert-results');
            if (advertResults) {
                advertResults.innerHTML = data.html;
            }
            attachPaginationEvents(); // re-bind pagination events

            // Close modal if it exists
            const verifiedModal = document.getElementById('verifiedModal');
            if (verifiedModal) {
                verifiedModal.classList.add('hidden');
            }
        })
        .catch(error => {
            console.error('Error fetching ads:', error);
            const advertResults = document.getElementById('advert-results');
            if (advertResults) {
                advertResults.innerHTML = '<div class="error">Failed to load ads. Please try again.</div>';
            }
        });
    }

    function attachPaginationEvents() {
        // Add your pagination event binding code here
        const paginationLinks = document.querySelectorAll('.pagination a');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.href;
                fetchSellerAds(url);
            });
        });
    }

    // Initial binding
    attachPaginationEvents();
});
</script>
