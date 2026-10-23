/**
 * ============================================================================
 * POST AD - Refactored with Database-Driven UI Configuration
 * ============================================================================
 *
 * PRODUCTION-SAFE:
 * - Uses CategoryUIManager with automatic fallback
 * - Backward compatible
 * Version: 2.0
 * Date: 2026-01-31
 */

// ============================================================================
// DROPDOWN MANAGER - Handles all dropdown population
// ============================================================================

class DropdownManager {
    static resetDropdown(selectElement, placeholder = "Select Option") {
        if (selectElement) {
            selectElement.innerHTML = `<option value="">${placeholder}</option>`;
        }
    }

    static populateDropdown(selectElement, data, valueKey, textKey) {
        if (!selectElement || !data) return;

        data.forEach(item => {
            const option = document.createElement('option');
            option.value = item[valueKey];
            option.text = item[textKey];
            selectElement.appendChild(option);
        });
    }

    static async fetchAndPopulate(url, selectElement, valueKey, textKey) {
        try {
            const response = await axios.get(url);
            this.populateDropdown(selectElement, response.data, valueKey, textKey);
            return response.data;
        } catch (error) {
            console.error(`Error fetching data from ${url}:`, error);
            return [];
        }
    }
}

// ============================================================================
// SHIPPING MANAGER - Handles shipping-related logic
// ============================================================================

class ShippingManager {
    constructor() {
        this.shippingDiv = document.getElementById("shipping");
        this.errorMsg = document.getElementById("shipping-error");
        this.shipmentRadios = document.querySelectorAll('input[name="shipment"]');
        this.buyDirectRadios = document.querySelectorAll('input[name="buy_direct"]');
    }

    initialize() {
        this.setupShipmentListeners();
        this.setupBuyDirectListeners();
        this.toggleShippingVisibility();
    }

    reset() {
        // Reset shipment to default (Pickup)
        const pickupRadio = document.querySelector('input[name="shipment"][value="Pickup"]');
        if (pickupRadio) pickupRadio.checked = true;

        // Reset buy direct to default (No)
        const buyNoRadio = document.querySelector('input[name="buy_direct"][value="No"]');
        if (buyNoRadio) buyNoRadio.checked = true;

        // Clear shipping method checkboxes
        const shippingCheckboxes = document.querySelectorAll('input[name="shipping[]"]');
        shippingCheckboxes.forEach(checkbox => checkbox.checked = false);

        // Hide shipping div and error message
        if (this.shippingDiv) {
            this.shippingDiv.classList.add("hidden");
        }
        if (this.errorMsg) {
            this.errorMsg.classList.add("hidden");
        }
    }

    setupShipmentListeners() {
        this.shipmentRadios.forEach(radio => {
            radio.addEventListener("change", () => {
                this.toggleShippingVisibility();

                // Force Buy Direct to "No" when switching to Pickup
                if (radio.value === "Pickup") {
                    const buyNo = document.querySelector('input[name="buy_direct"][value="No"]');
                    if (buyNo) buyNo.checked = true;
                }
            });
        });
    }

    setupBuyDirectListeners() {
        this.buyDirectRadios.forEach(radio => {
            radio.addEventListener("change", () => {
                const shipRadio = document.querySelector(
                    `input[name="shipment"][value="${radio.value === "Yes" ? "Ship" : "Pickup"}"]`
                );
                if (shipRadio) {
                    shipRadio.checked = true;
                    this.toggleShippingVisibility();
                }
            });
        });
    }

    toggleShippingVisibility() {
        const isShipping = this.getShipmentValue() === "Ship";

        if (this.shippingDiv) {
            this.shippingDiv.classList.toggle("hidden", !isShipping);
        }

        // Auto-select first shipping option when shipping is enabled
        if (isShipping) {
            const shippingCheckboxes = document.querySelectorAll('input[name="shipping[]"]');
            if (shippingCheckboxes.length > 0 && !this.hasAnyShippingSelected()) {
                shippingCheckboxes[0].checked = true;
            }
        }

        // Clear error if switching to Pickup
        if (!isShipping && this.errorMsg) {
            this.errorMsg.classList.add("hidden");
        }
    }

    hasAnyShippingSelected() {
        const selectedMethods = document.querySelectorAll('input[name="shipping[]"]:checked');
        return selectedMethods.length > 0;
    }

    getShipmentValue() {
        const checked = document.querySelector('input[name="shipment"]:checked');
        return checked ? checked.value : null;
    }

    validateShipping() {
        const isShipping = this.getShipmentValue() === "Ship";
        const selectedMethods = document.querySelectorAll('input[name="shipping[]"]:checked');

        if (isShipping && selectedMethods.length === 0) {
            if (this.errorMsg) {
                this.errorMsg.classList.remove("hidden");
            }
            if (this.shippingDiv) {
                this.shippingDiv.scrollIntoView({ behavior: 'smooth' });
            }
            return false;
        }

        if (this.errorMsg) {
            this.errorMsg.classList.add("hidden");
        }
        return true;
    }
}

// ============================================================================
// FORM CONTROLLER - Orchestrates all managers (Database-Driven)
// ============================================================================

class FormController {
    constructor() {
        this.uiManager = window.categoryUIManager;
        this.shippingManager = new ShippingManager();
        this.selectedCategoryId = null;

        this.categorySelect = document.getElementById('category');
        this.subcategorySelect = document.getElementById('subcategory');
        this.brandSelect = document.getElementById('brand');
        this.modelSelect = document.getElementById('model');
    }

    async initialize() {
        // Wait for UI manager to load config from database
        await this.uiManager.initialize();

        this.setupCategoryListener();
        this.setupSubcategoryListener();
        this.setupBrandListener();
        this.setupFormValidation();
        this.shippingManager.initialize();
    }

    setupCategoryListener() {
        if (!this.categorySelect) return;

        this.categorySelect.addEventListener('change', async (e) => {
            const categoryId = e.target.value;
            this.selectedCategoryId = categoryId;

            // RESET EVERYTHING - Start fresh
            this.resetForm();

            // Apply database-driven UI rules (no hardcoded IDs!)
            this.uiManager.applyCategoryRules(categoryId);

            // Fetch and populate subcategories
            await DropdownManager.fetchAndPopulate(
                `/fetch-subcat/${categoryId}`,
                this.subcategorySelect,
                'id',
                'sub_category'
            );
        });
    }

    resetForm() {
        // Reset all dropdowns
        DropdownManager.resetDropdown(this.subcategorySelect, "Select Sub Category");
        DropdownManager.resetDropdown(this.brandSelect, "Select Option");
        DropdownManager.resetDropdown(this.modelSelect, "Select Option");

        // Reset shipping section
        this.shippingManager.reset();

        // Clear any form inputs in the managed divs
        this.clearDivInputs(['divCar', 'divPhone', 'divModel']);
    }

    clearDivInputs(divIds) {
        divIds.forEach(divId => {
            const div = document.getElementById(divId);
            if (!div) return;

            // Clear all inputs, textareas, and selects
            const inputs = div.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                if (input.type === 'checkbox' || input.type === 'radio') {
                    input.checked = false;
                } else if (input.tagName === 'SELECT') {
                    input.selectedIndex = 0;
                } else {
                    input.value = '';
                }
            });
        });
    }

    setupSubcategoryListener() {
        if (!this.subcategorySelect) return;

        this.subcategorySelect.addEventListener('change', async (e) => {
            const subcategoryId = e.target.value;

            // Apply database-driven subcategory-specific visibility rules
            this.uiManager.applySubcategoryRules(subcategoryId);

            // Reset brand dropdown
            DropdownManager.resetDropdown(this.brandSelect, "Select Option");

            // Reset model dropdown with appropriate placeholder
            const modelPlaceholder = ['2', '21', '23'].includes(subcategoryId)
                ? "Select Model"
                : "Select Option";
            DropdownManager.resetDropdown(this.modelSelect, modelPlaceholder);

            // Fetch and populate brands
            await DropdownManager.fetchAndPopulate(
                `/fetch-brand/${subcategoryId}`,
                this.brandSelect,
                'id',
                'brand'
            );
        });
    }

    setupBrandListener() {
        if (!this.brandSelect) return;

        this.brandSelect.addEventListener('change', async (e) => {
            const brandId = e.target.value;

            // Get current subcategory to determine correct placeholder
            const currentSubcategory = this.subcategorySelect?.value;
            const placeholder = ['2', '21', '23'].includes(currentSubcategory)
                ? "Select Model"
                : "Select Option";

            // Reset model dropdown
            DropdownManager.resetDropdown(this.modelSelect, placeholder);

            // Fetch and populate models
            await DropdownManager.fetchAndPopulate(
                `/fetch-model/${brandId}`,
                this.modelSelect,
                'id',
                'model'
            );
        });
    }

    setupFormValidation() {
        const form = document.querySelector('form');
        if (!form) return;

        form.addEventListener('submit', (e) => {
            if (!this.shippingManager.validateShipping()) {
                e.preventDefault();
            }
        });
    }
}

// ============================================================================
// INITIALIZATION
// ============================================================================

document.addEventListener('DOMContentLoaded', async () => {
    const formController = new FormController();
    await formController.initialize();
});

// ============================================================================
// LEGACY FUNCTION SUPPORT (for backward compatibility)
// ============================================================================

// Legacy function for backward compatibility - redirects to new implementation
function showHideDiv(categoryId, subcategoryId) {
    console.warn('showHideDiv() is deprecated. The new system handles this automatically.');
}

// Keep this function if it's called elsewhere in your codebase
function toggleShipping() {
    const shippingDiv = document.getElementById("shipping");
    const isShipping = document.querySelector('input[name="shipment"]:checked')?.value === "Ship";

    if (shippingDiv) {
        shippingDiv.classList.toggle("hidden", !isShipping);
    }

    if (!isShipping) {
        const errorMsg = document.getElementById("shipping-error");
        if (errorMsg) {
            errorMsg.classList.add("hidden");
        }
    }

    // Auto-select Buy Direct when Shipping is selected
    if (isShipping) {
        const buyDirectYes = document.querySelector('input[name="buy_direct"][value="Yes"]');
        if (buyDirectYes && !buyDirectYes.checked) {
            buyDirectYes.checked = true;
        }
    }
}

// Auto-select Shipping when Buy Direct is selected
function toggleBuyDirect() {
    const buyDirectYes = document.querySelector('input[name="buy_direct"][value="Yes"]');
    const isBuyDirect = buyDirectYes?.checked;

    if (isBuyDirect) {
        const shipRadio = document.querySelector('input[name="shipment"][value="Ship"]');
        if (shipRadio && !shipRadio.checked) {
            shipRadio.checked = true;
        }
        toggleShipping();
        // Auto-check first carrier if none selected
        const checked = document.querySelectorAll('input[name="shipping[]"]:checked');
        if (checked.length === 0) {
            const first = document.querySelector('input[name="shipping[]"]');
            if (first) first.checked = true;
        }
    }
}
