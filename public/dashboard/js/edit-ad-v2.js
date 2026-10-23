/**
 * ============================================================================
 * EDIT AD - Refactored with Database-Driven UI Configuration
 * ============================================================================
 *
 * PRODUCTION-SAFE:
 * - Uses CategoryUIManager with automatic fallback
 * - Backward compatible
 *
 * Version: 2.0
 * Date: 2026-01-31
 */

document.addEventListener('DOMContentLoaded', async function() {
    // ==================== Initialize UI Manager ====================
    await window.categoryUIManager.initialize();

    // ==================== Form Selection Logic ====================
    const categorySelect = document.getElementById('category');
    const subcategorySelect = document.getElementById('subcategory');
    const brandSelect = document.getElementById('brand');
    const modelSelect = document.getElementById('model');

    // Store original values from data attributes
    const originalValues = {
        category: categorySelect.dataset.selected,
        subcategory: subcategorySelect.dataset.selected,
        brand: brandSelect.dataset.selected,
        model: modelSelect ? modelSelect.dataset.selected : null
    };

    // Initialize the form
    initializeForm();

    // Event Handlers
    categorySelect.addEventListener('change', handleCategoryChange);
    subcategorySelect.addEventListener('change', handleSubcategoryChange);
    if (brandSelect) brandSelect.addEventListener('change', handleBrandChange);

    // Functions
    function initializeForm() {
        const catId = categorySelect.value;
        const subcatId = subcategorySelect.value;

        // Apply database-driven UI rules
        if (catId) {
            window.categoryUIManager.applyCategoryRules(catId);
        }

        if (subcatId) {
            window.categoryUIManager.applySubcategoryRules(subcatId);
        }

        // If a category was pre-selected, trigger the dependent changes
        if (catId) categorySelect.dispatchEvent(new Event('change'));
        if (subcatId) subcategorySelect.dispatchEvent(new Event('change'));
    }

    function handleCategoryChange() {
        const categoryId = this.value;

        if (!categoryId) {
            subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
            brandSelect.innerHTML = '<option value="">Select Brand</option>';
            if (modelSelect) modelSelect.innerHTML = '<option value="">Select Model</option>';
            return;
        }

        // Apply database-driven UI rules (replaces hardcoded if/else!)
        window.categoryUIManager.applyCategoryRules(categoryId);

        // Fetch subcategories
        fetch(`/fetch-subcat/${categoryId}`)
            .then(response => response.json())
            .then(data => {
                subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
                data.forEach(subcat => {
                    const option = document.createElement('option');
                    option.value = subcat.id;
                    option.text = subcat.sub_category;
                    if (subcat.id == originalValues.subcategory && categoryId == originalValues.category) {
                        option.selected = true;
                    }
                    subcategorySelect.appendChild(option);
                });

                if (subcategorySelect.value) {
                    subcategorySelect.dispatchEvent(new Event('change'));
                }
            })
            .catch(error => console.error('Error fetching subcategories:', error));
    }

    function handleSubcategoryChange() {
        const subcategoryId = this.value;

        if (!subcategoryId) {
            brandSelect.innerHTML = '<option value="">Select Brand</option>';
            if (modelSelect) modelSelect.innerHTML = '<option value="">Select Model</option>';
            return;
        }

        // Apply database-driven UI rules (replaces hardcoded if/else!)
        window.categoryUIManager.applySubcategoryRules(subcategoryId);

        // Fetch brands
        fetch(`/fetch-brand/${subcategoryId}`)
            .then(response => response.json())
            .then(data => {
                brandSelect.innerHTML = '<option value="">Select Brand</option>';
                data.forEach(brand => {
                    const option = document.createElement('option');
                    option.value = brand.id;
                    option.text = brand.brand;
                    if (brand.id == originalValues.brand && subcategoryId == originalValues.subcategory) {
                        option.selected = true;
                    }
                    brandSelect.appendChild(option);
                });

                if (brandSelect.value && [2, 21, 23].includes(parseInt(subcategoryId))) {
                    brandSelect.dispatchEvent(new Event('change'));
                }
            })
            .catch(error => console.error('Error fetching brands:', error));
    }

    function handleBrandChange() {
        const brandId = this.value;
        const subcategoryId = subcategorySelect.value;

        if (!brandId || ![2, 21, 23].includes(parseInt(subcategoryId))) {
            if (modelSelect) modelSelect.innerHTML = '<option value="">Select Model</option>';
            return;
        }

        fetch(`/fetch-model/${brandId}`)
            .then(response => response.json())
            .then(data => {
                if (!modelSelect) return;

                modelSelect.innerHTML = '<option value="">Select Model</option>';
                data.forEach(model => {
                    const option = document.createElement('option');
                    option.value = model.id;
                    option.text = model.model;

                    if (modelSelect.dataset.selected &&
                        brandId == originalValues.brand &&
                        subcategoryId == originalValues.subcategory &&
                        model.id == originalValues.model) {
                        option.selected = true;
                    }

                    modelSelect.appendChild(option);
                });

                modelSelect.dataset.selected = '';
            })
            .catch(error => console.error('Error fetching models:', error));
    }

    // ==================== Shipment Toggle ====================
    function toggleDiv() {
        const selectedOption = document.querySelector('input[name="shipment"]:checked')?.value;
        const shipping = document.getElementById("shipping");

        if (shipping) {
            if (selectedOption === "Ship") {
                shipping.classList.remove("hidden");
            } else {
                shipping.classList.add("hidden");
            }
        }
    }

    // Attach shipment toggle to radio buttons
    const shipmentRadios = document.querySelectorAll('input[name="shipment"]');
    shipmentRadios.forEach(radio => {
        radio.addEventListener('change', toggleDiv);
    });

    // Initial shipment toggle check
    toggleDiv();
});

// ==================== Character Counter for Ad Title ====================
document.getElementById('ad_title').addEventListener('input', function() {
    const charCount = this.value.length;
    const maxLength = 75;
    const charCountElement = document.getElementById('char-count');

    charCountElement.textContent = charCount;

    if (charCount >= maxLength) {
        this.classList.remove('focus:ring-blue-500', 'border-gray-300');
        this.classList.add('border-red-500', 'focus:ring-red-500');
        charCountElement.parentElement.classList.remove('text-gray-500');
        charCountElement.parentElement.classList.add('text-red-500', 'font-semibold');
    } else {
        this.classList.remove('border-red-500', 'focus:ring-red-500');
        this.classList.add('focus:ring-blue-500', 'border-gray-300');
        charCountElement.parentElement.classList.remove('text-red-500', 'font-semibold');
        charCountElement.parentElement.classList.add('text-gray-500');
    }
});

// ==================== Trix Editor Word Counter ====================
document.addEventListener('DOMContentLoaded', function() {
    const trixEditor = document.querySelector('trix-editor');
    const hiddenInput = document.getElementById('content');
    const wordCountElement = document.getElementById('word-count');
    const maxLength = 3500;

    if (!trixEditor) return; // Exit if trix editor not found

    function updateCharacterCount() {
        if (!trixEditor.editor) return;

        const plainTextContent = trixEditor.editor.getDocument().toString();
        const charCount = plainTextContent.length;
        const htmlContent = trixEditor.innerHTML;

        wordCountElement.textContent = charCount;
        hiddenInput.value = htmlContent;

        if (charCount >= maxLength) {
            trixEditor.style.border = '2px solid #ef4444';
            wordCountElement.parentElement.classList.remove('text-gray-500');
            wordCountElement.parentElement.classList.add('text-red-500', 'font-semibold');

            if (charCount > maxLength) {
                console.warn('Content exceeds maximum length');
            }
        } else {
            trixEditor.style.border = '1px solid #d1d5db';
            wordCountElement.parentElement.classList.remove('text-red-500', 'font-semibold');
            wordCountElement.parentElement.classList.add('text-gray-500');
        }
    }

    trixEditor.addEventListener('trix-initialize', updateCharacterCount);
    trixEditor.addEventListener('trix-change', updateCharacterCount);
    trixEditor.addEventListener('trix-attachment-add', updateCharacterCount);
    trixEditor.addEventListener('trix-attachment-remove', updateCharacterCount);
});
