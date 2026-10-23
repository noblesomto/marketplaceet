let selectedCategoryId = null;
var salary = document.getElementById("salary");
var expectedSalary = document.getElementById("expectedSalary");
var services = document.getElementById("services");
salary.classList.add("hidden");
expectedSalary.classList.add("hidden");
services.classList.add("hidden");

document.getElementById('category').addEventListener('change', function () {
        var countryId = this.value;
        selectedCategoryId = this.value;
        //console.log(selectedCategoryId);
            if (countryId === "3") {
                document.querySelector('label[for="brand"]').textContent = "Select Job Type:";

            }else{
                document.querySelector('label[for="brand"]').textContent = "Select Option:";
            }


        console.log(countryId);
        // Fetch states
        axios.get('/fetch-subcat/' + countryId)
            .then(function (response) {
                var stateSelect = document.getElementById('subcategory');
                stateSelect.innerHTML = '<option value="">Select Sub Category</option>'; // Reset state dropdown
                document.getElementById('brand').innerHTML = '<option value="">Select Option</option>'; // Reset city dropdown
                const COUNTRY_VISIBILITY_CONFIG = {
                "7": {
                    show: [],
                    hide: ["services", "shipment", "itemCondition", "buyDirect", "quantity"]
                },
                "11": {
                    show: ["services"],
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
            };

            // Elements that should be hidden initially
            const INITIALLY_HIDDEN = ["divCar", "divPhone", "divModel"];

            class ElementVisibilityManager {
                constructor(countryId) {
                    this.countryId = countryId;
                    this.elements = this.cacheElements();
                }

                // Cache all DOM elements to avoid repeated queries
                cacheElements() {
                    const elementIds = [
                        "divCar", "divPhone", "divModel", "services", "shipment",
                        "itemCondition", "buyDirect", "price", "shipping",
                        "quantity", "salary", "expectedSalary"
                    ];

                    return elementIds.reduce((acc, id) => {
                        const element = document.getElementById(id);
                        if (element) {
                            acc[id] = element;
                        }
                        return acc;
                    }, {});
                }

                // Show an element by removing 'hidden' class
                show(elementId) {
                    if (this.elements[elementId]) {
                        this.elements[elementId].classList.remove("hidden");
                    }
                }

                // Hide an element by adding 'hidden' class
                hide(elementId) {
                    if (this.elements[elementId]) {
                        this.elements[elementId].classList.add("hidden");
                    }
                }

                // Apply visibility rules based on country configuration
                applyVisibilityRules() {
                    // First, hide initially hidden elements
                    INITIALLY_HIDDEN.forEach(id => this.hide(id));

                    // Get the configuration for this country (or default)
                    const config = COUNTRY_VISIBILITY_CONFIG[this.countryId] ||
                                  COUNTRY_VISIBILITY_CONFIG.default;

                    // Hide all elements that should be hidden
                    config.hide.forEach(id => this.hide(id));

                    // Show all elements that should be shown
                    config.show.forEach(id => this.show(id));
                }

                // Optional: Get all inputs from divCar if needed
                getDivCarInputs() {
                    if (this.elements.divCar) {
                        return this.elements.divCar.querySelectorAll('input, textarea, select, checkbox');
                    }
                    return [];
                }
            }


                response.data.forEach(function (subcat) {
                    var option = document.createElement('option');
                    option.value = subcat.id;
                    option.text = subcat.sub_category;
                    stateSelect.appendChild(option);


                });
            })
            .catch(function (error) {
                console.error(error);
            });
    });

    document.getElementById('subcategory').addEventListener('change', function () {
        var stateId = this.value;

        //onsole.log(stateId);
        // Fetch cities
        axios.get('/fetch-brand/' + stateId)
            .then(function (response) {
                var citySelect = document.getElementById('brand');
                citySelect.innerHTML = '<option value="">Select Option</option>'; // Reset city dropdown

                response.data.forEach(function (brand) {
                    var option = document.createElement('option');
                    option.value = brand.id;
                    option.text = brand.brand;
                    citySelect.appendChild(option);

                    // Show the relevant div based on the selection
                    if (stateId === "2") {
                        divCar.classList.remove("hidden");
                        divModel.classList.remove("hidden");
                        shipment.classList.add("hidden");
                        itemCondition.classList.add("hidden");
                        buyDirect.classList.add("hidden");
                    } else if (stateId === "6") {
                        divPhone.classList.remove("hidden");
                        itemCondition.classList.add("hidden");
                         shipment.classList.remove("hidden");
                        //divModel.classList.remove("hidden");
                    }else{
                        //shipment.classList.remove("hidden");
                    }
                });
            })
            .catch(function (error) {
                console.error(error);
            });
    });


    document.getElementById('brand').addEventListener('change', function () {
        var brandId = this.value;

        // Fetch Model
        //console.log(brandId);
        axios.get('/fetch-model/' + brandId)
            .then(function (response) {
                var modelSelect = document.getElementById('model');
                modelSelect.innerHTML = '<option value="">Select Model</option>'; // Reset city dropdown

                response.data.forEach(function (model) {
                    var option = document.createElement('option');
                    option.value = model.id;
                    option.text = model.model;
                    modelSelect.appendChild(option);

                });
            })
            .catch(function (error) {
                console.error(error);
            });
    });


document.addEventListener("DOMContentLoaded", function () {
    const shipmentRadios = document.querySelectorAll('input[name="shipment"]');
    const buyDirectRadios = document.querySelectorAll('input[name="buy_direct"]');
    const shippingDropdown = document.getElementById("shipping");

    // Show/hide dropdown when shipment changes
    shipmentRadios.forEach(radio => {
        radio.addEventListener("change", function () {
            if (this.value === "Ship") {
                shippingDropdown.classList.remove("hidden");
            } else {
                shippingDropdown.classList.add("hidden");

                // 🟢 NEW: When user switches shipment to Pickup, force Buy Direct to "No"
                const buyNo = document.querySelector('input[name="buy_direct"][value="No"]');
                if (buyNo) {
                    buyNo.checked = true;
                }
            }
        });
    });

    // Auto-change shipment when Buy Direct changes
    buyDirectRadios.forEach(radio => {
        radio.addEventListener("change", function () {
            if (this.value === "Yes") {
                // Set shipment to 'Ship'
                document.querySelector('input[name="shipment"][value="Ship"]').checked = true;
                shippingDropdown.classList.remove("hidden");
            } else {
                // Reset to pickup
                document.querySelector('input[name="shipment"][value="Pickup"]').checked = true;
                shippingDropdown.classList.add("hidden");
            }
        });
    });
});



function toggleShipping() {
        const shippingDiv = document.getElementById("shipping");
        const isShipping = document.querySelector('input[name="shipment"]:checked').value === "Ship";

        // Show or hide the shipping section
        shippingDiv.classList.toggle("hidden", !isShipping);

        // Clear error if switching back to Pickup
        if (!isShipping) {
            document.getElementById("shipping-error").classList.add("hidden");
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        toggleShipping(); // On page load

        // Attach onchange manually in case inline one fails
        const shipmentRadios = document.querySelectorAll('input[name="shipment"]');
        shipmentRadios.forEach(radio => {
            radio.addEventListener('change', toggleShipping);
        });

        const form = document.querySelector('form');

        if (form) {
            form.addEventListener('submit', function (e) {
                const isShipping = document.querySelector('input[name="shipment"]:checked').value === "Ship";
                const selectedMethods = document.querySelectorAll('input[name="shipping[]"]:checked');
                const errorMsg = document.getElementById("shipping-error");

                // If shipping selected but no shipping method checked
                if (isShipping && selectedMethods.length === 0) {
                    e.preventDefault(); // Prevent form submission
                    errorMsg.classList.remove("hidden"); // Show error
                    document.getElementById('shipping').scrollIntoView({ behavior: 'smooth' });
                } else {
                    errorMsg.classList.add("hidden"); // Hide error if valid
                }
            });
        }
    });

