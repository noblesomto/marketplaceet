/**
 * Admin Edit Advert - Uses Database-Driven UI Configuration (CategoryUIManager)
 * Mirrors edit-ad-v2.js pattern used in the user dashboard.
 */

// ==================== Shipment Toggle (global — also called via onclick) ====================
function toggleDiv() {
    const selectedOption = document.querySelector('input[name="shipment"]:checked')?.value;
    const shipping = document.getElementById('shipping');
    if (shipping) {
        if (selectedOption === 'Ship') {
            shipping.classList.remove('hidden');
        } else {
            shipping.classList.add('hidden');
        }
    }
}

document.addEventListener('DOMContentLoaded', async function () {

    // ==================== Initialize UI Manager ====================
    await window.categoryUIManager.initialize();

    // ==================== Form Elements ====================
    const categorySelect    = document.getElementById('category');
    const subcategorySelect = document.getElementById('subcategory');
    const brandSelect       = document.getElementById('brand');
    const modelSelect       = document.getElementById('model');

    // Store original server-rendered values
    const originalValues = {
        category:    categorySelect.dataset.selected,
        subcategory: subcategorySelect.dataset.selected,
        brand:       brandSelect.dataset.selected,
        model:       modelSelect ? modelSelect.dataset.selected : null
    };

    // Initialize on load
    initializeForm();

    // Event listeners
    categorySelect.addEventListener('change', handleCategoryChange);
    subcategorySelect.addEventListener('change', handleSubcategoryChange);
    if (brandSelect) brandSelect.addEventListener('change', handleBrandChange);

    // ==================== Initialization ====================
    function initializeForm() {
        const catId    = categorySelect.value;
        const subcatId = subcategorySelect.value;

        if (catId)    window.categoryUIManager.applyCategoryRules(catId);
        if (subcatId) window.categoryUIManager.applySubcategoryRules(subcatId);

        if (catId)    categorySelect.dispatchEvent(new Event('change'));
        if (subcatId) subcategorySelect.dispatchEvent(new Event('change'));
    }

    // ==================== Category Change ====================
    function handleCategoryChange() {
        const categoryId = this.value;

        if (!categoryId) {
            subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
            brandSelect.innerHTML       = '<option value="">Select Brand</option>';
            if (modelSelect) modelSelect.innerHTML = '<option value="">Select Model</option>';
            return;
        }

        window.categoryUIManager.applyCategoryRules(categoryId);

        fetch(`/fetch-subcat/${categoryId}`)
            .then(r => r.json())
            .then(data => {
                subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
                data.forEach(subcat => {
                    const opt = document.createElement('option');
                    opt.value = subcat.id;
                    opt.text  = subcat.sub_category;
                    if (subcat.id == originalValues.subcategory && categoryId == originalValues.category) {
                        opt.selected = true;
                    }
                    subcategorySelect.appendChild(opt);
                });
                if (subcategorySelect.value) {
                    subcategorySelect.dispatchEvent(new Event('change'));
                }
            })
            .catch(e => console.error('Error fetching subcategories:', e));
    }

    // ==================== Subcategory Change ====================
    function handleSubcategoryChange() {
        const subcategoryId = this.value;

        if (!subcategoryId) {
            brandSelect.innerHTML = '<option value="">Select Brand</option>';
            if (modelSelect) modelSelect.innerHTML = '<option value="">Select Model</option>';
            return;
        }

        window.categoryUIManager.applySubcategoryRules(subcategoryId);

        fetch(`/fetch-brand/${subcategoryId}`)
            .then(r => r.json())
            .then(data => {
                brandSelect.innerHTML = '<option value="">Select Brand</option>';
                data.forEach(brand => {
                    const opt = document.createElement('option');
                    opt.value = brand.id;
                    opt.text  = brand.brand;
                    if (brand.id == originalValues.brand && subcategoryId == originalValues.subcategory) {
                        opt.selected = true;
                    }
                    brandSelect.appendChild(opt);
                });
                if (brandSelect.value && [2, 21, 23].includes(parseInt(subcategoryId))) {
                    brandSelect.dispatchEvent(new Event('change'));
                }
            })
            .catch(e => console.error('Error fetching brands:', e));
    }

    // ==================== Brand Change (Model fetch) ====================
    function handleBrandChange() {
        const brandId       = this.value;
        const subcategoryId = subcategorySelect.value;

        if (!brandId || ![2, 21, 23].includes(parseInt(subcategoryId))) {
            if (modelSelect) modelSelect.innerHTML = '<option value="">Select Model</option>';
            return;
        }

        fetch(`/fetch-model/${brandId}`)
            .then(r => r.json())
            .then(data => {
                if (!modelSelect) return;
                modelSelect.innerHTML = '<option value="">Select Model</option>';
                data.forEach(m => {
                    const opt = document.createElement('option');
                    opt.value = m.id;
                    opt.text  = m.model;
                    if (modelSelect.dataset.selected &&
                        brandId       == originalValues.brand &&
                        subcategoryId == originalValues.subcategory &&
                        m.id          == originalValues.model) {
                        opt.selected = true;
                    }
                    modelSelect.appendChild(opt);
                });
                modelSelect.dataset.selected = '';
            })
            .catch(e => console.error('Error fetching models:', e));
    }

    // ==================== Shipment Radio Listeners ====================
    document.querySelectorAll('input[name="shipment"]').forEach(radio => {
        radio.addEventListener('change', toggleDiv);
    });
    toggleDiv(); // Set initial state

    // ==================== Title Character Counter ====================
    const titleInput = document.getElementById('ad_title');
    if (titleInput) {
        const update = function () {
            const count = this.value.length;
            const el    = document.getElementById('char-count');
            if (el) el.textContent = count;
            if (count >= 75) {
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else {
                this.classList.remove('is-invalid');
                this.classList.add('is-valid');
            }
        };
        titleInput.addEventListener('input', update);
        titleInput.dispatchEvent(new Event('input'));
    }

    // ==================== Trix Description Word Counter ====================
    const trixEditor      = document.querySelector('trix-editor');
    const wordCountEl     = document.getElementById('word-count');

    if (trixEditor && wordCountEl) {
        function updateCount() {
            if (!trixEditor.editor) return;
            const count = trixEditor.editor.getDocument().toString().length;
            wordCountEl.textContent = count;
        }
        trixEditor.addEventListener('trix-initialize', updateCount);
        trixEditor.addEventListener('trix-change', updateCount);
    }
});
