<div class="block md:hidden overflow-x-auto mb-2 pb-1">
    <div class="flex justify-between gap-2 p-2 w-max min-w-full">

        <!-- Brands Button/Dropdown -->
        <div class="relative">
            <button
                id="brandDropdownButton"
                class="flex items-center justify-between px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                Brands
                <svg class="w-5 h-5 ml-2 -mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>


        <!-- Region Button/Dropdown -->
        <div class="relative">
            <button
                id="filterlocationButton"
                class="flex items-center justify-between px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                Region
                <svg class="w-5 h-5 ml-2 -mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>

        <!-- Price Button/Dropdown -->
        <div class="relative">
            <button
                id="priceDropdownButton"
                class="flex items-center justify-between px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                Price
                <svg class="w-5 h-5 ml-2 -mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>

        @if(!in_array($subcat->category->id, [1, 3, 11, 18]))
        <!-- Filter BuyDirect -->
        <div class="relative">
            <button
                id="buyDirectDesktop"
                class="flex items-center justify-between px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                Buy Direct
                <svg class="w-5 h-5 ml-2 -mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
        @endif



        <!-- Verified Sellers Button/Dropdown -->
        <div class="relative">
            <button
                id="verifiedDropdownButton"
                class="flex items-center justify-between px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                Verified Sellers
                <svg class="w-5 h-5 ml-2 -mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>

        @if($subcat->cat_id == 1)
        <!-- Car Filters Button -->
        <div class="relative">
            <button
                id="carFiltersButton"
                class="flex items-center justify-between px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                </svg>
                Vehicle Filters
            </button>
        </div>
        @elseif($subcat->cat_id == 4)
        <!-- Phone Filters Button -->
        <div class="relative">
            <button
                id="phoneFiltersButton"
                class="flex items-center justify-between px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path>
                </svg>
                Phone Filters
            </button>
        </div>
        @endif

    </div>
</div>

@if($subcat->cat_id == 1)
    @include('public.components.mobile.modal-car-filters')
@elseif($subcat->cat_id == 4)
    @include('public.components.mobile.modal-phone-filters')
@endif

<!-- Region Modal -->
<div id="regionModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Region Filter</h3>
                <div class="mt-2">
                    <!-- Region filter content goes here -->
                    <p>Region filter was not used again</p>
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm close-modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Price Modal -->
<div id="verifyPriceModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Price Filter</h3>
                <div class="mt-2">
                    <!-- Region filter content goes here -->
                    @include('public.components.advert.price-filter')
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm close-modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Verified Sellers Modal -->
<div id="verifiedModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Sellers Filter</h3>
                <div class="mt-2">
                    <!-- Region filter content goes here -->
                    @include('public.components.advert.sellers-subcategory')
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm close-modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Brands Modal -->
<div id="brandModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center w-full min-h-screen px-2 py-10 sm:px-4 sm:py-20">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all w-full sm:max-w-4xl sm:my-8">
            <div class="bg-white w-full px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Brand Filter</h3>
                <div class="mt-2">
                    @include('public.components.advert.filter-brands-subcat')
                </div>
            </div>
            <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm close-modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>



<script>
    // Get all buttons and modals
    const regionButton = document.getElementById('filterlocationButton');
    const priceButton = document.getElementById('priceDropdownButton');
    const buyDirectButton = document.getElementById('buyDirectDesktop');
    const verifiedButton = document.getElementById('verifiedDropdownButton');
    const brandButton = document.getElementById('brandDropdownButton');

    const regionModal = document.getElementById('regionModal');
    const verifyPriceModal = document.getElementById('verifyPriceModal');
    const verifiedModal = document.getElementById('verifiedModal');
    const brandModal = document.getElementById('brandModal');

    // Get all close buttons
    const closeButtons = document.querySelectorAll('.close-modal');

    // Add click handlers to buttons
    if (regionButton) {
        regionButton.addEventListener('click', () => {
            regionModal.classList.remove('hidden');
        });
    }

    if (priceButton) {
        priceButton.addEventListener('click', () => {
            verifyPriceModal.classList.remove('hidden');
        });
    }

    if (buyDirectButton) {
        buyDirectButton.addEventListener('click', () => {
            // BuyDirect functionality - can be extended as needed
            console.log('Buy Direct filter clicked');
        });
    }

    if (verifiedButton) {
        verifiedButton.addEventListener('click', () => {
            verifiedModal.classList.remove('hidden');
        });
    }

    if (brandButton) {
        brandButton.addEventListener('click', () => {
            brandModal.classList.remove('hidden');
        });
    }

    // Add click handlers to close buttons
    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            if (regionModal) regionModal.classList.add('hidden');
            if (verifyPriceModal) verifyPriceModal.classList.add('hidden');
            if (verifiedModal) verifiedModal.classList.add('hidden');
            if (brandModal) brandModal.classList.add('hidden');
        });
    });

    // Close modal when clicking outside
    window.addEventListener('click', (event) => {
        if (regionModal && event.target === regionModal) {
            regionModal.classList.add('hidden');
        }
        if (verifyPriceModal && event.target === verifyPriceModal) {
            verifyPriceModal.classList.add('hidden');
        }
        if (verifiedModal && event.target === verifiedModal) {
            verifiedModal.classList.add('hidden');
        }
        if (brandModal && event.target === brandModal) {
            brandModal.classList.add('hidden');
        }
    });
</script>
