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

        //console.log(countryId);
        // Fetch states
        axios.get('/fetch-subcat/' + countryId)
            .then(function (response) {
                var stateSelect = document.getElementById('subcategory');
                stateSelect.innerHTML = '<option value="">Select Sub Category</option>'; // Reset state dropdown
                document.getElementById('brand').innerHTML = '<option value="">Select Option</option>'; // Reset city dropdown
                var divCar = document.getElementById("divCar");
                var divPhone = document.getElementById("divPhone");
                var shipment = document.getElementById("shipment");
                var itemCondition = document.getElementById("itemCondition");
                var buyDirect = document.getElementById("buyDirect");
                const inputs = divCar.querySelectorAll('input, textarea, select, checkbox');
                var price = document.getElementById("price");
                var shipping = document.getElementById('shipping');
                var quantity = document.getElementById('quantity');


                // Hide all divs initially
            divCar.classList.add("hidden");
            divPhone.classList.add("hidden");
            divModel.classList.add("hidden");

             if (countryId === "11") {
                services.classList.remove("hidden");
                shipment.classList.add("hidden");
                itemCondition.classList.add("hidden");
                buyDirect.classList.add("hidden");
                if (quantity) {
                    quantity.classList.add("hidden");
                }
             }else{
                services.classList.add("hidden");
                shipment.classList.remove("hidden");
                itemCondition.classList.remove("hidden");
                buyDirect.classList.remove("hidden");
                if (quantity) {
                    quantity.classList.remove("hidden");
                }
             }

            if (countryId === "3") {
                salary.classList.remove("hidden");
                price.classList.add("hidden");
                shipment.classList.add("hidden");
                itemCondition.classList.add("hidden");
                shipping.classList.add("hidden");
                buyDirect.classList.add("hidden");
                expectedSalary.classList.add("hidden");
                if (quantity) {
                    quantity.classList.add("hidden");
                }
            }else if (countryId === "18") {
                expectedSalary.classList.remove("hidden");
                price.classList.add("hidden");
                salary.classList.add("hidden");
                shipment.classList.add("hidden");
                shipping.classList.add("hidden");
                itemCondition.classList.add("hidden");
                buyDirect.classList.add("hidden");
                if (quantity) {
                    quantity.classList.add("hidden");
                }
            }else{
                price.classList.remove("hidden");
                //shipping.classList.remove("hidden");
                if (quantity) {
                    quantity.classList.remove("hidden");
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

