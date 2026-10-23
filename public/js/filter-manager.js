/**
 * Advanced Filter Manager
 * Handles filtering for Cars and Phones with multiple filter types
 */

class FilterManager {
    constructor() {
        this.filters = {
            category: null,
            subCategory: null,
            brand: null,
            location: null,
            priceMin: null,
            priceMax: null,
            buyDirect: null,
            verifiedSellers: 'all',
            // Car filters
            carCondition: [],
            carRegistration: [],
            carFuelType: [],
            carTransmission: [],
            // Phone filters
            phoneCondition: [],
            phoneDeviceType: []
        };

        this.isLoading = false;
        this.currentPage = 1;
        this.hasMore = true;
        this.debounceTimer = null;

        this.init();
    }

    init() {
        // Initialize context filters from page data
        this.extractContextFilters();

        // Attach event listeners
        this.attachCarFilterListeners();
        this.attachPhoneFilterListeners();
        this.attachGeneralFilterListeners();

        // Debug
        console.log('Filter Manager Initialized', this.filters);
    }

    /**
     * Extract context filters from the page (category, subcategory, brand, location)
     */
    extractContextFilters() {
        // Try to get from data attributes or global variables
        if (typeof currentCategory !== 'undefined') {
            this.filters.category = currentCategory;
        }
        if (typeof currentSubCategory !== 'undefined') {
            this.filters.subCategory = currentSubCategory;
        }
        if (typeof currentBrand !== 'undefined') {
            this.filters.brand = currentBrand;
        }
        if (typeof currentLocation !== 'undefined') {
            this.filters.location = currentLocation;
        }
    }

    /**
     * Attach event listeners for car filter checkboxes
     */
    attachCarFilterListeners() {
        const carCheckboxes = document.querySelectorAll('.car-filter-checkbox');

        carCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                const value = e.target.value;
                const name = e.target.name;

                if (name === 'car_condition[]') {
                    this.toggleArrayValue(this.filters.carCondition, value, e.target.checked);
                } else if (name === 'car_registration[]') {
                    this.toggleArrayValue(this.filters.carRegistration, value, e.target.checked);
                } else if (name === 'car_fuel_type[]') {
                    this.toggleArrayValue(this.filters.carFuelType, value, e.target.checked);
                } else if (name === 'car_transmission[]') {
                    this.toggleArrayValue(this.filters.carTransmission, value, e.target.checked);
                }

                this.debounceApplyFilters();
            });
        });
    }

    /**
     * Attach event listeners for phone filter checkboxes
     */
    attachPhoneFilterListeners() {
        const phoneCheckboxes = document.querySelectorAll('.phone-filter-checkbox');

        phoneCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                const value = e.target.value;
                const name = e.target.name;

                if (name === 'phone_condition[]') {
                    this.toggleArrayValue(this.filters.phoneCondition, value, e.target.checked);
                } else if (name === 'phone_device_type[]') {
                    this.toggleArrayValue(this.filters.phoneDeviceType, value, e.target.checked);
                }

                this.debounceApplyFilters();
            });
        });
    }

    /**
     * Attach listeners for general filters (price, buy direct, sellers)
     */
    attachGeneralFilterListeners() {
        // This will integrate with existing filter implementations
        // Listen for custom events from price filter, buy direct, etc.

        document.addEventListener('priceFilterChanged', (e) => {
            this.filters.priceMin = e.detail.min;
            this.filters.priceMax = e.detail.max;
            this.debounceApplyFilters();
        });

        document.addEventListener('buyDirectChanged', (e) => {
            this.filters.buyDirect = e.detail.value;
            this.debounceApplyFilters();
        });

        document.addEventListener('sellersFilterChanged', (e) => {
            this.filters.verifiedSellers = e.detail.value;
            this.debounceApplyFilters();
        });
    }

    /**
     * Toggle value in an array (add if checked, remove if unchecked)
     */
    toggleArrayValue(array, value, isChecked) {
        const index = array.indexOf(value);

        if (isChecked && index === -1) {
            array.push(value);
        } else if (!isChecked && index > -1) {
            array.splice(index, 1);
        }
    }

    /**
     * Debounce filter application to avoid too many requests
     */
    debounceApplyFilters() {
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => {
            this.applyFilters();
        }, 500);
    }

    /**
     * Apply all active filters
     */
    applyFilters() {
        if (this.isLoading) return;

        this.isLoading = true;
        this.currentPage = 1;

        // Show loading state
        this.showLoadingState();

        // Determine which filter endpoint to use
        const hasCarFilters = this.filters.carCondition.length > 0 ||
                             this.filters.carRegistration.length > 0 ||
                             this.filters.carFuelType.length > 0 ||
                             this.filters.carTransmission.length > 0;

        const hasPhoneFilters = this.filters.phoneCondition.length > 0 ||
                               this.filters.phoneDeviceType.length > 0;

        let endpoint = '/filter/adverts';
        let filterData = this.buildFilterData();

        if (hasCarFilters) {
            endpoint = '/filter/car-details';
        } else if (hasPhoneFilters) {
            endpoint = '/filter/phone-details';
        }

        // Make AJAX request
        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify(filterData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            this.updateResults(data);
            this.isLoading = false;
            this.hideLoadingState();
        })
        .catch(error => {
            console.error('Filter error:', error);
            this.showError('Failed to load filtered results. Please try again.');
            this.isLoading = false;
            this.hideLoadingState();
        });
    }

    /**
     * Build filter data object for API request
     */
    buildFilterData() {
        const data = {
            page: this.currentPage
        };

        // Context filters
        if (this.filters.category) data.category = this.filters.category;
        if (this.filters.subCategory) data.sub_category = this.filters.subCategory;
        if (this.filters.brand) data.brand = this.filters.brand;
        if (this.filters.location) data.location = this.filters.location;

        // Price filters
        if (this.filters.priceMin) data.min = this.filters.priceMin;
        if (this.filters.priceMax) data.max = this.filters.priceMax;

        // Buy direct filter
        if (this.filters.buyDirect) data.buydirect = this.filters.buyDirect;

        // Verified sellers filter
        if (this.filters.verifiedSellers && this.filters.verifiedSellers !== 'all') {
            data.sellers = this.filters.verifiedSellers;
        }

        // Car filters
        if (this.filters.carCondition.length > 0) {
            data.condition = this.filters.carCondition;
        }
        if (this.filters.carRegistration.length > 0) {
            data.registration = this.filters.carRegistration;
        }
        if (this.filters.carFuelType.length > 0) {
            data.fuel_type = this.filters.carFuelType;
        }
        if (this.filters.carTransmission.length > 0) {
            data.transmission = this.filters.carTransmission;
        }

        // Phone filters
        if (this.filters.phoneCondition.length > 0) {
            data.condition = this.filters.phoneCondition;
        }
        if (this.filters.phoneDeviceType.length > 0) {
            data.device_type = this.filters.phoneDeviceType;
        }

        return data;
    }

    /**
     * Update the results container with new HTML
     */
    updateResults(data) {
        const container = document.getElementById('ads-container');
        if (container) {
            container.innerHTML = data.html;
        }

        this.hasMore = data.hasMore || false;
        this.updateLoadMoreButton();

        // Update active filter count badge
        this.updateActiveFilterCount();
    }

    /**
     * Show loading state
     */
    showLoadingState() {
        const container = document.getElementById('ads-container');
        if (container) {
            container.style.opacity = '0.5';
            container.style.pointerEvents = 'none';
        }

        // Show spinner in load more button if exists
        const loadMoreBtn = document.getElementById('load-more-btn');
        if (loadMoreBtn) {
            loadMoreBtn.disabled = true;
        }
    }

    /**
     * Hide loading state
     */
    hideLoadingState() {
        const container = document.getElementById('ads-container');
        if (container) {
            container.style.opacity = '1';
            container.style.pointerEvents = 'auto';
        }

        const loadMoreBtn = document.getElementById('load-more-btn');
        if (loadMoreBtn) {
            loadMoreBtn.disabled = false;
        }
    }

    /**
     * Show error message
     */
    showError(message) {
        const container = document.getElementById('ads-container');
        if (container) {
            container.innerHTML = `
                <div class="col-span-full flex flex-col items-center justify-center p-10 bg-red-50 rounded-lg">
                    <svg class="w-12 h-12 text-red-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-red-700 font-medium">${message}</p>
                </div>
            `;
        }
    }

    /**
     * Update load more button visibility
     */
    updateLoadMoreButton() {
        const loadMoreContainer = document.querySelector('.load-more-container');
        if (loadMoreContainer) {
            loadMoreContainer.style.display = this.hasMore ? 'block' : 'none';
        }
    }

    /**
     * Update active filter count badge
     */
    updateActiveFilterCount() {
        const count = this.getActiveFiltersCount();
        const badge = document.getElementById('active-filter-count');

        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'inline-block' : 'none';
        }
    }

    /**
     * Get count of active filters
     */
    getActiveFiltersCount() {
        let count = 0;

        count += this.filters.carCondition.length;
        count += this.filters.carRegistration.length;
        count += this.filters.carFuelType.length;
        count += this.filters.carTransmission.length;
        count += this.filters.phoneCondition.length;
        count += this.filters.phoneDeviceType.length;

        if (this.filters.priceMin || this.filters.priceMax) count++;
        if (this.filters.buyDirect) count++;
        if (this.filters.verifiedSellers !== 'all') count++;

        return count;
    }

    /**
     * Clear all filters
     */
    clearAllFilters() {
        // Reset filter arrays
        this.filters.carCondition = [];
        this.filters.carRegistration = [];
        this.filters.carFuelType = [];
        this.filters.carTransmission = [];
        this.filters.phoneCondition = [];
        this.filters.phoneDeviceType = [];
        this.filters.priceMin = null;
        this.filters.priceMax = null;
        this.filters.buyDirect = null;
        this.filters.verifiedSellers = 'all';

        // Uncheck all checkboxes
        document.querySelectorAll('.car-filter-checkbox, .phone-filter-checkbox').forEach(checkbox => {
            checkbox.checked = false;
        });

        // Reload without filters
        this.applyFilters();
    }

    /**
     * Clear specific filter type
     */
    clearFilter(filterType) {
        switch(filterType) {
            case 'car-condition':
                this.filters.carCondition = [];
                document.querySelectorAll('input[name="car_condition[]"]').forEach(cb => cb.checked = false);
                break;
            case 'car-registration':
                this.filters.carRegistration = [];
                document.querySelectorAll('input[name="car_registration[]"]').forEach(cb => cb.checked = false);
                break;
            case 'car-fuel':
                this.filters.carFuelType = [];
                document.querySelectorAll('input[name="car_fuel_type[]"]').forEach(cb => cb.checked = false);
                break;
            case 'car-transmission':
                this.filters.carTransmission = [];
                document.querySelectorAll('input[name="car_transmission[]"]').forEach(cb => cb.checked = false);
                break;
            case 'phone-condition':
                this.filters.phoneCondition = [];
                document.querySelectorAll('input[name="phone_condition[]"]').forEach(cb => cb.checked = false);
                break;
            case 'phone-device':
                this.filters.phoneDeviceType = [];
                document.querySelectorAll('input[name="phone_device_type[]"]').forEach(cb => cb.checked = false);
                break;
        }

        this.applyFilters();
    }
}

// Initialize filter manager when DOM is ready
let filterManager;

document.addEventListener('DOMContentLoaded', function() {
    // Only initialize if we're on a category/subcategory/brand page
    const hasFilters = document.querySelector('.car-filter-checkbox, .phone-filter-checkbox');

    if (hasFilters) {
        filterManager = new FilterManager();

        // Make it globally accessible for debugging
        window.filterManager = filterManager;
    }
});

// Export for use in other modules if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FilterManager;
}
