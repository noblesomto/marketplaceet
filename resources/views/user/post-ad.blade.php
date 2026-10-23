@include('user.layouts.header')
@include('user.layouts.nav')
@include('user.layouts.back-nav')
@include('user.layouts.search')

<!-- Custom CSS for consistent iOS Dropdowns -->
<style>
    /* Removes the ugly default iOS gradient/gloss and adds a custom arrow */
    .custom-select {
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 1rem center;
        background-repeat: no-repeat;
        background-size: 1.5em 1.5em;
        padding-right: 2.5rem;
    }
    /* Prevents iOS from zooming in on focus */
    input, select, textarea {
        font-size: 16px !important;
    }

    /* Image Quality Validation Styling */
    .image-validation-result {
        padding: 1rem;
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
        background: white;
    }
    .image-validation-result.valid {
        border-left: 4px solid #10b981;
    }
    .image-validation-result.invalid {
        border-left: 4px solid #ef4444;
    }
    .badge-success {
        background-color: #10b981;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .badge-info {
        background-color: #3b82f6;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .badge-warning {
        background-color: #f59e0b;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .badge-danger {
        background-color: #ef4444;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .alert-sm {
        font-size: 0.875rem;
    }
    .btn-close-validation {
        background: none;
        border: none;
        font-size: 1.5rem;
        line-height: 1;
        color: #6b7280;
        cursor: pointer;
        padding: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s;
    }
    .btn-close-validation:hover {
        background-color: #f3f4f6;
        color: #1f2937;
    }
    .gap-2 {
        gap: 0.5rem;
    }
</style>

<section class="max-w-4xl mx-auto my-4 px-1 sm:px-1 pb-20">

    <!-- Page Title -->
    <div class="mb-4">
        <h1 class="text-xl md:text-2xl font-bold text-dark_green">Create a New Ad</h1>
        <p class="text-gray-500 mt-1">Fill in the details below to publish your Ad.</p>
    </div>

    <!-- Error Alert -->
    @if ($errors->any())
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-8 rounded-r-lg shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">There were issues with your submission:</h3>
                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            @php
                                // ✅ HIDE IMAGE ERROR IF TEMP IMAGES EXIST
                                // If user has temp images from previous submission, don't show "images required" error
                                $isImageError = (
                                    str_contains(strtolower($error), 'image') &&
                                    (str_contains(strtolower($error), 'required') ||
                                     str_contains(strtolower($error), 'select at least') ||
                                     str_contains(strtolower($error), 'upload at least') ||
                                     str_contains(strtolower($error), 'minimum'))
                                );
                                $hasTempImages = session('temp_images') && count(session('temp_images')) > 0;
                                $shouldHideError = $isImageError && $hasTempImages;
                            @endphp

                            @if (!$shouldHideError)
                                <li>{{ $error }}</li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="/user/post-ad" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- CARD 1: Basic Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-3 py-4 border-b border-gray-200">
                <h2 class="text-base lg:text-lg font-semibold text-gray-800">Basic Information</h2>
            </div>
            <div class="p-3 md:p-4 space-y-6">

                <!-- Ad Type -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">What do you want to do?</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="relative flex items-center p-4 border rounded-xl cursor-pointer hover:bg-green-50 hover:border-dark_green transition-all has-[:checked]:border-dark_green has-[:checked]:bg-green-50 has-[:checked]:ring-1 has-[:checked]:ring-dark_green">
                            <input type="radio" name="ad_type" value="Private" class="w-5 h-5 text-dark_green border-gray-300 focus:ring-dark_green" {{ old('ad_type', 'Private') == 'Private' ? 'checked' : '' }} required>
                            <span class="ml-3 block text-gray-900 font-medium">I offer (Selling)</span>
                        </label>
                        <label class="relative flex items-center p-4 border rounded-xl cursor-pointer hover:bg-green-50 hover:border-dark_green transition-all has-[:checked]:border-dark_green has-[:checked]:bg-green-50 has-[:checked]:ring-1 has-[:checked]:ring-dark_green">
                            <input type="radio" name="ad_type" value="Commercial" class="w-5 h-5 text-dark_green border-gray-300 focus:ring-dark_green" {{ old('ad_type') == 'Commercial' ? 'checked' : '' }}>
                            <span class="ml-3 block text-gray-900 font-medium">I'm looking for (Request)</span>
                        </label>
                    </div>
                </div>

                <!-- Title -->
                <div>
                    <label for="ad_title" class="block text-sm font-semibold text-gray-700 mb-2">Ad Title <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <input type="text"
                               name="ad_title"
                               id="ad_title"
                               placeholder="e.g. iPhone 14 Pro Max 256GB"
                               class="block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-dark_green focus:border-transparent transition-colors text-base"
                               value="{{ old('ad_title') }}"
                               maxlength="75"
                               required>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-xs text-gray-400 bg-white pl-1"><span id="char-count">0</span>/75</span>
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-gray-500">A precise title helps you sell faster.</p>
                </div>

                <!-- Category Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Category -->
                    <div>
                        <label for="category" class="block text-sm font-semibold text-gray-700 mb-2">Category <span class="text-red-500">*</span></label>
                        <select id="category" name="category" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent bg-white text-base" data-old-value="{{ old('category') }}" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category') == $category->id ? 'selected' : '' }}>{{ $category->category }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Subcategory -->
                    <div>
                        <label for="subcategory" class="block text-sm font-semibold text-gray-700 mb-2">Sub Category <span class="text-red-500">*</span></label>
                        <select id="subcategory" name="subcategory" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent bg-white text-base" data-old-value="{{ old('subcategory') }}" required>
                            <option value="">Select Subcategory</option>
                        </select>
                    </div>

                    <!-- Brand -->
                    <div>
                        <label for="brand" class="block text-sm font-semibold text-gray-700 mb-2">Brand <span class="text-red-500">*</span></label>
                        <select id="brand" name="brand" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent bg-white text-base" data-old-value="{{ old('brand') }}" required>
                            <option value="">Select Options</option>
                        </select>
                    </div>

                    <!-- Model (Hidden by default) -->
                    <div id="divModel" class="hidden">
                        <label for="model" class="block text-sm font-semibold text-gray-700 mb-2">Model</label>
                        <select id="model" name="model" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent bg-white text-base" data-old-value="{{ old('model') }}">
                            <option value="">Select Model</option>
                        </select>
                        @error('model') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Item Condition -->
                    <div id="itemCondition">
                        <label for="pr" class="block text-sm font-semibold text-gray-700 mb-2">Item Condition <span class="text-red-500">*</span></label>
                        <select id="pr" name="item_condition" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent bg-white text-base">
                            <option value="">Please Choose</option>
                            <option value="New" {{ old('item_condition') == 'New' ? 'selected' : '' }}>New</option>
                            <option value="Foreign Used" {{ old('item_condition') == 'Foreign Used' ? 'selected' : '' }}>Foreign Used</option>
                            <option value="Locally Used" {{ old('item_condition') == 'Locally Used' ? 'selected' : '' }}>Locally Used</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 2: Vehicle Details (Hidden) -->
        <!-- Logic Note: ID "divCar" is required for JS to toggle visibility -->
        <div id="divCar" class="hidden bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h2 class="text-base lg:text-lg font-semibold text-gray-800">Vehicle Specifications</h2>
            </div>
            <div class="p-3 md:p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                    <!-- Vehicle Condition -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Vehicle Condition <span class="text-red-500">*</span></label>
                        <select name="condition" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base">
                            <option value="">Please Choose</option>
                            <option value="Local used" {{ old('condition') == 'Local used' ? 'selected' : '' }}>Local used</option>
                            <option value="Foreign used" {{ old('condition') == 'Foreign used' ? 'selected' : '' }}>Foreign used</option>
                            <option value="Brand new" {{ old('condition') == 'Brand new' ? 'selected' : '' }}>Brand new</option>
                        </select>
                        @error('condition') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Mileage -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Mileage (Km) </label>
                        <div class="flex">
                            <input type="text" name="mileage" placeholder="0" value="{{ old('mileage') }}" class="block w-full px-4 py-3 rounded-l-lg border border-gray-300 focus:ring-dark_green focus:border-dark_green text-base">
                            <span class="inline-flex items-center px-3 rounded-r-lg border border-l-0 border border-gray-300 bg-gray-50 text-gray-500 text-sm">Km</span>
                        </div>
                    </div>



                    <!-- Registration -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Registration <span class="text-red-500">*</span></label>
                        <select name="registration" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base">
                            <option value="">--Select Type--</option>
                            <option value="Registered" {{ old('registration') == 'Registered' ? 'selected' : '' }}>Registered</option>
                            <option value="Unregistered" {{ old('registration') == 'Unregistered' ? 'selected' : '' }}>Unregistered</option>
                        </select>
                        @error('registration') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Fuel -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Fuel Type <span class="text-red-500">*</span></label>
                        <select name="fuel" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base">
                            <option value="">Please Choose</option>
                            <option value="Petrol" {{ old('fuel') == 'Petrol' ? 'selected' : '' }}>Petrol</option>
                            <option value="Diesel" {{ old('fuel') == 'Diesel' ? 'selected' : '' }}>Diesel</option>
                            <option value="Natural gas CNG" {{ old('fuel') == 'Natural gas CNG' ? 'selected' : '' }}>Natural gas CNG</option>
                            <option value="LPG" {{ old('fuel') == 'LPG' ? 'selected' : '' }}>LPG</option>
                            <option value="Hybrid" {{ old('fuel') == 'Hybrid' ? 'selected' : '' }}>Hybrid</option>
                            <option value="Electric" {{ old('fuel') == 'Electric' ? 'selected' : '' }}>Electric</option>
                        </select>
                        @error('fuel') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Transmission -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Transmission <span class="text-red-500">*</span></label>
                        <select name="transmission" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base">
                            <option value="">Please Choose</option>
                            <option value="Automatic" {{ old('transmission') == 'Automatic' ? 'selected' : '' }}>Automatic</option>
                            <option value="Manual" {{ old('transmission') == 'Manual' ? 'selected' : '' }}>Manual</option>
                            <option value="CVT" {{ old('transmission') == 'CVT' ? 'selected' : '' }}>CVT</option>
                            <option value="AMT" {{ old('transmission') == 'AMT' ? 'selected' : '' }}>AMT</option>
                            <option value="Other" {{ old('transmission') == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('transmission') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Vehicle Type -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Body Type <span class="text-red-500">*</span></label>
                        <select name="vehicle_type" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base">
                            <option value="">Please Choose</option>
                            <option value="Small Car" {{ old('vehicle_type') == 'Small Car' ? 'selected' : '' }}>Small Car</option>
                            <option value="Station Wagon" {{ old('vehicle_type') == 'Station Wagon' ? 'selected' : '' }}>Station Wagon</option>
                            <option value="Limousine" {{ old('vehicle_type') == 'Limousine' ? 'selected' : '' }}>Limousine</option>
                            <option value="Convertible" {{ old('vehicle_type') == 'Convertible' ? 'selected' : '' }}>Convertible</option>
                            <option value="SUV/Off Road Vehicle" {{ old('vehicle_type') == 'SUV/Off Road Vehicle' ? 'selected' : '' }}>SUV/Off Road Vehicle</option>
                            <option value="Van/Bus" {{ old('vehicle_type') == 'Van/Bus' ? 'selected' : '' }}>Van/Bus</option>
                            <option value="Coupe" {{ old('vehicle_type') == 'Coupe' ? 'selected' : '' }}>Coupe</option>
                            <option value="Truck" {{ old('vehicle_type') == 'Truck' ? 'selected' : '' }}>Truck</option>
                            <option value="Others" {{ old('vehicle_type') == 'Others' ? 'selected' : '' }}>Others</option>
                        </select>
                        @error('vehicle_type') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Color -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Exterior Color <span class="text-red-500">*</span></label>
                        <select id="Carcolor" name="exterior_color" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base">
                            <option value="">Choose Color</option>
                            @php
                                $carColors = ['Black' => 'Black','White' => 'White','Gray' => 'Gray','Silver' => 'Silver','Blue' => 'Blue','Red' => 'Red','Gold' => 'Gold','Green' => 'Green','Beige' => 'Beige','Brown' => 'Brown','Yellow' => 'Yellow','Orange' => 'Orange','Purple' => 'Purple','Maroon' => 'Maroon','Burgundy' => 'Burgundy','Bronze' => 'Bronze','Champagne' => 'Champagne','Pearl White' => 'Pearl White','Gunmetal' => 'Gunmetal Gray','Midnight Blue' => 'Midnight Blue','Navy Blue' => 'Navy Blue','Olive Green' => 'Olive Green','Charcoal' => 'Charcoal','Matte Black' => 'Matte Black','Two Tone' => 'Two-Tone','Gradient' => 'Gradient','Chameleon' => 'Chameleon','Custom Wrap' => 'Custom Wrap','Chrome' => 'Chrome','Camo' => 'Camouflage','Others' => 'Others'];
                            @endphp
                            @foreach($carColors as $value => $label)
                                <option value="{{ $value }}" {{ old('exterior_color') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('exterior_color') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Doors -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Number of Doors </label>
                        <select name="doors" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base">
                            <option value="">Please Choose</option>
                            <option value="1 Door" {{ old('doors') == '1 Door' ? 'selected' : '' }}>1 Door</option>
                            <option value="2 Doors" {{ old('doors') == '2 Doors' ? 'selected' : '' }}>2 Doors</option>
                            <option value="3 Doors" {{ old('doors') == '3 Doors' ? 'selected' : '' }}>3 Doors</option>
                            <option value="4 Doors" {{ old('doors') == '4 Doors' ? 'selected' : '' }}>4 Doors</option>
                        </select>
                    </div>

                    <!-- Interior Material -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Interior Material</label>
                        <select name="material_interior" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base">
                            <option value="">Please Choose</option>
                            <option value="Full Grain Leather" {{ old('material_interior') == 'Full Grain Leather' ? 'selected' : '' }}>Full Grain Leather</option>
                            <option value="Partial Leather" {{ old('material_interior') == 'Partial Leather' ? 'selected' : '' }}>Partial Leather</option>
                            <option value="Material" {{ old('material_interior') == 'Material' ? 'selected' : '' }}>Material</option>
                            <option value="Velour" {{ old('material_interior') == 'Velour' ? 'selected' : '' }}>Velour</option>
                            <option value="Alcantara" {{ old('material_interior') == 'Alcantara' ? 'selected' : '' }}>Alcantara</option>
                        </select>
                    </div>

                </div>

                <!-- Equipment Section -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h3 class="text-md font-bold text-gray-800 mb-4">Features & Equipment</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        <!-- Exterior Eq -->
                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block mb-3">Exterior</span>
                            <div class="space-y-3">
                                @foreach(['Trailer hitch', 'Parking assistance', 'Alloy wheels', 'Xenon/LED headlights'] as $item)
                                <label class="flex items-center group cursor-pointer">
                                    <input type="checkbox" name="exterior_equipment[]" value="{{ $item }}" class="w-5 h-5 text-dark_green rounded border-gray-300 focus:ring-dark_green cursor-pointer" {{ in_array($item, old('exterior_equipment', [])) ? 'checked' : '' }}>
                                    <span class="ml-3 text-sm text-gray-700 group-hover:text-dark_green transition-colors">{{ $item }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Interior Eq -->
                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block mb-3">Interior</span>
                            <div class="space-y-3">
                                @foreach(['Air conditioning', 'Navigation system', 'Radio/tuner', 'Bluetooth', 'Hands-free system', 'Sunroof/panoramic roof', 'Seat heating', 'Cruise control', 'Non-smoking vehicle'] as $item)
                                <label class="flex items-center group cursor-pointer">
                                    <input type="checkbox" name="interior[]" value="{{ $item }}" class="w-5 h-5 text-dark_green rounded border-gray-300 focus:ring-dark_green cursor-pointer" {{ in_array($item, old('interior', [])) ? 'checked' : '' }}>
                                    <span class="ml-3 text-sm text-gray-700 group-hover:text-dark_green transition-colors">{{ $item }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Security -->
                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block mb-3">Security</span>
                            <div class="space-y-3">
                                @foreach(['Anti-lock braking system (ABS)', 'Service history maintained'] as $item)
                                <label class="flex items-center group cursor-pointer">
                                    <input type="checkbox" name="security[]" value="{{ $item }}" class="w-5 h-5 text-dark_green rounded border-gray-300 focus:ring-dark_green cursor-pointer" {{ in_array($item, old('security', [])) ? 'checked' : '' }}>
                                    <span class="ml-3 text-sm text-gray-700 group-hover:text-dark_green transition-colors">{{ $item }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 3: Phone Details (Hidden) -->
        <!-- Logic Note: ID "divPhone" is required for JS -->
        <div id="divPhone" class="hidden bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h2 class="text-base lg:text-lg font-semibold text-gray-800">Device Details</h2>
            </div>
            <div class="p-3 md:p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <!-- Phone Color -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Color <span class="text-red-500">*</span></label>
                        <select name="phone_color" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent bg-white text-base">
                            <option value="">Choose Color</option>
                            @foreach(['Black', 'White', 'Gray', 'Silver', 'Gold', 'Blue', 'Red', 'Green', 'Yellow', 'Orange', 'Purple', 'Pink', 'Rose Gold', 'Bronze', 'Copper', 'Midnight', 'Space Gray', 'Midnight Green', 'Lavender', 'Aqua', 'Teal', 'Turquoise', 'Coral', 'Champagne', 'Graphite', 'Starlight', 'Twilight', 'Gradient', 'Transparent', 'Others'] as $color)
                                <option value="{{ $color }}" {{ old('phone_color') == $color ? 'selected' : '' }}>{{ $color }}</option>
                            @endforeach
                        </select>
                        @if ($errors->has('phone_color')) <p class="text-xs text-red-500 mt-1">{{ $errors->first('phone_color') }}</p> @endif
                    </div>

                    <!-- Device Type -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Device Type <span class="text-red-500">*</span></label>
                        <select name="device" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent bg-white text-base">
                            <option value="">Please Choose</option>
                            <option value="Device" {{ old('device') == 'Device' ? 'selected' : '' }}>Device</option>
                            <option value="Accessories" {{ old('device') == 'Accessories' ? 'selected' : '' }}>Accessories</option>
                            <option value="Device & Accessories" {{ old('device') == 'Device & Accessories' ? 'selected' : '' }}>Device & Accessories</option>
                        </select>
                        @if ($errors->has('device')) <p class="text-xs text-red-500 mt-1">{{ $errors->first('device') }}</p> @endif
                    </div>

                    <!-- Condition -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Condition <span class="text-red-500">*</span></label>
                        <select name="phone_condition" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent bg-white text-base">
                            <option value="">Please Choose</option>
                            <option value="New - Unboxed" {{ old('phone_condition') == 'New - Unboxed' ? 'selected' : '' }}>New (Unboxed)</option>
                            <option value="Foreign Used - No Packaging" {{ old('phone_condition') == 'Foreign Used - No Packaging' ? 'selected' : '' }}>Foreign Used (No Packaging)</option>
                            <option value="Used - Very Good" {{ old('phone_condition') == 'Used - Very Good' ? 'selected' : '' }}>Used - Very Good</option>
                            <option value="Used - Good" {{ old('phone_condition') == 'Used - Good' ? 'selected' : '' }}>Used - Good</option>
                            <option value="Used - In Order" {{ old('phone_condition') == 'Used - In Order' ? 'selected' : '' }}>Used - In Order</option>
                            <option value="Used - Defect" {{ old('phone_condition') == 'Used - Defect' ? 'selected' : '' }}>Used - Defect</option>
                        </select>
                        @if ($errors->has('phone_condition')) <p class="text-xs text-red-500 mt-1">{{ $errors->first('phone_condition') }}</p> @endif
                    </div>

                </div>
            </div>
        </div>

        <!-- CARD 4: Pricing & Salary -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h2 class="text-base lg:text-lg font-semibold text-gray-800">Financials</h2>
            </div>
            <div class="p-3 md:p-4 space-y-6">

                <!-- Price Section (Logic controlled by JS via #price ID) -->
                <div id="price">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Price</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                        <!-- Amount Input -->
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-base">Br </span>
                            </div>
                            <input type="text" name="price_display" id="price_display" class="block w-full pl-8 pr-12 py-3 border border-gray-300 rounded-lg focus:ring-dark_green focus:border-dark_green text-base" placeholder="0.00" value="{{ old('price') ? number_format(old('price'), 0, '.', ',') : '' }}">
                            <input type="hidden" name="price" id="price_hidden" value="{{ old('price') }}">
                        </div>

                        <!-- Price Type -->
                        <div>
                            <select name="price_type" class="custom-select block w-full px-4 py-3 border border-gray-300 bg-white rounded-lg shadow-sm focus:ring-dark_green focus:border-transparent text-base">
                                <option value="Fixed" {{ old('price_type', 'Fixed') == 'Fixed' ? 'selected' : '' }}>Fixed Price</option>
                                <option value="Negotiable" {{ old('price_type') == 'Negotiable' ? 'selected' : '' }}>Negotiable</option>
                                <option value="Give Away" {{ old('price_type') == 'Give Away' ? 'selected' : '' }}>Give Away</option>
                            </select>
                        </div>

                        <!-- Contact for Price -->
                        <div id="services" class="hidden flex items-center h-full pt-1">
                             <input type="hidden" name="contact_price" value="no">
                             <label class="flex items-center cursor-pointer select-none">
                                <input type="checkbox" name="contact_price" value="yes" class="w-5 h-5 text-dark_green rounded border border-gray-300 focus:ring-dark_green" {{ old('contact_price') == 'yes' ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">Contact for Price</span>
                            </label>
                        </div>
                    </div>
                </div>


                <!-- Salary (Hidden by default, shown via JS) -->
                <div id="salary" class="hidden">
                     <label class="block text-sm font-semibold text-gray-700 mb-2">Salary</label>
                     <select name="salary" class="custom-select block w-full md:w-1/2 px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent text-base">
                        <option value="">--Select Salary--</option>
                        @php
                            $salaryRanges = ['Commission', 'Below Br 2,000', 'Br 2,000 - Br 4,000', 'Br 4,000 - Br 6,000', 'Br 6,000 - Br 8,000', 'Br 8,000 - Br 10,000', 'Br 10,000 - Br 12,000', 'Br 12,000 - Br 14,000', 'Br 14,000 - Br 16,000', 'Br 16,000 - Br 18,000', 'Br 18,000 - Br 20,000', 'Br 20,000 - Br 22,000', 'Br 22,000 - Br 25,000', 'Br 25,000 - Br 30,000', 'Br 30,000 - Br 35,000', 'Br 35,000 - Br 40,000', 'Br 40,000 - Br 45,000', 'Br 45,000 - Br 50,000', 'Above Br 50,000'];
                        @endphp
                        @foreach($salaryRanges as $range)
                            <option value="{{ $range }}" {{ old('salary') == $range ? 'selected' : '' }}>{{ $range }}</option>
                        @endforeach
                    </select>
                    {{-- Don't show error if field is hidden --}}
                    @if ($errors->has('salary'))
                        <p class="text-xs text-red-500 mt-1 salary-error">{{ $errors->first('salary') }}</p>
                    @endif
                </div>

                <!-- Expected Salary (Hidden by default, shown via JS) -->
                <div id="expectedSalary" class="hidden">
                     <label class="block text-sm font-semibold text-gray-700 mb-2">Expected Salary</label>
                     <select name="expected_salary" class="custom-select block w-full md:w-1/2 px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent text-base">
                        <option value="">--Select Expected Salary--</option>
                        @php
                            $expectedSalaryRanges = ['Below Br 5,000', 'Br 5,000 - Br 7,500', 'Br 7,500 - Br 10,000', 'Br 10,000 - Br 12,000', 'Br 12,000 - Br 14,000', 'Br 14,000 - Br 16,000', 'Br 16,000 - Br 18,000', 'Br 18,000 - Br 20,000', 'Br 20,000 - Br 22,000', 'Br 22,000 - Br 25,000', 'Br 25,000 - Br 30,000', 'Br 30,000 - Br 35,000', 'Br 35,000 - Br 40,000', 'Br 40,000 - Br 45,000', 'Br 45,000 - Br 50,000', 'Above Br 50,000'];
                        @endphp
                        @foreach($expectedSalaryRanges as $range)
                            <option value="{{ $range }}" {{ old('expected_salary') == $range ? 'selected' : '' }}>{{ $range }}</option>
                        @endforeach
                    </select>
                    {{-- Don't show error if field is hidden --}}
                    @if ($errors->has('expected_salary'))
                        <p class="text-xs text-red-500 mt-1 expected-salary-error">{{ $errors->first('expected_salary') }}</p>
                    @endif
                </div>

                <!-- Quantity (Restored) -->
                @if($user->acc_type=="Commercial")
                <div id="quantity">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Item Quantity</label>
                    <input type="number" name="quantity" placeholder="Item Quantity" class="block w-full md:w-1/3 px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent text-base" value="{{ old('quantity', 1) }}" min="1" max="100">
                </div>
                @endif
            </div>
        </div>

        <!-- CARD 5: Shipment & Buy Direct -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
             <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h2 class="text-base lg:text-lgfont-semibold text-gray-800">Logistics & Payment</h2>
            </div>
            <div class="p-3 md:p-4 space-y-8">

                <!-- Shipment Section -->
                <div id="shipment">
                    <label class="block text-sm font-semibold text-gray-700 mb-3">Delivery Options</label>
                    <div class="flex flex-col sm:flex-row gap-4 mb-4">
                        <label class="inline-flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-dark_green has-[:checked]:bg-green-50">
                            <input type="radio" name="shipment" value="Ship" class="text-dark_green border-gray-300 focus:ring-dark_green h-4 w-4" onchange="toggleShipping()" {{ old('shipment') == 'Ship' ? 'checked' : '' }}>
                            <span class="ml-2 text-gray-700 font-medium">Shipping Possible</span>
                        </label>
                        <label class="inline-flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-dark_green has-[:checked]:bg-green-50">
                            <input {{ old('shipment', 'Pickup') == 'Pickup' ? 'checked' : '' }} type="radio" name="shipment" value="Pickup" class="text-dark_green border-gray-300 focus:ring-dark_green h-4 w-4" onchange="toggleShipping()">
                            <span class="ml-2 text-gray-700 font-medium">Pickup Only</span>
                        </label>
                    </div>

                    <!-- Hidden Shipping Methods -->
                    @if(config('global.shipping_carriers_enabled'))
<div id="shipping" class="hidden pl-0 sm:pl-4 sm:border-l-2 sm:border-gray-200 space-y-2">
                        <p class="text-sm font-semibold text-gray-800 mb-2">Select Available Carriers:</p>
                        @foreach($shippings as $row)
                        <label class="flex items-start p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                            <input id="shipping-{{ $row->id }}" name="shipping[]" type="checkbox" value="{{ $row->id }}" class="mt-1 h-5 w-5 text-dark_green border-gray-300 rounded focus:ring-dark_green" {{ in_array($row->id, old('shipping', [])) ? 'checked' : '' }}>
                            <div class="ml-3">
                                <div class="flex items-center">
                                    <img class="w-8 h-auto mr-2" src="{{ Str::startsWith($row->logo, 'http') ? $row->logo : asset('uploads/shipping/'.$row->logo) }}" alt="Logo">
                                    <span class="font-bold text-gray-900">{{ $row->company }}</span>
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Max. {{ $row->weight }} kg, {{ $row->description }}</p>
                            </div>
                        </label>
                        @endforeach
                        <p id="shipping-error" class="text-red-500 text-sm hidden">Please select at least one shipping method.</p>
                    </div>
                    @endif
                </div>

                <!-- Buy Direct Section -->
                <div id="buyDirect" class="hidden bg-blue-50 border border-blue-100 rounded-xl p-5">
                    <label class="block text-md font-semibold text-blue-900 mb-4">Payment Method</label>
                    @php $sellerHasBank = !empty($user->account_number) && !empty($user->bank_code); @endphp
                    @error('buy_direct')
                        <p class="text-red-600 text-sm mb-3">{{ $message }}</p>
                    @enderror
                    @unless($sellerHasBank)
                        <p class="text-xs text-amber-800 bg-amber-50 border border-amber-200 rounded-lg px-3 py-2 mb-3">
                            To enable Buy Direct, first <a href="/user/payments" class="font-semibold underline">add your bank details</a> so we can pay you when the item sells.
                        </p>
                    @endunless
                    <p class="text-xs text-blue-900 mb-3">With Buy Direct the buyer pays online and we hold the money until they confirm they've received the item. You arrange delivery with the buyer.</p>


                    <div class="space-y-4">
                        <!-- Yes Option -->
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="radio" name="buy_direct" value="Yes" class="h-4 w-4 text-dark_green border-gray-300 focus:ring-dark_green" onchange="toggleBuyDirect()" {{ old('buy_direct') == 'Yes' ? 'checked' : '' }} required>
                            </div>

                            <div class="ml-3">
                                <span class="block text-sm font-medium text-gray-900">Enable "Buy Direct"</span>
                                <div class="mt-2 text-xs text-gray-600 space-y-2">
                                    <div class="flex">
                                        <span class="mr-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-blue-600">
                                              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                                            </svg>
                                        </span>
                                        <span>Selecting this option allows buyers to purchase your item using 'Buy Direct.' <br><a class="font-semibold text-dartk_green" href="/payments-refunds">Learn More</a> </span>
                                    </div>
                                    <p class="flex items-center"><svg class="w-4 h-4 mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg> Secure payment processing</p>
                                    <p class="flex items-center"><svg class="w-4 h-4 mr-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg> Fixed price (no negotiation)</p>
                                </div>
                            </div>
                        </div>

                        <!-- No Option -->
                        <div class="flex items-start">
                             <div class="flex items-center h-5">
                                <input {{ old('buy_direct', 'No') == 'No' ? 'checked' : '' }} type="radio" name="buy_direct" value="No" class="h-4 w-4 text-dark_green border-gray-300 focus:ring-dark_green">
                            </div>
                            <div class="ml-3">
                                <span class="block text-sm font-medium text-gray-900">No, do not use "Buy direct"</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 6: Description & Images -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
             <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h2 class="text-base lg:text-lg font-semibold text-gray-800">Visuals & Description</h2>
            </div>
            <div class="p-3 md:p-4 space-y-8">

                <!-- Description -->
                <div data-has-editor>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Detailed Description <span class="text-red-500">*</span></label>
                    <input id="content" type="hidden" name="description" value="{{ old('description') }}" required>
                    <div class="prose max-w-none">
                         <trix-editor input="content" class="min-h-[150px] border-gray-300 rounded-lg focus:border-dark_green focus:ring-dark_green"></trix-editor>
                    </div>
                    <div class="flex justify-end mt-1">
                        <span class="text-xs text-gray-400"><span id="word-count">0</span>/3500 characters</span>
                    </div>
                </div>

                <!-- IMPROVED IMAGE UPLOAD SECTION -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Product Photos <span class="text-red-500">*</span></label>

                    <!-- Quality Tips -->
                    <div class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <div>
                                <h6 class="text-sm font-semibold text-blue-900 mb-2">Tips for Best Quality Photos:</h6>
                                <ul class="text-xs text-blue-800 space-y-1">
                                    <li>• Use your phone camera at highest quality setting</li>
                                    <li>• Minimum resolution: <strong>800×600px</strong> (Recommended: <strong>1200×900px</strong>)</li>
                                    <li>• Take photos in good lighting (natural daylight works best)</li>
                                    <li>• Hold steady and ensure subject is in focus</li>
                                    <li id="min-images-tip">• <strong>Minimum of {{ $minImages }} image{{ $minImages === 1 ? '' : 's' }} required</strong> to post your ad. You can upload up to {{ $maxImages }} images</li>
                                    <li>• Avoid screenshots, watermarked, or blurry images</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div id="image-error" class="hidden mb-4 p-3 rounded-lg bg-red-100 text-red-800 text-sm font-medium"></div>

                    <!-- Dropzone -->
                    <div class="border-2 border-dashed border-gray-300 rounded-xl hover:bg-gray-50 hover:border-dark_green transition-colors relative group">
                        <label for="imageUpload" class="cursor-pointer flex flex-col items-center justify-center py-6 w-full h-full z-10">
                            <div class="p-4 rounded-full bg-blue-50 text-dark_green mb-3 group-hover:scale-110 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-900">Click to upload or drag images here</span>
                            <span class="text-xs text-gray-500 mt-1">PNG, JPG, WebP up to 20MB (Max {{ $maxImages }} images)</span>
                        </label>
                        <input id="imageUpload" name="images[]" type="file" multiple accept="image/*" class="hidden">
                    </div>

                    <!-- Validation Results Container -->
                    <div id="validation-results" class="mt-4 space-y-3 hidden"></div>

                    <!-- Sorting/Preview Area -->
                    <input type="hidden" name="image_order" id="image_order">
                    <div id="preview" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-4 mt-6">
                        <!-- ✅ Display temp images if they exist (from validation error) -->
                        @if (session('temp_images'))
                            @foreach (session('temp_images') as $index => $tempImage)
                                <div class="relative group" data-temp-image="{{ $index }}">
                                    <img src="{{ $tempImage['url'] }}" alt="Preview {{ $index + 1 }}" class="w-full h-32 object-cover rounded-lg border-2 border-dark_green shadow-md">
                                    <div class="absolute top-1 right-1 bg-dark_green text-white text-xs px-2 py-1 rounded">{{ $index + 1 }}</div>
                                    <button type="button" onclick="removeTempImage({{ $index }})" class="absolute top-1 left-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    </button>
                                    <input type="hidden" name="temp_image_paths[]" value="{{ $tempImage['path'] }}">
                                </div>
                            @endforeach
                        @endif
                        <!-- JS injects new previews here -->
                    </div>

                    <div class="flex items-center text-xs text-gray-500 mt-3">
                         <img src="{{ asset('frontend/images/swap.png') }}" class="h-5 w-auto mr-2 opacity-60">
                         <span>Drag and drop thumbnails to reorder them.</span>
                    </div>

                    @if (session('temp_images'))
                        <div class="mt-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <div class="text-sm text-green-800">
                                    <p class="font-semibold">Your images are retained!</p>
                                    <p class="text-xs mt-1">The {{ count(session('temp_images')) }} image(s) you uploaded are still here. You can add more or remove them.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- CARD 7: Location & Contact -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
             <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h2 class="text-base lg:text-lg font-semibold text-gray-800">Location & Contact</h2>
            </div>
            <div class="p-3 md:p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- State -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">State</label>
                        <select onchange="toggleLGA(this);" name="state" id="state" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent bg-white text-base" data-old-value="{{ old('state') }}">
                            <option value="">-- Select State --</option>
                            @foreach ($states as $state)
                                <option value="{{ $state->name }}" {{ old('state') == $state->name ? 'selected' : '' }}>{{ $state->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- LGA -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">LGA</label>
                        <select name="lga" id="lga" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent bg-white select-lga text-base" data-old-value="{{ old('lga') }}" required>
                             <!-- Populated by JS -->
                        </select>
                    </div>

                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Profile Name</label>
                        <input type="text" id="name" name="name" value="{{ $user->name }}" readonly class="block w-full px-4 py-3 rounded-lg border  border-gray-200 bg-gray-100 text-gray-500 cursor-not-allowed text-base">
                        <p class="text-xs text-gray-400 mt-1">To change this, please edit your profile settings.</p>
                    </div>

                    <!-- Show Contact -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Show Phone Number on Ad?</label>
                        <select name="show_contact" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent bg-white text-base">
                            <option value="Yes" {{ old('show_contact', 'Yes') == 'Yes' ? 'selected' : '' }}>Yes</option>
                            <option value="No" {{ old('show_contact') == 'No' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Section -->
        <div class="pt-6">
            <div class="mb-6 bg-white p-2 rounded-xl">
                 @include('admin.components.post-boost')
            </div>

            <div class="flex flex-col items-center space-y-4 mx-2">
                <p class="text-xs text-center text-gray-500 max-w-lg">
                    By clicking "Post Ad", you agree to our Terms of Use and Privacy Policy. Please ensure your ad does not violate our community guidelines.
                </p>
                <button type="submit" class="bg-dark_green hover:bg-secondary_dark text-white font-semibold py-3 px-8 rounded-lg transition duration-200 ease-in-out transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 w-full">
                    Post Ad
                </button>
            </div>
        </div>

    </form>
</section>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trix@2.0.8/dist/trix.css">
<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.min.js"></script>
<script src="{{ asset('frontend/js/lga.js') }}"></script>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    window.MIN_IMAGES = {{ $minImages }};
    window.MAX_IMAGES = {{ $maxImages }};
</script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script src='https://cdn.jsdelivr.net/npm/tesseract.js@4/dist/tesseract.min.js'></script>

{{-- Database-Driven Category UI Configuration (2026-01-31) --}}
<script src="{{ asset('dashboard/js/category-ui-manager.js') }}"></script>
<script src="{{ asset('dashboard/js/post-ad-v2.js') }}"></script>
{{-- Old hardcoded version (kept for rollback): <script src="{{ asset('dashboard/js/post-ad.js') }}"></script> --}}

<script src="{{ asset('dashboard/js/image-quality-validator.js') }}"></script>
<script src="{{ asset('dashboard/js/word-count.js') }}"></script>
<script src="{{ asset('dashboard/js/sortable.js') }}"></script>
<script src="{{ asset('dashboard/js/submit.js') }}"></script>

{{-- ✅ IMAGE QUALITY VALIDATION --}}
<script>
// Initialize Image Quality Validator
const imageQualityValidator = new ImageQualityValidator();
// Accumulates files across multiple picker selections
let accumulatedDT = new DataTransfer();
let validationResults = [];

// Render and validate ALL currently accumulated files
async function renderValidationResults() {
    const imageInput    = document.getElementById('imageUpload');
    const validationContainer = document.getElementById('validation-results');
    const errorDiv      = document.getElementById('image-error');
    const allFiles      = Array.from(accumulatedDT.files);

    validationContainer.innerHTML = '';
    errorDiv.classList.add('hidden');
    validationResults = [];

    if (allFiles.length === 0) {
        validationContainer.classList.add('hidden');
        return;
    }

    validationContainer.classList.remove('hidden');

    for (let i = 0; i < allFiles.length; i++) {
        const result = await imageQualityValidator.validateImage(allFiles[i]);
        result.fileIndex = i;
        validationResults.push(result);

        const resultHTML = imageQualityValidator.generateResultHTML(result, allFiles[i].name, i);
        const resultDiv  = document.createElement('div');
        resultDiv.innerHTML = resultHTML;
        validationContainer.appendChild(resultDiv.firstElementChild);
    }

    // Sync input.files with the accumulated set so the form submits all of them
    imageInput.files = accumulatedDT.files;

    const hasErrors = validationResults.some(r => !r.valid);
    if (hasErrors) {
        errorDiv.innerHTML = '<strong>⚠️ Quality Issues:</strong> Some images don\'t meet requirements. Fix or remove them before posting.';
        errorDiv.classList.remove('hidden');
        return;
    }

    const countValidation = imageQualityValidator.validateMinimumCount(accumulatedDT.files, {{ $minImages }});
    if (!countValidation.valid) {
        errorDiv.innerHTML = '<strong>⚠️ Not Enough Images:</strong> ' + countValidation.error;
        errorDiv.classList.remove('hidden');
    }
}

// Remove a specific image by its index in the accumulated list
function removeValidationResult(index) {
    const newDT = new DataTransfer();
    Array.from(accumulatedDT.files).forEach((file, i) => {
        if (i !== index) newDT.items.add(file);
    });
    accumulatedDT = newDT;
    renderValidationResults();
}

// Read a file into memory immediately so Android file-path changes don't
// break the upload (ERR_UPLOAD_FILE_CHANGED on Google Photos / camera apps).
function readFileIntoMemory(file) {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            const blob = new Blob([e.target.result], { type: file.type });
            resolve(new File([blob], file.name, { type: file.type, lastModified: file.lastModified }));
        };
        reader.onerror = () => resolve(file); // fallback: use original if read fails
        reader.readAsArrayBuffer(file);
    });
}

// Each new picker selection ADDS to the accumulated list (not replaces)
document.getElementById('imageUpload').addEventListener('change', async function(e) {
    for (const file of e.target.files) {
        const memFile = await readFileIntoMemory(file);
        accumulatedDT.items.add(memFile);
    }
    renderValidationResults();
});

// ✅ MINIMUM IMAGE COUNT: Block form submission if fewer than 3 images
document.querySelector('form[action="/user/post-ad"]').addEventListener('submit', function(e) {
    const imageInput = document.getElementById('imageUpload');
    const tempImagePaths = document.querySelectorAll('input[name="temp_image_paths[]"]');
    const categoryId = parseInt(document.getElementById('category')?.value || 0);
    const isJobsCategory = categoryId === 3 || categoryId === 18;

    // Count total images: new uploads + retained temp images
    const newImageCount = imageInput ? imageInput.files.length : 0;
    const tempImageCount = tempImagePaths.length;
    const totalImages = newImageCount + tempImageCount;

    const minRequired = {{ $minImages }};
    const maxAllowed  = {{ $maxImages }};

    if (!isJobsCategory && totalImages < minRequired) {
        e.preventDefault();
        const errorDiv = document.getElementById('image-error');
        if (errorDiv) {
            errorDiv.innerHTML = '<strong>⚠️ Minimum ' + minRequired + ' Image' + (minRequired === 1 ? '' : 's') + ' Required:</strong> Please upload at least ' + minRequired + ' images to post your ad. You currently have ' + totalImages + '.';
            errorDiv.classList.remove('hidden');
            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    } else if (!isJobsCategory && totalImages > maxAllowed) {
        e.preventDefault();
        const errorDiv = document.getElementById('image-error');
        if (errorDiv) {
            errorDiv.innerHTML = '<strong>⚠️ Too Many Images:</strong> Maximum ' + maxAllowed + ' images allowed. You currently have ' + totalImages + '.';
            errorDiv.classList.remove('hidden');
            errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
});

{{-- ✅ RESTORE OLD VALUES ON VALIDATION ERROR --}}
// Function to remove temp image
function removeTempImage(index) {
    const imageDiv = document.querySelector(`[data-temp-image="${index}"]`);
    if (imageDiv) {
        imageDiv.remove();

        // If no more temp images, hide the success message
        const remainingTempImages = document.querySelectorAll('[data-temp-image]');
        if (remainingTempImages.length === 0) {
            const successMsg = document.querySelector('.bg-green-50');
            if (successMsg) {
                successMsg.remove();
            }
        }
    }
}

// Restore old values after page fully loads and main scripts initialize
window.addEventListener('load', function() {
    // Wait a bit to ensure all scripts are initialized
    setTimeout(async function() {

        // Get old values from data attributes
        const oldCategory = document.getElementById('category')?.dataset.oldValue;
        const oldSubcategory = document.getElementById('subcategory')?.dataset.oldValue;
        const oldBrand = document.getElementById('brand')?.dataset.oldValue;
        const oldModel = document.getElementById('model')?.dataset.oldValue;
        const oldState = document.getElementById('state')?.dataset.oldValue;
        const oldLga = document.getElementById('lga')?.dataset.oldValue;


        // 🚗 PRESERVE CAR DETAIL VALUES BEFORE RESTORATION
        // Store current values from car fields before JavaScript clears them
        const carFieldValues = {};
        if (document.getElementById('divCar')) {
            const carFields = document.getElementById('divCar').querySelectorAll('input, select, textarea');
            carFields.forEach(field => {
                if (field.type === 'checkbox' || field.type === 'radio') {
                    if (field.checked) {
                        if (!carFieldValues[field.name]) carFieldValues[field.name] = [];
                        carFieldValues[field.name].push(field.value);
                    }
                } else if (field.value) {
                    carFieldValues[field.name] = field.value;
                }
            });
        }

        // 📱 PRESERVE PHONE DETAIL VALUES
        const phoneFieldValues = {};
        if (document.getElementById('divPhone')) {
            const phoneFields = document.getElementById('divPhone').querySelectorAll('input, select, textarea');
            phoneFields.forEach(field => {
                if (field.value) {
                    phoneFieldValues[field.name] = field.value;
                }
            });
        }

        // Helper function to wait for dropdown to be populated
        const waitForOptions = (select, minOptions = 1, maxWait = 5000) => {
            return new Promise((resolve, reject) => {
                const startTime = Date.now();
                const checkOptions = () => {
                    if (select.options.length > minOptions) {
                        resolve();
                    } else if (Date.now() - startTime > maxWait) {
                        reject();
                    } else {
                        setTimeout(checkOptions, 150);
                    }
                };
                checkOptions();
            });
        };

        // Helper function to set select value and trigger change
        const setSelectValue = async (selectId, value, waitForPopulation = true) => {
            const select = document.getElementById(selectId);
            if (!select || !value) {
                return false;
            }

            try {
                if (waitForPopulation) {
                    await waitForOptions(select);
                }

                // Set the value
                select.value = value;

                // Verify it was set
                if (select.value == value) {
                    // Trigger change event
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    return true;
                } else {
                    console.error(`❌ ${selectId} failed to set value: ${value}`);
                    return false;
                }
            } catch (error) {
                console.error(`❌ Error setting ${selectId}:`, error);
                return false;
            }
        };

        try {
            // Restore category → subcategory → brand → model chain
            if (oldCategory) {
                const categorySelect = document.getElementById('category');
                if (categorySelect) {
                    // Set category (already populated)
                    categorySelect.value = oldCategory;

                    // Trigger change to populate subcategories
                    categorySelect.dispatchEvent(new Event('change', { bubbles: true }));

                    // Wait for subcategories to load, then restore
                    if (oldSubcategory) {
                        await waitForOptions(document.getElementById('subcategory'));
                        await setSelectValue('subcategory', oldSubcategory);

                        // Wait for brands to load, then restore
                        if (oldBrand) {
                            await waitForOptions(document.getElementById('brand'));
                            await setSelectValue('brand', oldBrand);

                            // Wait for models to load, then restore
                            if (oldModel) {
                                await waitForOptions(document.getElementById('model'));
                                await setSelectValue('model', oldModel);
                            }
                        }
                    }
                }
            }

            // Restore state → LGA chain (independent of category)
            if (oldState) {
                const stateSelect = document.getElementById('state');
                if (stateSelect) {
                    // State is already populated, just set value
                    stateSelect.value = oldState;

                    // Trigger LGA population
                    if (typeof toggleLGA === 'function') {
                        toggleLGA(stateSelect);
                    } else {
                        stateSelect.dispatchEvent(new Event('change', { bubbles: true }));
                    }

                    // Wait for LGAs to load, then restore
                    if (oldLga) {
                        await waitForOptions(document.getElementById('lga'));
                        await setSelectValue('lga', oldLga);
                    }
                }
            }

            // Restore shipping visibility if "Ship" was selected
            const shipmentShip = document.querySelector('input[name="shipment"][value="Ship"]');
            if (shipmentShip && shipmentShip.checked) {
                const shippingDiv = document.getElementById('shipping');
                if (shippingDiv) {
                    shippingDiv.classList.remove('hidden');
                }
            }

            // 🚗 RESTORE CAR DETAIL VALUES (after UI has been updated)
            // Wait a bit for UI updates to complete, then restore car values
            await new Promise(resolve => setTimeout(resolve, 300));

            if (Object.keys(carFieldValues).length > 0) {
                const divCar = document.getElementById('divCar');
                if (divCar && !divCar.classList.contains('hidden')) {
                    Object.keys(carFieldValues).forEach(fieldName => {
                        const field = document.querySelector(`[name="${fieldName}"]`);
                        if (field) {
                            if (field.type === 'checkbox' || field.type === 'radio') {
                                // Restore checkboxes/radios
                                const values = Array.isArray(carFieldValues[fieldName])
                                    ? carFieldValues[fieldName]
                                    : [carFieldValues[fieldName]];

                                values.forEach(value => {
                                    const checkbox = document.querySelector(`[name="${fieldName}"][value="${value}"]`);
                                    if (checkbox) {
                                        checkbox.checked = true;
                                    }
                                });
                            } else {
                                // Restore input/select values
                                field.value = carFieldValues[fieldName];
                            }
                        }
                    });
                }
            }

            // 📱 RESTORE PHONE DETAIL VALUES
            if (Object.keys(phoneFieldValues).length > 0) {
                const divPhone = document.getElementById('divPhone');
                if (divPhone && !divPhone.classList.contains('hidden')) {
                    Object.keys(phoneFieldValues).forEach(fieldName => {
                        const field = document.querySelector(`[name="${fieldName}"]`);
                        if (field) {
                            field.value = phoneFieldValues[fieldName];
                        }
                    });
                }
            }

            // 🔒 HIDE VALIDATION ERRORS FOR HIDDEN FIELDS
            // If salary/expected_salary divs are hidden, hide their error messages
            setTimeout(() => {
                const salaryDiv = document.getElementById('salary');
                const expectedSalaryDiv = document.getElementById('expectedSalary');

                if (salaryDiv && salaryDiv.classList.contains('hidden')) {
                    const salaryError = document.querySelector('.salary-error');
                    if (salaryError) {
                        salaryError.style.display = 'none';
                    }
                }

                if (expectedSalaryDiv && expectedSalaryDiv.classList.contains('hidden')) {
                    const expectedSalaryError = document.querySelector('.expected-salary-error');
                    if (expectedSalaryError) {
                        expectedSalaryError.style.display = 'none';
                    }
                }
            }, 100);

        } catch (error) {
            console.error('❌ Error during form restoration:', error);
        }
    }, 500); // Wait 500ms for scripts to initialize
});
</script>

@include('user.layouts.footer')
