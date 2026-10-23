// ============================================================================
// CONFIGURATION FOR POST AD - All business rules in one place
// ============================================================================

const CONFIG = {
    // Category-based visibility rules
    categoryVisibility: {
        "1": {
            show: ["price"],
            hide: ["services", "shipment", "itemCondition", "buyDirect", "quantity"]
        },
        "7": {
            show: ["price"],
            hide: ["services", "shipment", "itemCondition", "buyDirect", "quantity"]
        },
        "11": {
            show: ["services","price"],
            hide: ["shipment", "itemCondition", "buyDirect", "quantity"]
        },
        "3": {
            show: ["salary"],
            hide: ["price", "shipment", "itemCondition", "shipping", "buyDirect", "expectedSalary", "quantity"]
        },
        "18": {
            show: ["expectedSalary"],
            hide: ["price", "salary", "shipment", "shipping", "itemCondition", "buyDirect", "quantity"]
        },
        default: {
            show: ["price", "quantity", "shipment", "itemCondition", "buyDirect"],
            hide: ["services", "salary", "expectedSalary"]
        }
    },

    // Subcategory-based visibility rules
    subcategoryVisibility: {
        "2": { // Cars
            show: ["divCar", "divModel"],
            hide: ["shipment", "itemCondition", "buyDirect"]
        },
        "21": { // Mini Buses
            show: ["divCar", "divModel"],
            hide: ["shipment", "itemCondition", "buyDirect"]
        },
        "23": { // Trucks
            show: ["divCar", "divModel"],
            hide: ["shipment", "itemCondition", "buyDirect"]
        },
        "22": { // Scooter
            show: ["itemCondition"],
            hide: ["shipment", "buyDirect", "divCar", "divModel"]
        },
        "24": { // Vehicle Parts
            show: ["itemCondition", "shipment", "buyDirect"],
            hide: ["divCar", "divModel"]
        },
        "25": { // Boats
            show: ["itemCondition"],
            hide: ["shipment", "buyDirect", "divCar", "divModel"]
        },
        "6": { // Phones
            show: ["divPhone", "divModel", "shipment"],
            hide: ["itemCondition"]
        },
        "16": { // Birds
            show: ["shipment"],
            hide: ["itemCondition"]
        },
        "17": { // Cats & Kittens
            show: ["shipment"],
            hide: ["itemCondition"]
        },
        "18": { // Dogs
            show: ["shipment"],
            hide: ["itemCondition"]
        },
        "19": { // Fishes
            show: ["shipment"],
            hide: ["itemCondition"]
        }
    },

    // Label text changes based on category
    categoryLabels: {
        "3": "Select Job Type:",
        "10": "Select Type:",
        default: "Select Option:"
    },

    // Label text changes based on Sub category
    subCategoryLabels: {
        "2": "Brand:",
        "21": "Brand:",
        "23": "Brand:",
        default: "Select Option:"
    },


    // Elements that start hidden on page load
    initiallyHidden: ["divCar", "divPhone", "divModel", "salary", "expectedSalary", "services", "buyDirect"],

    // All manageable element IDs
    managedElements: [
        "divCar", "divPhone", "divModel", "services", "shipment",
        "itemCondition", "buyDirect", "price", "shipping",
        "quantity", "salary", "expectedSalary"
    ]
};

// ============================================================================
// ELEMENT VISIBILITY MANAGER
// ============================================================================

class ElementVisibilityManager {
    constructor() {
        this.elements = this.cacheElements();
        this.initializeHiddenElements();
    }

    cacheElements() {
        return CONFIG.managedElements.reduce((acc, id) => {
            const element = document.getElementById(id);
            if (element) {
                acc[id] = element;
            }
            return acc;
        }, {});
    }

    initializeHiddenElements() {
        CONFIG.initiallyHidden.forEach(id => this.hide(id));
    }

    show(elementId) {
        if (this.elements[elementId]) {
            this.elements[elementId].classList.remove("hidden");
        }
    }

    hide(elementId) {
        if (this.elements[elementId]) {
            this.elements[elementId].classList.add("hidden");
        }
    }

    applyRules(configKey, id) {
        const config = configKey[id] || configKey.default;
        if (!config) return;

        config.hide?.forEach(elementId => this.hide(elementId));
        config.show?.forEach(elementId => this.show(elementId));
    }

    resetAllElements() {
        // Hide all managed elements to start fresh
        CONFIG.managedElements.forEach(elementId => this.hide(elementId));
    }

    applyCategoryRules(categoryId) {
        // Reset everything first
        this.resetAllElements();
        // Then apply the specific rules for this category
        this.applyRules(CONFIG.categoryVisibility, categoryId);
    }

    applySubcategoryRules(subcategoryId) {
        this.applyRules(CONFIG.subcategoryVisibility, subcategoryId);
    }
}

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
// LABEL MANAGER - Handles dynamic label text changes
// ============================================================================

class LabelManager {
    static updateLabel(forAttribute, newText) {
        const label = document.querySelector(`label[for="${forAttribute}"]`);
        if (label) {
            label.textContent = newText;
        }
    }

    static updateBrandLabel(categoryId) {
        const labelText =
            CONFIG.categoryLabels[categoryId] ||
            CONFIG.categoryLabels.default;

        this.updateLabel("brand", labelText);
    }

    static updateSubCategoryLabel(subCategoryId) {
        const labelText =
            CONFIG.subCategoryLabels[subCategoryId] ||
            CONFIG.subCategoryLabels.default;

        this.updateLabel("brand", labelText);
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

        // Show buyDirect only when "Shipping Possible" is selected and the shipment
        // section is visible (i.e. not hidden by category rules)
        const shipmentSection = document.getElementById("shipment");
        const buyDirectDiv = document.getElementById("buyDirect");
        if (buyDirectDiv && shipmentSection && !shipmentSection.classList.contains("hidden")) {
            if (isShipping) {
                buyDirectDiv.classList.remove("hidden");
            } else {
                buyDirectDiv.classList.add("hidden");
            }
        }

        // Clear error if switching to Pickup
        if (!isShipping && this.errorMsg) {
            this.errorMsg.classList.add("hidden");
        }
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
// FORM CONTROLLER - Orchestrates all managers
// ============================================================================

class FormController {
    constructor() {
        this.visibilityManager = new ElementVisibilityManager();
        this.shippingManager = new ShippingManager();
        this.selectedCategoryId = null;

        this.categorySelect = document.getElementById('category');
        this.subcategorySelect = document.getElementById('subcategory');
        this.brandSelect = document.getElementById('brand');
        this.modelSelect = document.getElementById('model');
    }

    initialize() {
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

            // Update label
            LabelManager.updateBrandLabel(categoryId);

            // Apply visibility rules (this now resets all elements first)
            this.visibilityManager.applyCategoryRules(categoryId);

            // Re-apply shipping state so buyDirect respects the Ship/Pickup selection
            this.shippingManager.toggleShippingVisibility();

            // Hide the minimum-images tip for Jobs/CV categories (IDs 3 & 18) — no images required
            const minImagesTip = document.getElementById("min-images-tip");
            if (minImagesTip) {
                if (categoryId === "3" || categoryId === "18") {
                    minImagesTip.classList.add("hidden");
                } else {
                    minImagesTip.classList.remove("hidden");
                }
            }

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

            // Apply subcategory-specific visibility rules
            this.visibilityManager.applySubcategoryRules(subcategoryId);

            // Re-apply shipping state so buyDirect respects the Ship/Pickup selection
            this.shippingManager.toggleShippingVisibility();

            // Update label based on subcategory
            LabelManager.updateSubCategoryLabel(subcategoryId);

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
            const placeholder = ['2', '6', '21', '23'].includes(currentSubcategory)
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

document.addEventListener('DOMContentLoaded', () => {
    const formController = new FormController();
    formController.initialize();
});

// ============================================================================
// LEGACY FUNCTION SUPPORT (for backward compatibility)
// ============================================================================

// Legacy no-op kept for backward compatibility
function showHideDiv() {}

// Keep this function if it's called elsewhere in your codebase
function toggleShipping() {
    const shippingDiv = document.getElementById("shipping");
    const isShipping = document.querySelector('input[name="shipment"]:checked')?.value === "Ship";

    if (shippingDiv) {
        shippingDiv.classList.toggle("hidden", !isShipping);
    }

    // Show buyDirect only when Shipping Possible is selected
    const shipmentSection = document.getElementById("shipment");
    const buyDirectDiv = document.getElementById("buyDirect");
    if (buyDirectDiv && shipmentSection && !shipmentSection.classList.contains("hidden")) {
        if (isShipping) {
            buyDirectDiv.classList.remove("hidden");
        } else {
            buyDirectDiv.classList.add("hidden");
        }
    }

    if (!isShipping) {
        const errorMsg = document.getElementById("shipping-error");
        if (errorMsg) {
            errorMsg.classList.add("hidden");
        }
    }
}
