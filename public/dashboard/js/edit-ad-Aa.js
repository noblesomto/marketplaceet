
//Edit Ad JS Functions


document.addEventListener('DOMContentLoaded', function() {
    // ==================== Form Selection Logic ====================
    const categorySelect = document.getElementById('category');
    const subcategorySelect = document.getElementById('subcategory');
    const brandSelect = document.getElementById('brand');
    const modelSelect = document.getElementById('model');
    const divCar = document.getElementById('divCar');
    const divPhone = document.getElementById('divPhone');
    const divModel = document.getElementById('divModel');
    const shipmentDiv = document.getElementById('shipment');
    const shipping = document.getElementById('shipping');
    const itemCondition = document.getElementById("itemCondition");
    const buyDirect = document.getElementById("buyDirect");
    var salary = document.getElementById("salary");
    var expectedSalary = document.getElementById("expectedSalary");
    var services = document.getElementById("services");
    var quantity = document.getElementById('quantity');
    salary.classList.add("hidden");
    expectedSalary.classList.add("hidden");
    services.classList.add("hidden");
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

        toggleSections(subcatId, catId);

        // If a category was pre-selected, trigger the dependent changes
        if (catId) categorySelect.dispatchEvent(new Event('change'));
        if (subcatId) subcategorySelect.dispatchEvent(new Event('change'));
        //console.log(subcatId);
    }


    function handleCategoryChange() {
        const categoryId = this.value;
        if (!categoryId) {
            subcategorySelect.innerHTML = '<option value="">Select Subcategory</option>';
            brandSelect.innerHTML = '<option value="">Select Brand</option>';
            if (modelSelect) modelSelect.innerHTML = '<option value="">Select Model</option>';
            toggleSections('');
            return;
        }
        toggleSections(null,categoryId);

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
            .catch(error => console.error('Error:', error));
    }

    function handleSubcategoryChange() {
        const subcategoryId = this.value;
        if (!subcategoryId) {
            brandSelect.innerHTML = '<option value="">Select Brand</option>';
            if (modelSelect) modelSelect.innerHTML = '<option value="">Select Model</option>';
            toggleSections('');
            return;
        }

        toggleSections(subcategoryId,null);

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

                if (brandSelect.value && (subcategoryId == 2 || subcategoryId == 6)) {
                    brandSelect.dispatchEvent(new Event('change'));
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function handleBrandChange() {
        const brandId = this.value;
        const subcategoryId = subcategorySelect.value;

        if (!brandId || !(subcategoryId == 2 || subcategoryId == 6)) {
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
            .catch(error => console.error('Error:', error));
    }

  function toggleSections(subcategoryId, categoryId) {
    // Convert to string for consistent comparison
    subcategoryId = subcategoryId ? String(subcategoryId) : null;
    categoryId = categoryId ? String(categoryId) : null;

    // ==================== CATEGORY-BASED RULES ====================

    // Category Services (11)
    if (categoryId === "11") {
        services.classList.remove("hidden");
        buyDirect.classList.add("hidden");
        shipping.classList.add("hidden");
        shipmentDiv.classList.add("hidden");
        itemCondition.classList.add("hidden");
        if (quantity) quantity.classList.add("hidden");
    } else {
        services.classList.add("hidden");
        buyDirect.classList.remove("hidden");
        shipping.classList.remove("hidden");
        shipmentDiv.classList.remove("hidden");
        itemCondition.classList.remove("hidden");
        if (quantity) quantity.classList.remove("hidden");
    }

    // Category Jobs (3)
    if (categoryId === "3") {
        salary.classList.remove("hidden");
        price.classList.add("hidden");
        shipmentDiv.classList.add("hidden");
        shipping.classList.add("hidden");
        itemCondition.classList.add("hidden");
        buyDirect.classList.add("hidden");
        expectedSalary.classList.add("hidden");
        if (quantity) quantity.classList.add("hidden");
    }
    // Category CV (18)
    else if (categoryId === "18") {
        expectedSalary.classList.remove("hidden");
        price.classList.add("hidden");
        salary.classList.add("hidden");
        shipmentDiv.classList.add("hidden");
        shipping.classList.add("hidden");
        itemCondition.classList.add("hidden");
        buyDirect.classList.add("hidden");
        if (quantity) quantity.classList.add("hidden");
    }
    else {
        price.classList.remove("hidden");
        salary.classList.add("hidden");
        expectedSalary.classList.add("hidden");
        if (quantity) quantity.classList.remove("hidden");
    }

    // ==================== SUBCATEGORY-BASED RULES ====================

    if (!subcategoryId) return;

    // Cars (2), Mini Buses (21), Trucks (23)
    if (subcategoryId === "2" || subcategoryId === "21" || subcategoryId === "23") {
        if (divCar) divCar.classList.remove('hidden');
        if (divModel) divModel.classList.remove('hidden');
        if (divPhone) divPhone.classList.add('hidden');
        if (shipmentDiv) shipmentDiv.classList.add('hidden');
        if (itemCondition) itemCondition.classList.add('hidden');
        if (buyDirect) buyDirect.classList.add('hidden');
        if (modelSelect) modelSelect.setAttribute('required', 'required');
    }
    // Scooter (22)
    else if (subcategoryId === "22") {
        if (itemCondition) itemCondition.classList.remove('hidden');
        if (divCar) divCar.classList.add('hidden');
        if (divModel) divModel.classList.add('hidden');
        if (divPhone) divPhone.classList.add('hidden');
        if (shipmentDiv) shipmentDiv.classList.add('hidden');
        if (buyDirect) buyDirect.classList.add('hidden');
        if (modelSelect) modelSelect.removeAttribute('required');
    }
    // Vehicle Parts (24)
    else if (subcategoryId === "24") {
        if (itemCondition) itemCondition.classList.remove('hidden');
        if (shipmentDiv) shipmentDiv.classList.remove('hidden');
        if (buyDirect) buyDirect.classList.remove('hidden');
        if (divCar) divCar.classList.add('hidden');
        if (divModel) divModel.classList.add('hidden');
        if (divPhone) divPhone.classList.add('hidden');
        if (modelSelect) modelSelect.removeAttribute('required');
    }
    // Boats (25)
    else if (subcategoryId === "25") {
        if (itemCondition) itemCondition.classList.remove('hidden');
        if (divCar) divCar.classList.add('hidden');
        if (divModel) divModel.classList.add('hidden');
        if (divPhone) divPhone.classList.add('hidden');
        if (shipmentDiv) shipmentDiv.classList.add('hidden');
        if (buyDirect) buyDirect.classList.add('hidden');
        if (modelSelect) modelSelect.removeAttribute('required');
    }
    // Phones (6)
    else if (subcategoryId === "6") {
        if (divPhone) divPhone.classList.remove('hidden');
        if (divModel) divModel.classList.remove('hidden');
        if (shipmentDiv) shipmentDiv.classList.remove('hidden');
        if (divCar) divCar.classList.add('hidden');
        if (itemCondition) itemCondition.classList.add('hidden');
        if (modelSelect) modelSelect.setAttribute('required', 'required');
    }
    // Default: hide all special divs
    else {
        if (divCar) divCar.classList.add('hidden');
        if (divPhone) divPhone.classList.add('hidden');
        if (divModel) divModel.classList.add('hidden');
        if (modelSelect) modelSelect.removeAttribute('required');
    }
}


    // ==================== Shipment Toggle ====================
    function toggleDiv() {
        const selectedOption = document.querySelector('input[name="shipment"]:checked').value;
        const shipping = document.getElementById("shipping");

        if (selectedOption === "Ship") {
            shipping.classList.remove("hidden");
        } else {
            shipping.classList.add("hidden");
        }
    }


});



document.getElementById('ad_title').addEventListener('input', function() {
    const charCount = this.value.length;
    const maxLength = 75;
    const charCountElement = document.getElementById('char-count');

    // Update character count
    charCountElement.textContent = charCount;

    // Style the input field
    if (charCount >= maxLength) {
        this.classList.remove('focus:ring-blue-500', 'border-gray-300');
        this.classList.add('border-red-500', 'focus:ring-red-500');

        // Make character counter red
        charCountElement.parentElement.classList.remove('text-gray-500');
        charCountElement.parentElement.classList.add('text-red-500', 'font-semibold');
    } else {
        this.classList.remove('border-red-500', 'focus:ring-red-500');
        this.classList.add('focus:ring-blue-500', 'border-gray-300');

        // Reset character counter to gray
        charCountElement.parentElement.classList.remove('text-red-500', 'font-semibold');
        charCountElement.parentElement.classList.add('text-gray-500');
    }
});


document.addEventListener('DOMContentLoaded', function() {
    const trixEditor = document.querySelector('trix-editor');
    const hiddenInput = document.getElementById('content');
    const wordCountElement = document.getElementById('word-count');
    const maxLength = 3500;

    function updateCharacterCount() {
        // Check if editor is initialized
        if (!trixEditor.editor) {
            return;
        }

        // Get plain text for character counting (without HTML tags)
        const plainTextContent = trixEditor.editor.getDocument().toString();
        const charCount = plainTextContent.length;

        // Get HTML content for saving to database (preserves formatting)
        const htmlContent = trixEditor.innerHTML;

        // Update character count display
        wordCountElement.textContent = charCount;

        // Update hidden input with HTML content (preserves styling)
        hiddenInput.value = htmlContent;

        // Add red styling when at limit
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

    // Wait for Trix editor to initialize before setting up listeners
    trixEditor.addEventListener('trix-initialize', function() {
        // Initial count after initialization
        updateCharacterCount();
    });

    // Listen for text changes in Trix editor
    trixEditor.addEventListener('trix-change', updateCharacterCount);

    // Also listen for other Trix events that might change content
    trixEditor.addEventListener('trix-attachment-add', updateCharacterCount);
    trixEditor.addEventListener('trix-attachment-remove', updateCharacterCount);
});
