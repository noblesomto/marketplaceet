<!-- Laptop & Computer Filters Modal -->
<div id="computerFiltersModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
        </div>
        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all w-full sm:my-8 sm:align-middle sm:max-w-xl sm:w-full">
            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg leading-6 font-bold text-gray-900">Laptop Filters</h3>
                    <button class="close-computer-modal text-gray-400 hover:text-gray-600" aria-label="Close">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <div class="mt-2 space-y-4 max-h-96 overflow-y-auto">
                    @foreach(['processor' => 'Processor', 'ram' => 'RAM', 'storage' => 'Storage'] as $specField => $specLabel)
                    <div class="{{ $loop->last ? '' : 'border-b border-gray-200' }} pb-4">
                        <h4 class="font-bold text-gray-900 text-sm mb-3">{{ $specLabel }}</h4>
                        @include('public.components.filter.computer-spec', ['field' => $specField])
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-gray-50 px-4 py-3 sm:px-6 flex gap-2">
                <button type="button"
                        id="clearComputerFilters"
                        class="flex-1 inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                    Clear All
                </button>
                <button type="button"
                        class="flex-1 inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 close-computer-modal">
                    Apply Filters
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('computerFiltersModal');
    const open = document.getElementById('computerFiltersButton');

    if (open) {
        open.addEventListener('click', () => modal.classList.remove('hidden'));
    }

    document.querySelectorAll('.close-computer-modal').forEach(button => {
        button.addEventListener('click', () => modal.classList.add('hidden'));
    });

    document.getElementById('clearComputerFilters')?.addEventListener('click', () => {
        if (typeof window.filterManager !== 'undefined') {
            window.filterManager.clearFilter('computer');
        } else {
            document.querySelectorAll('.computer-filter-checkbox').forEach(checkbox => { checkbox.checked = false; });
        }
        modal.classList.add('hidden');
    });

    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.classList.add('hidden');
        }
    });
});
</script>
