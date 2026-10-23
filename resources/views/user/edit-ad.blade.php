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

    /* Edit Page Specific: Image Container for Sortable */
    .image-container {
        position: relative;
    }
</style>

<section class="max-w-4xl mx-auto my-8 px-1 sm:px-1 pb-20">

    <!-- Page Title -->
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl font-bold text-dark_green">Edit Ad Details</h1>
        <p class="text-gray-500 mt-1">Update the information below to modify your advertisement.</p>
    </div>

    @include('public.components.flash-message')

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
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form action="/user/edit-ad/{{ $advert->id }}" id="advertForm" method="POST" role="form" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <!-- CARD 1: Basic Information -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h2 class="text-base lg:text-lg font-semibold text-gray-800">Basic Information</h2>
            </div>
            <div class="p-3 md:p-4 space-y-6">

                <!-- Ad Type -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-3">What do you want to do?</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <label class="relative flex items-center p-4 border rounded-xl cursor-pointer hover:bg-green-50 hover:border-dark_green transition-all has-[:checked]:border-dark_green has-[:checked]:bg-green-50 has-[:checked]:ring-1 has-[:checked]:ring-dark_green">
                            <input type="radio" name="ad_type" value="Private" {{ $advert->ad_type === 'Private' ? 'checked' : '' }} class="w-5 h-5 text-dark_green border border-gray-300 focus:ring-dark_green" required>
                            <span class="ml-3 block text-gray-900 font-medium">I offer (Selling)</span>
                        </label>
                        <label class="relative flex items-center p-4 border rounded-xl cursor-pointer hover:bg-green-50 hover:border-dark_green transition-all has-[:checked]:border-dark_green has-[:checked]:bg-green-50 has-[:checked]:ring-1 has-[:checked]:ring-dark_green">
                            <input type="radio" name="ad_type" value="Commercial" {{ $advert->ad_type === 'Commercial' ? 'checked' : '' }} class="w-5 h-5 text-dark_green border border-gray-300 focus:ring-dark_green">
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
                               placeholder="Ad Title"
                               class="block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-2 focus:ring-dark_green focus:border-transparent transition-colors text-base"
                               value="{{ $advert->ad_title }}"
                               maxlength="75"
                               required>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-xs text-gray-400 bg-white pl-1"><span id="char-count">0</span>/75</span>
                        </div>
                    </div>
                </div>

                <!-- Categories Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Category -->
                    <div>
                        <label for="category" class="block text-sm font-semibold text-gray-700 mb-2">Category</label>
                        <select id="category" name="category" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent bg-white text-base" required data-selected="{{ $advert->category }}">
                            <option value="">Select Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $advert->category == $category->id ? 'selected' : '' }}>
                                    {{ $category->category }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Subcategory -->
                    <div>
                        <label for="subcategory" class="block text-sm font-semibold text-gray-700 mb-2">Sub Category</label>
                        <select id="subcategory" name="subcategory" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent bg-white text-base" required data-selected="{{ $advert->sub_category }}">
                            <option value="">Select Subcategory</option>
                            @foreach($subcategories as $subcategory)
                                <option value="{{ $subcategory->id }}" {{ $advert->sub_category == $subcategory->id ? 'selected' : '' }}>
                                    {{ $subcategory->sub_category }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Brand -->
                    <div>
                        <label for="brand" class="block text-sm font-semibold text-gray-700 mb-2">Brand</label>
                        <select id="brand" name="brand" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent bg-white text-base" required data-selected="{{ $advert->brand }}">
                            <option value="">Select Brand</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->id }}" {{ $advert->brand == $brand->id ? 'selected' : '' }}>
                                    {{ $brand->brand }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Model (Conditional) -->
                    <div id="divModel" class="{{ in_array($advert->sub_category, [2, 21, 23]) ? '' : 'hidden' }}">
                        <label for="model" class="block text-sm font-semibold text-gray-700 mb-2">Model</label>
                        <select id="model" name="model" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent bg-white text-base"
                            data-selected="{{ in_array($advert->sub_category, [2, 21, 23]) ? optional($advert->car)->model : '' }}">
                            <option value="">Select Model</option>
                            @foreach($models as $model)
                                <option value="{{ $model->id }}"
                                    {{ in_array($advert->sub_category, [2, 21, 23]) && optional($advert->car)->model == $model->id ? 'selected' : '' }}>
                                    {{ $model->model }}
                                </option>
                            @endforeach
                        </select>
                        @error('model') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Item Condition -->
                    <div id="itemCondition" class="{{ (in_array($advert->category, [1, 3, 7, 11, 18]) || in_array($advert->sub_category, [2, 6, 16, 17, 18, 19, 21, 23])) ? 'hidden' : '' }}">
                        <label for="pr" class="block text-sm font-semibold text-gray-700 mb-2">Item Condition <span class="text-red-500">*</span></label>
                        <select id="pr" name="item_condition" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent bg-white text-base">
                            <option value="">Please Choose</option>
                            <option value="New" {{ $advert->item_condition == 'New' ? 'selected' : '' }}>New</option>
                            <option value="Foreign Used" {{ $advert->item_condition == 'Foreign Used' ? 'selected' : '' }}>Foreign Used</option>
                            <option value="Locally Used" {{ $advert->item_condition == 'Locally Used' ? 'selected' : '' }}>Locally Used</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 2: Vehicle Specifics (Conditional) -->
        <div id="divCar" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden {{ in_array($advert->sub_category, [2, 21, 23]) ? '' : 'hidden' }}">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h2 class="text-base lg:text-lg font-semibold text-gray-800">Vehicle Specifics</h2>
            </div>

            <div class="p-3 md:p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Mileage -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Mileage *</label>
                        <div class="flex">
                            <input type="text" name="mileage" placeholder="0" value="{{ optional($advert->car)->mileage }}" class="block w-full px-4 py-3 rounded-l-lg border border-gray-300 focus:ring-dark_green focus:border-dark_green text-base">
                            <span class="inline-flex items-center px-3 rounded-r-lg border border-l-0 border border-gray-300 bg-gray-50 text-gray-500 text-sm">Km</span>
                        </div>
                    </div>

                    <!-- Condition -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Vehicle Condition *</label>
                        <select name="condition" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base">
                            <option value="">Please Choose</option>
                            <option value="Local used" {{ optional($advert->car)->condition == 'Local used' ? 'selected' : '' }}> Local used</option>
                            <option value="Foreign used" {{ optional($advert->car)->condition == 'Foreign used' ? 'selected' : '' }}>Foreign used</option>
                            <option value="Brand new" {{ optional($advert->car)->condition == 'Brand new' ? 'selected' : '' }}>Brand new</option>
                        </select>
                        @error('condition') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Registration -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Registration</label>
                        <select name="registration" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base">
                            <option value="">--Select Type--</option>
                            <option value="Registered" {{ optional($advert->car)->registration == 'Registered' ? 'selected' : '' }}>Registered</option>
                            <option value="Unregistered" {{ optional($advert->car)->registration == 'Unregistered' ? 'selected' : '' }}>Unregistered</option>
                        </select>
                        @error('registration') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Fuel -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Fuel Type *</label>
                        <select name="fuel" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base">
                            <option value="">Please Choose</option>
                            @foreach(['Petrol','Diesel','Natural gas CNG','LPG','Hybrid','Electric'] as $fuelType)
                                <option value="{{ $fuelType }}" {{ optional($advert->car)->fuel === $fuelType ? 'selected' : '' }}>{{ $fuelType }}</option>
                            @endforeach
                        </select>
                        @error('fuel') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Transmission -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Transmission *</label>
                        <select name="transmission" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base">
                            <option value="">Please Choose</option>
                            <option value="Automatic" {{ optional($advert->car)->transmission == 'Automatic' ? 'selected' : '' }}>Automatic</option>
                            <option value="Manual" {{ optional($advert->car)->transmission == 'Manual' ? 'selected' : '' }}>Manual</option>
                            <option value="CVT" {{ optional($advert->car)->transmission == 'CVT' ? 'selected' : '' }}>CVT</option>
                            <option value="AMT" {{ optional($advert->car)->transmission == 'AMT' ? 'selected' : '' }}>AMT</option>
                            <option value="Other" {{ optional($advert->car)->transmission == 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('transmission') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Vehicle Type -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Body Type <span class="text-red-500">*</span></label>
                        <select name="vehicle_type" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base">
                            <option value="">Please Choose</option>
                            @foreach(['Small Car','Station Wagon','Limousine','Convertible','SUV/Off Road Vehicle','Van/Bus','Coupe','Truck','Others'] as $vType)
                                <option value="{{ $vType }}" {{ optional($advert->car)->vehicle_type === $vType ? 'selected' : '' }}>{{ $vType }}</option>
                            @endforeach
                        </select>
                        @error('vehicle_type') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Exterior Color -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Exterior Color *</label>
                        <select id="exterior_color" name="exterior_color" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base">
                            <option value="">Choose Color</option>
                            @php $carColors = ['Black'=>'Black','White'=>'White','Gray'=>'Gray','Silver'=>'Silver','Blue'=>'Blue','Red'=>'Red','Gold'=>'Gold','Green'=>'Green','Beige'=>'Beige','Brown'=>'Brown','Yellow'=>'Yellow','Orange'=>'Orange','Purple'=>'Purple','Maroon'=>'Maroon','Burgundy'=>'Burgundy','Bronze'=>'Bronze','Champagne'=>'Champagne','Pearl White'=>'Pearl White','Gunmetal'=>'Gunmetal Gray','Midnight Blue'=>'Midnight Blue','Navy Blue'=>'Navy Blue','Olive Green'=>'Olive Green','Charcoal'=>'Charcoal','Matte Black'=>'Matte Black','Two Tone'=>'Two-Tone','Gradient'=>'Gradient','Chameleon'=>'Chameleon','Custom Wrap'=>'Custom Wrap','Chrome'=>'Chrome','Camo'=>'Camouflage','Others'=>'Others']; @endphp
                            @foreach($carColors as $value => $label)
                                <option value="{{ $value }}" {{ optional($advert->car)->exterior_color === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('exterior_color') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Doors -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Number of Doors *</label>
                        <select name="doors" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base">
                             <option value="">Please Choose</option>
                            @foreach(['1 Door','2 Doors','3 Doors','4 Doors'] as $door)
                                <option value="{{ $door }}" {{ optional($advert->car)->doors == $door ? 'selected' : '' }}>{{ $door }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Interior Material -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Interior Material *</label>
                        <select name="material_interior" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base">
                             <option value="">Please Choose</option>
                            @foreach(['Full Grain Leather','Partial Leather','Material','Velour', 'Alcantara'] as $mat)
                                <option value="{{ $mat }}" {{ optional($advert->car)->material_interior == $mat ? 'selected' : '' }}>{{ $mat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Equipment -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <h3 class="text-md font-bold text-gray-800 mb-4">Features & Equipment</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

                        <!-- Exterior Eq -->
                        <div>
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block mb-3">Exterior</span>
                            @php $selExt = json_decode(optional($advert->car)->exterior_equipment) ?? []; @endphp
                            <div class="space-y-3">
                                @foreach(['Trailer hitch', 'Parking assistance', 'Alloy wheels', 'Xenon/LED headlights'] as $item)
                                <label class="flex items-center group cursor-pointer">
                                    <input type="checkbox" name="exterior_equipment[]" value="{{ $item }}" class="w-5 h-5 text-dark_green rounded border border-gray-300 focus:ring-dark_green" {{ in_array($item, $selExt) ? 'checked' : '' }}>
                                    <span class="ml-3 text-sm text-gray-700">{{ $item }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Interior Eq -->
                        <div>
                             <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block mb-3">Interior</span>
                             @php $selInt = json_decode(optional($advert->car)->interior) ?? []; @endphp
                             <div class="space-y-3">
                                 @foreach(['Air conditioning', 'Navigation system', 'Radio/tuner', 'Bluetooth', 'Seat heating', 'Cruise control', 'Sunroof/panoramic roof'] as $item)
                                <label class="flex items-center group cursor-pointer">
                                    <input type="checkbox" name="interior[]" value="{{ $item }}" class="w-5 h-5 text-dark_green rounded border border-gray-300 focus:ring-dark_green" {{ in_array($item, $selInt) ? 'checked' : '' }}>
                                    <span class="ml-3 text-sm text-gray-700">{{ $item }}</span>
                                </label>
                                @endforeach
                             </div>
                        </div>

                        <!-- Security -->
                         <div>
                             <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block mb-3">Security</span>
                             @php $selSec = json_decode(optional($advert->car)->security) ?? []; @endphp
                             <div class="space-y-3">
                                 @foreach(['Anti-lock braking system (ABS)', 'Service history maintained', 'Airbags', 'Alarm system'] as $item)
                                <label class="flex items-center group cursor-pointer">
                                    <input type="checkbox" name="security[]" value="{{ $item }}" class="w-5 h-5 text-dark_green rounded border border-gray-300 focus:ring-dark_green" {{ in_array($item, $selSec) ? 'checked' : '' }}>
                                    <span class="ml-3 text-sm text-gray-700">{{ $item }}</span>
                                </label>
                                @endforeach
                             </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 3: Phone Details (Conditional) -->
        <div id="divPhone" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden {{ $advert->sub_category == 6 ? '' : 'hidden' }}">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h2 class="text-base lg:text-lg font-semibold text-gray-800">Device Details</h2>
            </div>
            <div class="p-3 md:p-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Phone Color -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Color *</label>
                        <select name="phone_color" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base">
                            <option value="">Choose Color</option>
                            @foreach(['Black','White','Gray','Silver','Gold','Blue','Red','Green','Yellow','Orange','Purple','Pink','Rose Gold','Bronze','Copper','Midnight','Space Gray','Midnight Green','Lavender','Aqua','Teal','Turquoise','Coral','Champagne','Graphite','Starlight','Twilight','Gradient','Transparent','Others'] as $pColor)
                                <option value="{{ $pColor }}" {{ optional($advert->phone)->color === $pColor ? 'selected' : '' }}>{{ $pColor }}</option>
                            @endforeach
                        </select>
                        @error('phone_color') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Device -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Device Type *</label>
                        <select name="device" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base">
                            <option value="">Please Choose</option>
                            @foreach(['Device','Accessories','Device & Accessories'] as $dType)
                                <option value="{{ $dType }}" {{ optional($advert->phone)->device == $dType ? 'selected' : '' }}>{{ $dType }}</option>
                            @endforeach
                        </select>
                        @error('device') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <!-- Condition -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Condition *</label>
                        <select name="phone_condition" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base">
                            <option value="">Please Choose</option>
                            @foreach(['New - Unboxed', 'Foreign Used - No Packaging', 'Used - Very Good', 'Used - Good', 'Used - In Order', 'Used - Defect'] as $pCond)
                                <option value="{{ $pCond }}" {{ optional($advert->phone)->condition == $pCond ? 'selected' : '' }}>{{ $pCond }}</option>
                            @endforeach
                        </select>
                        @error('phone_condition') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 4: Financials -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
             <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h2 class="text-base lg:text-lg font-semibold text-gray-800">Financials</h2>
            </div>
            <div class="p-3 md:p-4 space-y-6">
                <!-- Price -->
                <div id="price">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Price</label>
                     <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-base">Br </span>
                            </div>
                             <input type="text" name="price_display" id="price_display" class="block w-full pl-8 pr-12 py-3 border border border-gray-300 rounded-lg focus:ring-dark_green focus:border-dark_green text-base" placeholder="0.00" value="{{ old('price') ? number_format(old('price'), 0, '.', ',') : number_format($advert->price, 0, '.', ',') }}">
                             <input type="hidden" name="price" id="price_hidden" value="{{ old('price', $advert->price) }}">
                        </div>
                        <div>
                            <select name="price_type" class="custom-select block w-full px-4 py-3 border border border-gray-300 bg-white rounded-lg shadow-sm focus:ring-dark_green focus:border-transparent text-base">
                                <option value="Fixed" {{ ($advert->price_type =='Fixed') ? "selected" : ""; }}>Fixed Price</option>
                                <option value="Negotiable" {{ ($advert->price_type =='Negotiable') ? "selected" : ""; }}>Negotiable</option>
                                <option value="Give Away" {{ ($advert->price_type =='Give Away') ? "selected" : ""; }}>Give Away</option>
                            </select>
                        </div>
                        <div id="services" class="flex items-center h-full pt-1">
                             <input type="hidden" name="contact_price" value="no">
                             <label class="flex items-center cursor-pointer select-none">
                                <input type="checkbox" name="contact_price" value="yes" class="w-5 h-5 text-dark_green rounded border border-gray-300 focus:ring-dark_green" {{ $advert->contact_price == 'yes' ? 'checked' : '' }}>
                                <span class="ml-2 text-sm text-gray-700">Contact for Price</span>
                            </label>
                        </div>
                     </div>
                </div>

                <!-- Salaries (Hidden by default, shown via JS) -->
                <div id="salary" class="hidden">
                     <label class="block text-sm font-semibold text-gray-700 mb-2">Salary</label>
                     <select name="salary" class="custom-select block w-full md:w-1/2 px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent text-base">
                        <option value="">--Select Salary--</option>
                        @foreach(['Commission', 'Below Br 2,000', 'Br 2,000 - Br 4,000', 'Above Br 50,000'] as $sal)
                            <option value="{{ $sal }}" {{ $advert->salary == $sal ? 'selected' : '' }}>{{ $sal }}</option>
                        @endforeach
                    </select>
                </div>
                 <div id="expectedSalary" class="hidden">
                     <label class="block text-sm font-semibold text-gray-700 mb-2">Expected Salary</label>
                     <select name="expected_salary" class="custom-select block w-full md:w-1/2 px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green focus:border-transparent text-base">
                        <option value="">--Select Expected Salary--</option>
                        @foreach(['Below Br 5,000', 'Br 5,000 - Br 7,500', 'Above Br 50,000'] as $expSal)
                            <option value="{{ $expSal }}" {{ $advert->expected_salary == $expSal ? 'selected' : '' }}>{{ $expSal }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Quantity -->
                @if($user->acc_type=="Commercial")
                <div id="quantity">
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Item Quantity</label>
                    <input type="number" name="quantity" class="block w-full md:w-1/3 px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green text-base" value="{{ $advert->quantity }}" min="1" max="100">
                </div>
                @endif
            </div>
        </div>

        <!-- CARD 5: Logistics & Payment -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h2 class="text-base lg:text-lg font-semibold text-gray-800">Logistics & Payment</h2>
            </div>
            <div class="p-3 md:p-4 space-y-8">
                <!-- Shipment -->
                <div id="shipment">
                     <label class="block text-sm font-semibold text-gray-700 mb-3">Delivery Options</label>
                     <div class="flex flex-col sm:flex-row gap-4 mb-4">
                        <label class="inline-flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-dark_green has-[:checked]:bg-green-50">
                            <input type="radio" name="shipment" value="Ship" class="text-dark_green border border-gray-300 focus:ring-dark_green h-4 w-4" {{ $advert->shipment === 'Ship' ? 'checked' : '' }} onclick="toggleDiv()">
                            <span class="ml-2 text-gray-700 font-medium">Shipping Possible</span>
                        </label>
                        <label class="inline-flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50 has-[:checked]:border-dark_green has-[:checked]:bg-green-50">
                            <input type="radio" name="shipment" value="Pickup" class="text-dark_green border border-gray-300 focus:ring-dark_green h-4 w-4" {{ $advert->shipment === 'Pickup' ? 'checked' : '' }} onclick="toggleDiv()">
                            <span class="ml-2 text-gray-700 font-medium">Only Pickup</span>
                        </label>
                     </div>

                     <!-- Shipping Methods (Hidden by default based on JS) -->
                    @if(config('global.shipping_carriers_enabled'))
<div id="shipping" class="hidden pl-0 sm:pl-4 sm:border-l-2 sm:border-gray-200 space-y-2">
                        <p class="text-sm font-semibold text-gray-800 mb-2">Select Carriers:</p>
                        @foreach($shippings as $row)
                        <label class="flex items-start p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors">
                             <input id="shipping-{{ $row->id }}" name="shipping[]" type="checkbox" value="{{ $row->id }}" class="mt-1 h-5 w-5 text-dark_green border border-gray-300 rounded focus:ring-dark_green">
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

                <!-- Buy Direct -->
                <div id="buyDirect" class="bg-blue-50 border border-blue-100 rounded-xl p-5 {{ in_array($advert->sub_category, [2, 21, 23]) ? 'hidden' : '' }}">
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
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex items-center h-5">
                                <input type="radio" name="buy_direct" value="Yes" {{ $advert->buy_direct === 'Yes' ? 'checked' : '' }} class="h-4 w-4 text-dark_green border border-gray-300 focus:ring-dark_green" required>
                            </div>
                            <div class="ml-3">
                                <span class="block text-sm font-medium text-gray-900">Enable "Buy Direct"</span>
                                <div class="mt-2 text-xs text-gray-600">Secure payment, fixed price, no negotiation.</div>
                            </div>
                        </div>
                        <div class="flex items-start">
                             <div class="flex items-center h-5">
                                <input type="radio" name="buy_direct" value="No" {{ $advert->buy_direct === 'No' ? 'checked' : '' }} class="h-4 w-4 text-dark_green border border-gray-300 focus:ring-dark_green">
                            </div>
                            <div class="ml-3">
                                <span class="block text-sm font-medium text-gray-900">No, do not use "Buy direct"</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CARD 6: Visuals & Description -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h2 class="text-base lg:text-lg font-semibold text-gray-800">Visuals & Description</h2>
            </div>
            <div class="p-3 md:p-4 space-y-8">
                <!-- Description -->
                <div data-has-editor>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Detailed Description *</label>
                     <input id="content" type="hidden" name="description" value="{{ old('description', $advert->description ?? '') }}" required>
                     <div class="prose max-w-none">
                         <trix-editor input="content" class="min-h-[150px] border border-gray-300 rounded-lg focus:border-dark_green focus:ring-dark_green"></trix-editor>
                    </div>
                     <div class="flex justify-end mt-1">
                        <span class="text-xs text-gray-400"><span id="word-count">0</span>/3500 characters</span>
                    </div>
                </div>

                <!-- IMAGES (EDIT MODE) -->
                <div>
                     <label class="block text-sm font-semibold text-gray-700 mb-2">Product Photos</label>

                     <div class="mb-4 p-3 rounded-lg bg-blue-50 border border-blue-100 text-sm text-blue-800">
                         Must keep at least <strong>{{ $minImages }}</strong> image{{ $minImages === 1 ? '' : 's' }}. Maximum <strong>{{ $maxImages }}</strong> images allowed.
                     </div>

                     @error('images')
                         <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-800 text-sm font-medium">{{ $message }}</div>
                     @enderror
                     @error('deleted_images')
                         <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-800 text-sm font-medium">{{ $message }}</div>
                     @enderror

                     <!-- Dropzone -->
                     <div class="border-2 border-dashed border border-gray-300 rounded-xl hover:bg-gray-50 hover:border-dark_green transition-colors relative group mb-6">
                        <label for="imageUpload" class="cursor-pointer flex flex-col items-center justify-center py-8 w-full h-full z-10">
                            <div class="p-4 rounded-full bg-blue-50 text-dark_green mb-3 group-hover:scale-110 transition-transform">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                </svg>
                            </div>
                            <span class="text-sm font-medium text-gray-900">Add more photos</span>
                            <span class="text-xs text-gray-500 mt-1">PNG, JPG, WebP up to 20MB (Max {{ $maxImages }} images total)</span>
                        </label>
                        <input name="images[]" type="file" id="imageUpload" multiple class="hidden" accept="image/*">
                        <input type="hidden" name="existing_image_order" id="existing_image_order">
                    </div>

                    <!-- Existing & New Preview Grid -->
                    <div id="preview" class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 gap-4">
                        @foreach($advert->getMedia('images') as $media)
                        <div class="relative group cursor-move image-container rounded-lg overflow-hidden shadow-sm border border-gray-200" draggable="true" data-id="{{ $media->id }}">
                            <img src="{{ $media->getUrl('thumbnail') }}" class="w-full h-32 object-cover">
                            <button type="button" class="absolute top-1 right-1 w-6 h-6 text-white bg-red-500 rounded-full hover:bg-red-600 flex items-center justify-center delete-image shadow-md transition-colors" data-id="{{ $media->id }}">&times;</button>
                            <input type="hidden" name="existing_images[]" value="{{ $media->id }}">
                        </div>
                        @endforeach
                    </div>
                     <div class="flex items-center text-xs text-gray-500 mt-3">
                         <img src="{{ asset('frontend/images/swap.png') }}" class="h-5 w-auto mr-2 opacity-60">
                         <span>Drag and drop to reorder images.</span>
                    </div>
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
                        <select onchange="toggleLGA(this);" name="state" id="state" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base">
                            <option value="" selected="selected">- Select State -</option>
                            @foreach($states as $state)
                            <option value="{{ $state->name }}" {{ ($advert->state == $state->name) ? "selected" : "" }}>{{ $state->name }}</option>
                            @endforeach
                        </select>
                     </div>

                     <!-- LGA -->
                     <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">LGA</label>
                        <select name="lga" id="lga" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base select-lga" required>
                            @if($advert->state && $advert->lga)
                                <option value="{{ $advert->lga }}" selected>{{ $advert->lga }}</option>
                            @endif
                        </select>
                     </div>

                     <!-- Name -->
                     <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Profile Name</label>
                        <input type="text" id="name" name="name" value="{{ $user->name }}" readonly class="block w-full px-4 py-3 rounded-lg border-gray-200 bg-gray-100 text-gray-500 cursor-not-allowed text-base">
                     </div>

                     <!-- Show Contact -->
                     <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Show Phone Number?</label>
                        <select name="show_contact" class="custom-select block w-full px-4 py-3 rounded-lg border border-gray-300 shadow-sm focus:ring-dark_green bg-white text-base">
                            <option value="Yes" {{ $advert->show_contact == 'Yes' ? 'selected' : '' }}>Yes</option>
                            <option value="No" {{ $advert->show_contact == 'No' ? 'selected' : '' }}>No</option>
                        </select>
                     </div>
                 </div>
             </div>
        </div>

        <!-- Submit Section -->
        <div class="pt-6">
            <div class="flex flex-col items-center space-y-4">
                <p class="text-xs text-center text-gray-500 max-w-lg">
                    By clicking "Update", you agree to our Terms of Use and Privacy Policy.
                </p>
                <button type="submit" class="w-full md:w-auto bg-dark_green hover:bg-green-700 text-white font-bold py-3 px-12 rounded-xl shadow-lg transform transition hover:-translate-y-0.5 duration-200 text-lg">
                    Update Ad
                </button>
            </div>
        </div>

    </form>
</section>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trix@2.0.8/dist/trix.css">
<!-- Scripts maintained -->
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    window.MIN_IMAGES = {{ $minImages }};
    window.MAX_IMAGES = {{ $maxImages }};
</script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
<script src="{{ asset('frontend/js/lga.js') }}"></script>

{{-- Database-Driven Category UI Configuration (2026-01-31) --}}
<script src="{{ asset('dashboard/js/category-ui-manager.js') }}"></script>
<script src="{{ asset('dashboard/js/edit-ad-v2.js') }}"></script>
{{-- Old hardcoded version (kept for rollback): <script src="{{ asset('dashboard/js/edit-ad-Aa.js') }}"></script> --}}

<script src="{{ asset('dashboard/js/edit-sortable.js') }}"></script>
<script src="{{ asset('dashboard/js/word-count.js') }}"></script>

<script>
    // Pass PHP data to JavaScript
    window.advertData = {
        state: @json($advert->state ?? ''),
        lga: @json($advert->lga ?? '')
    };
</script>

<script>
    document.getElementById('advertForm').addEventListener('submit', function () {
        const btn = this.querySelector('button[type="submit"]');
        if (!btn) return;
        btn.disabled = true;
        btn.innerHTML =
            '<svg class="animate-spin inline w-5 h-5 mr-2 -mt-1" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">' +
            '<circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>' +
            '<path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>' +
            '</svg>Updating...';
    });
</script>

@include('user.layouts.footer')
