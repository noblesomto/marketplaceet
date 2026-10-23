 <!-- Modal Backdrop -->
    <div id="modalBackdrop" class="fixed inset-0 bg-black bg-opacity-50 modal-backdrop hidden items-center justify-center z-50">
        <!-- Modal Container -->
        <div id="modalContainer" class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 transform transition-all">
            <!-- Modal Header -->
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Select Brand</h3>
                <button id="closeBrandModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-4">
                @if (!isset($cat->id))
                    @include('public.components.advert.filter-brands-subcat')
                @else
                    @include('public.components.advert.filter-brands')
                @endif


            </div>

            <!-- Modal Footer -->
            <div class="flex justify-end space-x-2 p-4 border-t bg-gray-50">
                <button id="cancelButton" class="px-4 py-2 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </button>

            </div>
        </div>
    </div>

    <script>
        // Get modal elements
        const brandsButton = document.getElementById('brandsButton');
        const modalBackdrop = document.getElementById('modalBackdrop');
        const modalContainer = document.getElementById('modalContainer');
        const closeBrandModal = document.getElementById('closeBrandModal');
        const cancelButton = document.getElementById('cancelButton');
        const confirmButton = document.getElementById('confirmButton');
        const brandOptions = document.querySelectorAll('.brand-option');

        let selectedBrand = null;

        // Show modal function
        function showModal() {
            modalBackdrop.classList.remove('hidden');
            modalBackdrop.classList.add('flex');
            // Add animation
            setTimeout(() => {
                modalContainer.classList.add('scale-100');
            }, 10);
        }

        // Hide modal function
        function hideModal() {
            modalContainer.classList.remove('scale-100');
            setTimeout(() => {
                modalBackdrop.classList.add('hidden');
                modalBackdrop.classList.remove('flex');
            }, 150);
        }

        // Event listeners
        brandsButton.addEventListener('click', showModal);
        closeBrandModal.addEventListener('click', hideModal);
        cancelButton.addEventListener('click', hideModal);

        // Close modal when clicking on backdrop
        modalBackdrop.addEventListener('click', (e) => {
            if (e.target === modalBackdrop) {
                hideModal();
            }
        });

        // Handle brand selection
        brandOptions.forEach(option => {
            option.addEventListener('click', () => {
                // Remove previous selection
                brandOptions.forEach(opt => opt.classList.remove('bg-blue-100', 'border-blue-500'));

                // Add selection styling
                option.classList.add('bg-blue-100', 'border-2', 'border-blue-500');

                // Store selected brand
                selectedBrand = option.querySelector('span').textContent;
            });
        });

        // Handle confirm button
        confirmButton.addEventListener('click', () => {
            if (selectedBrand) {
                brandsButton.textContent = selectedBrand;
                hideModal();

                // Reset selection
                brandOptions.forEach(opt => opt.classList.remove('bg-blue-100', 'border-blue-500', 'border-2'));
                selectedBrand = null;
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modalBackdrop.classList.contains('hidden')) {
                hideModal();
            }
        });
    </script>
