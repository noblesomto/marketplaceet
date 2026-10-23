@include('dashboard.layouts.header')
@include('dashboard.layouts.nav')
@include('dashboard.layouts.nav')
@include('dashboard.layouts.search')


<section class="w-full md:w-3/6 bg-white mx-auto p-3 text-sm mb-10">
    <div class="border-b-2 border-b-gray-200 pt-10 px-2 font-bold text-dark_green mb-2">
        Ad Details
        @include('frontend.components.flash-message')
        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-4 rounded mb-4">
                <p>There were some issues with your submission:</p>
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

    </div>

    <form action="/user/edit-ad/{{ $advert->id }}" id="advertForm" method="POST" role="form" class="" enctype="multipart/form-data">
             @csrf 
        <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
            <div class="col-span-10 lg:col-span-2">
                <div class="font-semibold">Bid/request</div>
            </div>
            <div class="col-span-10 lg:col-span-6 ">
                <div class="flex justify-start w-64">
                  <label class="flex items-center  w-full">
                    <input type="radio" {{ $advert->ad_type === 'Private' ? 'checked' : '' }} name="ad_type" value="Private" class="form-radio text-dark_green accent-dark_green"  required>
                    <span class="ml-2 ">I offer</span>
                  </label>

                  <label class="flex items-center w-64">
                    <input type="radio" {{ $advert->ad_type === 'Commercial' ? 'checked' : '' }} name="ad_type" value="Commercial" class="form-radio text-dark_green accent-dark_green" >
                    <span class="ml-2 ">I'm looking for</span>
                  </label>
                </div>
            </div>
      </div>


      <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
            <div class="col-span-10 md:col-span-2">
                <div class="font-semibold">Title</div>
            </div>
            <div class="col-span-10 md:col-span-5">
                @if ($errors->has('ad_title'))
                    <span class="text-red-400">{{ $errors->first('ad_title') }}</span>
                @endif
            <div class="relative">
                <input type="text"
                       name="ad_title"
                       id="ad_title"
                       placeholder="Ad Title"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       value="{{ $advert->ad_title }}"
                       maxlength="75"
                       required>
                <div class="text-sm text-gray-500 mt-1">
                    <span id="char-count">0</span>/75 characters
                </div>
            </div>
            </div>
            <div class="col-span-10 md:col-span-3">
                <div class="text-xs">
                   <span class="font-semibold"> Tip:</span> With a meaningful title, you sell better.
                  </div>
            </div>
       </div>



       <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
    <div class="col-span-10 md:col-span-2">
        <div class="font-semibold">Select Category</div>
    </div>
    <div class="col-span-10 md:col-span-8">
        <div class="grid grid-cols-6 gap-2">
            <!-- Category Dropdown -->
            <div class="col-span-6 md:col-span-2">
                <select id="category" name="category" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm bg-white" required
                        data-selected="{{ $advert->category }}">
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ $advert->category == $category->id ? 'selected' : '' }}>
                            {{ $category->category }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Subcategory Dropdown -->
            <div class="col-span-6 md:col-span-2">
                <select id="subcategory" name="subcategory" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm bg-white" required
                        data-selected="{{ $advert->sub_category }}">
                    <option value="">Select Subcategory</option>
                    @foreach($subcategories as $subcategory)
                        <option value="{{ $subcategory->id }}" {{ $advert->sub_category == $subcategory->id ? 'selected' : '' }}>
                            {{ $subcategory->sub_category }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Brand Dropdown -->
            <div class="col-span-6 md:col-span-2">
                <select id="brand" name="brand" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm bg-white" required
                        data-selected="{{ $advert->brand }}">
                    <option value="">Select Brand</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" {{ $advert->brand == $brand->id ? 'selected' : '' }}>
                            {{ $brand->brand }}
                        </option>
                    @endforeach
                </select>
            </div>


            <!-- Model Dropdown (conditionally shown) -->
            <div id="divModel" class="col-span-6 md:col-span-2 {{ in_array($advert->sub_category, [2]) ? '' : 'hidden' }}">
                <select id="model" name="model" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm bg-white"
        data-selected="{{ $advert->sub_category == 2 ? optional($advert->car)->model : ($advert->sub_category == 6 ? optional($advert->phone)->model : '') }}" >

                    <option value="">Select Model</option>
                    @foreach($models as $model)
                        <option value="{{ $model->id }}" 
                            {{
                                ($advert->sub_category == 2 && optional($advert->car)->model == $model->id) ||
                                ($advert->sub_category == 6 && optional($advert->phone)->model == $model->id)
                                    ? 'selected' : ''
                            }}
                        >
                            {{ $model->model }}
                        </option>

                    @endforeach
                </select>
            </div>

        </div>
    </div>
</div>



        <div id="itemCondition" class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200 {{ in_array($advert->sub_category, [2,6]) ? 'hidden' : '' }}">
        <div class="col-span-10 md:col-span-2">
            <div class="font-semibold">Item Condition *</div>
        </div>
        <div class="col-span-10 md:col-span-6">
            @if ($errors->has('item_condition'))
                <span class="text-red-400">{{ $errors->first('item_condition') }}</span>
            @endif
            <select id="pr" name="item_condition" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                <option value="">Please Choose</option>
                <option value="New" {{ $advert->item_condition == 'New' ? 'selected' : '' }}>New</option>
                <option value="Foreign Used" {{ $advert->item_condition == 'Foreign Used' ? 'selected' : '' }}>Foreign Used</option>
                <option value="Locally Used" {{ $advert->item_condition == 'Locally Used' ? 'selected' : '' }}>Locally Used</option>
            </select>
        </div>
    </div>

        <!-- Div 1 (Initially Hidden) -->
    <div id="divCar" class="p-2 {{ $advert->sub_category == 2 ? '' : 'hidden' }}">
        @if($advert->car)
        <div class="">

            <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Vehicle Condition <span class="text-red-500">*</span></div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('condition'))
                        <span class="text-red-400">{{ $errors->first('condition') }}</span>
                    @endif
                <select id="pr" name="condition" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="">Please Choose</option>
                    <option value="Local used" {{ $advert->car->condition == 'Local used' ? 'selected' : '' }}> Local used</option>
                    <option value="Foreign used" {{ $advert->car->condition == 'Foreign used' ? 'selected' : '' }}>Foreign used</option>
                    <option value="Brand new" {{ $advert->car->condition == 'Brand new' ? 'selected' : '' }}>Brand new</option>
                </select>
                </div>
           </div>

            <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Mileage </div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('mileage'))
                        <span class="text-red-400">{{ $errors->first('mileage') }}</span>
                    @endif
                    <div class="flex w-2/4">
                        <input type="text" name="mileage" placeholder="mileage" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ $advert->car->mileage }}">
                        <span class="mt-3">Km</span>
                    </div>
                </div>
           </div>



           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Registration <span class="text-red-500">*</span></div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('registration'))
                        <span class="text-red-400">{{ $errors->first('registration') }}</span>
                    @endif

                    <div class="flex w-2/4">
                        <select id="pr" name="registration" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                            <option value="">--Select Type--</option>
                            <option value="Registered" {{ $advert->car->registration == 'Registered' ? 'selected' : '' }}>Registered</option>
                            <option value="Unregistered" {{ $advert->car->registration == 'Unregistered' ? 'selected' : '' }}>Unregistered</option>
                        </select>

                    </div>
                </div>
           </div>

           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Fuel Type <span class="text-red-500">*</span></div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('fuel'))
                        <span class="text-red-400">{{ $errors->first('fuel') }}</span>
                    @endif
                <select id="pr" name="fuel" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="">Please Choose</option>
                    @foreach([
                        'Petrol',
                        'Diesel',
                        'Natural gas CNG',
                        'LPG',
                        'Hybrid',
                        'Electric'
                    ] as $fuelType)
                        <option 
                            value="{{ $fuelType }}" 
                            {{ $advert->car->fuel === $fuelType ? 'selected' : '' }}
                        >
                            {{ $fuelType }}
                        </option>
                    @endforeach
                </select>
                </div>
           </div>

           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Transmission <span class="text-red-500">*</span></div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('transmission'))
                        <span class="text-red-400">{{ $errors->first('transmission') }}</span>
                    @endif
                <select id="pr" name="transmission" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="">Please Choose</option>
                    <option value="Automatic" {{ $advert->car->transmission == 'Automatic' ? 'selected' : '' }}>Automatic</option>
                    <option value="Manually" {{ $advert->car->transmission == 'Manually' ? 'selected' : '' }}>Manually</option>
                    <option value="CVT" {{ $advert->car->transmission == 'CVT' ? 'selected' : '' }}>CVT</option>
                    <option value="AMT" {{ $advert->car->transmission == 'AMT' ? 'selected' : '' }}>AMT</option>
                    <option value="Other" {{ $advert->car->transmission == 'Other' ? 'selected' : '' }}>Other</option>
                </select>
                </div>
           </div>

           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Vehicle Type </div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('vehicle_type'))
                        <span class="text-red-400">{{ $errors->first('vehicle_type') }}</span>
                    @endif
                <select id="pr" name="vehicle_type" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="">Please Choose</option>
                    @foreach([
                        'Small Car',
                        'Station Wagon',
                        'Limousine',
                        'Convertible',
                        'SUV/Off Road Vehicle',
                        'Van/Bus',
                        'Coupe',
                        'Truck',
                        'Others'
                    ] as $vehicleType)
                        <option 
                            value="{{ $vehicleType }}"
                            {{ $advert->car->vehicle_type === $vehicleType ? 'selected' : '' }}
                        >
                            {{ $vehicleType }}
                        </option>
                    @endforeach
                </select>
                </div>
           </div>

            <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Exterior Color <span class="text-red-500">*</span></div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('exterior_color'))
                        <span class="text-red-400">{{ $errors->first('exterior_color') }}</span>
                    @endif
                <select id="exterior_color" name="exterior_color" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="">Choose Color</option>
                    @php
                        $carColors = [
                            // Basic colors
                            'Black' => 'Black',
                            'White' => 'White',
                            'Gray' => 'Gray',
                            'Silver' => 'Silver',
                            'Blue' => 'Blue',
                            'Red' => 'Red',
                            'Gold' => 'Gold',
                            'Green' => 'Green',

                            // Common extras
                            'Beige' => 'Beige',
                            'Brown' => 'Brown',
                            'Yellow' => 'Yellow',
                            'Orange' => 'Orange',
                            'Purple' => 'Purple',
                            'Maroon' => 'Maroon',
                            'Burgundy' => 'Burgundy',
                            'Bronze' => 'Bronze',
                            'Champagne' => 'Champagne',

                            // Premium & luxury shades
                            'Pearl White' => 'Pearl White',
                            'Gunmetal' => 'Gunmetal Gray',
                            'Midnight Blue' => 'Midnight Blue',
                            'Navy Blue' => 'Navy Blue',
                            'Olive Green' => 'Olive Green',
                            'Charcoal' => 'Charcoal',
                            'Matte Black' => 'Matte Black',

                            // Special finishes
                            'Two Tone' => 'Two-Tone',
                            'Gradient' => 'Gradient',
                            'Chameleon' => 'Chameleon',
                            'Custom Wrap' => 'Custom Wrap',
                            'Chrome' => 'Chrome',
                            'Camo' => 'Camouflage',
                            'Others' => 'Others',
                        ];
                    @endphp

                    @foreach($carColors as $value => $label)
                        <option value="{{ $value }}"
                                {{ optional($advert->car)->exterior_color === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                </div>
           </div>
           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Number of Doors </div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('doors'))
                        <span class="text-red-400">{{ $errors->first('doors') }}</span>
                    @endif
                <select id="pr" name="doors" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="">Please Choose</option>
                    <option value="1 Door" {{ $advert->car->doors == '1 Door' ? 'selected' : '' }}>1 Door</option>
                    <option value="2 Doors" {{ $advert->car->doors == '2 Doors' ? 'selected' : '' }}>2 Doors</option>
                    <option value="3 Doors" {{ $advert->car->doors == '3 Doors' ? 'selected' : '' }}>3 Doors</option>
                    <option value="4 Doors" {{ $advert->car->doors == '4 Doors' ? 'selected' : '' }}>4 Doors</option>
                </select>
                </div>
           </div>

           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Material Interior </div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('material_interior'))
                        <span class="text-red-400">{{ $errors->first('material_interior') }}</span>
                    @endif
                <select id="pr" name="material_interior" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="">Please Choose</option>
                    @php
                        $interiorMaterials = [
                            'Full Grain Leather',
                            'Partial Leather',
                            'Material',
                            'Velour', 
                            'Alcantara'
                        ];
                    @endphp
                    
                    @foreach($interiorMaterials as $material)
                        <option value="{{ $material }}" 
                            @if($advert->car->material_interior == $material) selected @endif>
                            {{ $material }}
                        </option>
                    @endforeach
                </select>
                </div>
           </div>

           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Exterior Equipment </div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    <div class="grid grid-cols-10">
                        <div class="col-span-5">
                            <div>
                                @php
                                    $exteriorEquipment = [
                                        'Trailer hitch',
                                        'Parking assistance',
                                        'Alloy wheels',
                                        'Xenon/LED headlights'
                                    ];
                                    $selectedEquipment = json_decode($advert->car->exterior_equipment) ?? [];
                                @endphp

                                @foreach(array_slice($exteriorEquipment, 0, 2) as $equipment)
                                    <div class="flex items-center">
                                        <input id="exterior-{{ Str::slug($equipment) }}" 
                                               type="checkbox" 
                                               name="exterior_equipment[]" 
                                               value="{{ $equipment }}" 
                                               class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                                               {{ in_array($equipment, $selectedEquipment) ? 'checked' : '' }}>
                                        <label for="exterior-{{ Str::slug($equipment) }}" class="ms-2 font-medium">
                                            {{ $equipment }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-span-5">
                            <div>
                                @foreach(array_slice($exteriorEquipment, 2) as $equipment)
                                    <div class="flex items-center">
                                        <input id="exterior-{{ Str::slug($equipment) }}" 
                                               type="checkbox" 
                                               name="exterior_equipment[]" 
                                               value="{{ $equipment }}" 
                                               class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                                               {{ in_array($equipment, $selectedEquipment) ? 'checked' : '' }}>
                                        <label for="exterior-{{ Str::slug($equipment) }}" class="ms-2 font-medium">
                                            {{ $equipment }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Interior Equipment </div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    <div class="grid grid-cols-10">
                        @php
                            $interiorEquipment = [
                                'Air conditioning',
                                'Navigation system',
                                'Radio/tuner',
                                'Bluetooth',
                                'Hands-free system',
                                'Sunroof/panoramic roof',
                                'Seat heating',
                                'Cruise control',
                                'Non-smoking vehicle'
                            ];
                            $selectedInterior = json_decode($advert->car->interior) ?? [];
                        @endphp
                        
                        <div class="col-span-5">
                            <div>
                                @foreach(array_slice($interiorEquipment, 0, 5) as $equipment)
                                    <div class="flex items-center">
                                        <input id="interior-{{ Str::slug($equipment) }}" 
                                               type="checkbox" 
                                               name="interior[]" 
                                               value="{{ $equipment }}" 
                                               class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                                               {{ in_array($equipment, $selectedInterior) ? 'checked' : '' }}>
                                        <label for="interior-{{ Str::slug($equipment) }}" class="ms-2 font-medium">
                                            {{ $equipment }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-span-5">
                            <div>
                                @foreach(array_slice($interiorEquipment, 5) as $equipment)
                                    <div class="flex items-center">
                                        <input id="interior-{{ Str::slug($equipment) }}" 
                                               type="checkbox" 
                                               name="interior[]" 
                                               value="{{ $equipment }}" 
                                               class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                                               {{ in_array($equipment, $selectedInterior) ? 'checked' : '' }}>
                                        <label for="interior-{{ Str::slug($equipment) }}" class="ms-2 font-medium">
                                            {{ $equipment }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Security </div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    <div class="grid grid-cols-10">
                        @php
                            $securityFeatures = [
                                'Anti-lock braking system (ABS)',
                                'Service history maintained',
                                'Electronic Stability Control',
                                'Airbags',
                                'Alarm system',
                                'Immobilizer',
                                'Traction control',
                                'ISOFIX child seat mounts'
                            ];
                            $selectedSecurity = json_decode($advert->car->security) ?? [];
                        @endphp
                        
                        <div class="col-span-5">
                            <div>
                                @foreach(array_slice($securityFeatures, 0, ceil(count($securityFeatures)/2)) as $feature)
                                    <div class="flex items-center">
                                        <input id="security-{{ Str::slug($feature) }}" 
                                               type="checkbox" 
                                               name="security[]" 
                                               value="{{ $feature }}" 
                                               class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                                               {{ in_array($feature, $selectedSecurity) ? 'checked' : '' }}>
                                        <label for="security-{{ Str::slug($feature) }}" class="ms-2 font-medium">
                                            {{ $feature }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-span-5">
                            <div>
                                @foreach(array_slice($securityFeatures, ceil(count($securityFeatures)/2)) as $feature)
                                    <div class="flex items-center">
                                        <input id="security-{{ Str::slug($feature) }}" 
                                               type="checkbox" 
                                               name="security[]" 
                                               value="{{ $feature }}" 
                                               class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                                               {{ in_array($feature, $selectedSecurity) ? 'checked' : '' }}>
                                        <label for="security-{{ Str::slug($feature) }}" class="ms-2 font-medium">
                                            {{ $feature }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        @endif
    </div>

    <!-- Div 2 (Initially Hidden) -->
    <div id="divPhone" class="mt-4 p-4 {{ $advert->sub_category == 6 ? '' : 'hidden' }}">
        @if($advert->phone)
        <div class="w-1/4">

            <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-4">
                    <div class="font-semibold">Phone Color *</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('phone_color'))
                        <span class="text-red-400">{{ $errors->first('phone_color') }}</span>
                    @endif
                <select id="Phonecolor" name="phone_color" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="">Choose Color</option>
                    @php
                        $phoneColors = [
                            // Basic colors
                            'Black' => 'Black',
                            'White' => 'White',
                            'Gray' => 'Gray',
                            'Silver' => 'Silver',
                            'Gold' => 'Gold',

                            // Standard vibrant colors
                            'Blue' => 'Blue',
                            'Red' => 'Red',
                            'Green' => 'Green',
                            'Yellow' => 'Yellow',
                            'Orange' => 'Orange',
                            'Purple' => 'Purple',
                            'Pink' => 'Pink',

                            // Premium shades
                            'Rose Gold' => 'Rose Gold',
                            'Bronze' => 'Bronze',
                            'Copper' => 'Copper',
                            'Midnight' => 'Midnight',
                            'Space Gray' => 'Space Gray',
                            'Midnight Green' => 'Midnight Green',

                            // Trendy / unique finishes
                            'Lavender' => 'Lavender',
                            'Aqua' => 'Aqua',
                            'Teal' => 'Teal',
                            'Turquoise' => 'Turquoise',
                            'Coral' => 'Coral',
                            'Champagne' => 'Champagne',
                            'Graphite' => 'Graphite',
                            'Starlight' => 'Starlight',
                            'Twilight' => 'Twilight',
                            'Gradient' => 'Gradient',
                            'Transparent' => 'Transparent',
                            'Others' => 'Others',
                        ];
                    @endphp

                    @foreach($phoneColors as $value => $label)
                        <option value="{{ $value }}"
                                {{ optional($advert->phone)->color === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                </div>
           </div>

            <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-4">
                    <div class="font-semibold">Device *</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('device'))
                        <span class="text-red-400">{{ $errors->first('device') }}</span>
                    @endif
                <select id="pr" name="device" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="">Please Choose</option>
                    @php
                        $deviceTypes = [
                            'Device' => 'Device',
                            'Accessories' => 'Accessories',
                            'Device & Accessories' => 'Device & Accessories'
                        ];
                    @endphp
                    
                    @foreach($deviceTypes as $value => $label)
                        <option value="{{ $value }}" 
                                {{ $advert->phone->device == $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                </div>
           </div>

           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-4">
                    <div class="font-semibold">Condition *</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('phone_condition'))
                        <span class="text-red-400">{{ $errors->first('phone_condition') }}</span>
                    @endif
                <select id="pr" name="phone_condition" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="">Please Choose</option>
                    @php
                        $phoneConditions = [
                            'New - Unboxed' => 'New <span class="text-xs">(New and Unboxed)</span>',
                            'Foreign Used - No Packaging' => 'Foreign Used <span class="text-xs">(Without original packaging)</span>',
                            'Used - Very Good' => 'Very Good (Well-maintained item with barely visible signs of wear)',
                            'Used - Good' => 'Good (Used item with visible signs of wear)',
                            'Used - In Order' => 'In Order (Used item with clearly visible signs of wear, but still usable)',
                            'Used -Defect' => 'Defect (Defective item suitable for repair or spare parts)'
                        ];
                    @endphp
                    
                    @foreach($phoneConditions as $value => $label)
                        <option value="{{ $value }}" 
                                {{ $advert->phone->condition == $value ? 'selected' : '' }}
                                {!! $value !== 'New - Unboxed' ? 'data-description="'.htmlspecialchars($label).'"' : '' !!}>
                            {!! $label !!}
                        </option>
                    @endforeach
                </select>
                </div>
             
           </div>


        </div>
        @endif
    </div>

    <!-- Shippment -->
    <div id="shipment" class="">
         <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
            <div class="col-span-10 lg:col-span-2">
                <div class="font-semibold">Shipment</div>
            </div>
            <div class="col-span-10 lg:col-span-5 ">
                <div class="flex justify-start ">
                  <label class="flex items-center  w-full">
                    <input type="radio" {{ $advert->shipment === 'Ship' ? 'checked' : '' }} name="shipment" value="Ship" class="form-radio text-dark_green accent-dark_green" onclick="toggleDiv()">
                    <span class="ml-2 ">Shipping Possible</span>
                  </label>

                  <label class="flex items-center w-64">
                    <input {{ $advert->shipment === 'Pickup' ? 'checked' : '' }} type="radio" name="shipment" value="Pickup" class="form-radio text-dark_green accent-dark_green" onclick="toggleDiv()">
                    <span class="ml-2 ">Only Pickup</span>
                  </label>
                </div>
            </div>
      </div>

       <!-- Shipping Methods (Hidden by default) -->
        <div id="shipping" class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200 ">
            <div class="col-span-10 lg:col-span-2">
                <div class="font-semibold">Shipping Method</div>
            </div>
            <div class="col-span-10 lg:col-span-5">
                <div id="shipping-methods">
                    @foreach($shippings as $row)
                    <div class="flex border border-dark_green rounded-lg p-2 mt-2">
                        <input
                            id="shipping-{{ $row->id }}"
                            name="shipping[]"
                            type="checkbox"
                            value="{{ $row->id }}"
                            class="w-6 h-6 text-dark_green bg-gray-100 rounded border-dark_green focus:ring-dark_green accent-primary"
                        >
                        <label for="shipping-{{ $row->id }}" class="ml-2 text-sm font-medium flex flex-col">
                            <div class="flex items-center">
                                <span><img class="w-10" src="{{ asset('uploads/shipping/'.$row->logo) }}"></span>
                                <span class="ml-2 font-bold">{{ $row->company }}</span>
                            </div>
                            <p>Max. {{ $row->weight }} kg, {{ $row->description }}</p>
                        </label>
                    </div>
                    @endforeach
                </div>
                <!-- Error message (hidden by default) -->
                <p id="shipping-error" class="mt-2 text-red-500 hidden">Please select at least one shipping method.</p>
            </div>
        </div>
    </div>


       <div id="price" class="grid grid-cols-10 gap-2 md:gap-5 py-3 border-b border-b-gray-200">
            <div class="col-span-10 md:col-span-2">
                <div class="font-semibold">Price</div>
            </div>
            <div class="col-span-10 md:col-span-3">
               
            <div class="flex justify-start items-center">
                <div class="">
                    @if ($errors->has('price'))
                        <span class="text-red-400">{{ $errors->first('price') }}</span>
                    @endif
                    <input
                        type="text"
                        name="price_display"
                        id="price_display"
                        placeholder="0"
                        class="w-36 px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        value="{{ old('price') ? number_format(old('price'), 0, '.', ',') : number_format($advert->price, 0, '.', ',') }}"
                    >
                    <input type="hidden" name="price" id="price_hidden" value="{{ old('price', $advert->price) }}">
                </div>
                <div class="text-base ml-2">
                    Birr
                </div>
            </div>
            </div>
            <div id="services" class="col-span-10 md:col-span-2 mt-1">
                <label class="text-base flex items-center gap-1">
                    <input
                        type="hidden"
                        name="contact_price"
                        value="no"
                    >
                    <input
                        type="checkbox"
                        class="default:ring-2 w-6 h-6"
                        name="contact_price"
                        value="yes"
                        {{ $advert->contact_price == 'yes' ? 'checked' : '' }}
                    >
                    Contact For Price
                </label>
            </div>
            <div class="col-span-10 md:col-span-3">
                <select id="price_type" name="price_type" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="Fixed" {{ ($advert->price_type =='Fixed') ? "selected" : ""; }}>Fixed Price</option>
                    <option value="Negotiable" {{ ($advert->price_type =='Negotiable') ? "selected" : ""; }}>Negotiable</option>
                    <option value="Give Away" {{ ($advert->price_type =='Give Away') ? "selected" : ""; }}>Give Away</option>
                </select>
            </div>
       </div>

       <div id="salary" class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
    <div class="col-span-10 md:col-span-2">
        <div class="font-semibold">Salary</div>
    </div>
    <div class="col-span-10 md:col-span-5">
        @if ($errors->has('salary'))
            <span class="text-red-400">{{ $errors->first('salary') }}</span>
        @endif
        <select id="salary" name="salary" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
            <option value="">--Select Salary--</option>
            <option value="Commission" {{ $advert->salary == 'Commission' ? 'selected' : '' }}>Commission</option>
            <option value="Below Br 20,000" {{ $advert->salary == 'Below Br 20,000' ? 'selected' : '' }}>Below Br 20,000</option>
            <option value="Br 20,000 - Br 40,000" {{ $advert->salary == 'Br 20,000 - Br 40,000' ? 'selected' : '' }}>Br 20,000 - Br 40,000</option>
            <option value="Br 40,000 - Br 60,000" {{ $advert->salary == 'Br 40,000 - Br 60,000' ? 'selected' : '' }}>Br 40,000 - Br 60,000</option>
            <option value="Br 60,000 - Br 80,000" {{ $advert->salary == 'Br 60,000 - Br 80,000' ? 'selected' : '' }}>Br 60,000 - Br 80,000</option>
            <option value="Br 80,000 - Br 100,000" {{ $advert->salary == 'Br 80,000 - Br 100,000' ? 'selected' : '' }}>Br 80,000 - Br 100,000</option>
            <option value="Br 100,000 - Br 120,000" {{ $advert->salary == 'Br 100,000 - Br 120,000' ? 'selected' : '' }}>Br 100,000 - Br 120,000</option>
            <option value="Br 120,000 - Br 140,000" {{ $advert->salary == 'Br 120,000 - Br 140,000' ? 'selected' : '' }}>Br 120,000 - Br 140,000</option>
            <option value="Br 140,000 - Br 160,000" {{ $advert->salary == 'Br 140,000 - Br 160,000' ? 'selected' : '' }}>Br 140,000 - Br 160,000</option>
            <option value="Br 160,000 - Br 180,000" {{ $advert->salary == 'Br 160,000 - Br 180,000' ? 'selected' : '' }}>Br 160,000 - Br 180,000</option>
            <option value="Br 180,000 - Br 200,000" {{ $advert->salary == 'Br 180,000 - Br 200,000' ? 'selected' : '' }}>Br 180,000 - Br 200,000</option>
            <option value="Br 200,000 - Br 220,000" {{ $advert->salary == 'Br 200,000 - Br 220,000' ? 'selected' : '' }}>Br 200,000 - Br 220,000</option>
            <option value="Br 220,000 - Br 250,000" {{ $advert->salary == 'Br 220,000 - Br 250,000' ? 'selected' : '' }}>Br 220,000 - Br 250,000</option>
            <option value="Br 250,000 - Br 300,000" {{ $advert->salary == 'Br 250,000 - Br 300,000' ? 'selected' : '' }}>Br 250,000 - Br 300,000</option>
            <option value="Br 300,000 - Br 350,000" {{ $advert->salary == 'Br 300,000 - Br 350,000' ? 'selected' : '' }}>Br 300,000 - Br 350,000</option>
            <option value="Br 350,000 - Br 400,000" {{ $advert->salary == 'Br 350,000 - Br 400,000' ? 'selected' : '' }}>Br 350,000 - Br 400,000</option>
            <option value="Br 400,000 - Br 450,000" {{ $advert->salary == 'Br 400,000 - Br 450,000' ? 'selected' : '' }}>Br 400,000 - Br 450,000</option>
            <option value="Br 450,000 - Br 500,000" {{ $advert->salary == 'Br 450,000 - Br 500,000' ? 'selected' : '' }}>Br 450,000 - Br 500,000</option>
            <option value="Above Br 500,000" {{ $advert->salary == 'Above Br 500,000' ? 'selected' : '' }}>Above Br 500,000</option>
        </select>
    </div>
    <div class="col-span-10 md:col-span-3">
    </div>
</div>

       <div id="expectedSalary" class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
    <div class="col-span-10 md:col-span-2">
        <div class="font-semibold">Expected Salary</div>
    </div>
    <div class="col-span-10 md:col-span-5">
        @if ($errors->has('expected_salary'))
            <span class="text-red-400">{{ $errors->first('expected_salary') }}</span>
        @endif
        <select id="expected_salary" name="expected_salary" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
            <option value="">--Select Expected Salary--</option>
            <option value="Below Br 50,000" {{ $advert->expected_salary == 'Below Br 50,000' ? 'selected' : '' }}>Below Br 50,000</option>
            <option value="Br 50,000 - Br 75,000" {{ $advert->expected_salary == 'Br 50,000 - Br 75,000' ? 'selected' : '' }}>Br 50,000 - Br 75,000</option>
            <option value="Br 75,000 - Br 100,000" {{ $advert->expected_salary == 'Br 75,000 - Br 100,000' ? 'selected' : '' }}>Br 75,000 - Br 100,000</option>
            <option value="Br 100,000 - Br 120,000" {{ $advert->expected_salary == 'Br 100,000 - Br 120,000' ? 'selected' : '' }}>Br 100,000 - Br 120,000</option>
            <option value="Br 120,000 - Br 140,000" {{ $advert->expected_salary == 'Br 120,000 - Br 140,000' ? 'selected' : '' }}>Br 120,000 - Br 140,000</option>
            <option value="Br 140,000 - Br 160,000" {{ $advert->expected_salary == 'Br 140,000 - Br 160,000' ? 'selected' : '' }}>Br 140,000 - Br 160,000</option>
            <option value="Br 160,000 - Br 180,000" {{ $advert->expected_salary == 'Br 160,000 - Br 180,000' ? 'selected' : '' }}>Br 160,000 - Br 180,000</option>
            <option value="Br 180,000 - Br 200,000" {{ $advert->expected_salary == 'Br 180,000 - Br 200,000' ? 'selected' : '' }}>Br 180,000 - Br 200,000</option>
            <option value="Br 200,000 - Br 220,000" {{ $advert->expected_salary == 'Br 200,000 - Br 220,000' ? 'selected' : '' }}>Br 200,000 - Br 220,000</option>
            <option value="Br 220,000 - Br 250,000" {{ $advert->expected_salary == 'Br 220,000 - Br 250,000' ? 'selected' : '' }}>Br 220,000 - Br 250,000</option>
            <option value="Br 250,000 - Br 300,000" {{ $advert->expected_salary == 'Br 250,000 - Br 300,000' ? 'selected' : '' }}>Br 250,000 - Br 300,000</option>
            <option value="Br 300,000 - Br 350,000" {{ $advert->expected_salary == 'Br 300,000 - Br 350,000' ? 'selected' : '' }}>Br 300,000 - Br 350,000</option>
            <option value="Br 350,000 - Br 400,000" {{ $advert->expected_salary == 'Br 350,000 - Br 400,000' ? 'selected' : '' }}>Br 350,000 - Br 400,000</option>
            <option value="Br 400,000 - Br 450,000" {{ $advert->expected_salary == 'Br 400,000 - Br 450,000' ? 'selected' : '' }}>Br 400,000 - Br 450,000</option>
            <option value="Br 450,000 - Br 500,000" {{ $advert->expected_salary == 'Br 450,000 - Br 500,000' ? 'selected' : '' }}>Br 450,000 - Br 500,000</option>
            <option value="Above Br 500,000" {{ $advert->expected_salary == 'Above Br 500,000' ? 'selected' : '' }}>Above Br 500,000</option>
        </select>
    </div>
    <div class="col-span-10 md:col-span-3">
    </div>
</div>

       @if($user->acc_type=="Commercial")
       <div id="quantity" class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
            <div class="col-span-10 md:col-span-2">
                <div class="font-semibold">Item Quantity</div>
            </div>
            <div class="col-span-10 md:col-span-5">
                @if ($errors->has('quantity'))
                    <span class="text-red-400">{{ $errors->first('quantity') }}</span>
                @endif
            <input type="number" id="name" name="quantity" placeholder="Item Quantity" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ $advert->quantity }}" min="1" max="100">
            </div>
            <div class="col-span-10 md:col-span-3">
                
            </div>
       </div>
       @endif

        <div id="buyDirect" class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200 {{ in_array($advert->sub_category, [2]) ? 'hidden' : '' }}">
            <div class="col-span-10 lg:col-span-2">
                <div class="font-semibold">Payment option</div>
            </div>
            <div class="col-span-10 lg:col-span-6 ">
                <div class="flex flex-col text-sm">
                  <label class="flex items-center  w-full">
                    <input type="radio" {{ $advert->buy_direct === 'Yes' ? 'checked' : '' }} name="buy_direct" value="Yes" class="form-radio text-dark_green accent-dark_green"  required>
                    <span class="ml-2 ">Yes, I would like to sell this item using the free ‘Buy Direct’ payment option</span>
                    
                  </label>
                  <div class="my-2 w-full border border-gray-300 p-2 rounded-lg text-sm">
                        <div class="flex">
                            <span class="mr-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                                </svg>
                            </span>
                            <span>Selecting this option allows buyers to purchase your item using ‘Buy Direct.’ <br><a class="font-semibold text-dartk_green" href="">Learn More</a> </span>
                        </div>
                        <div class="flex text-xs mt-1">
                            <span class="mr-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </span>
                            <span><strong class="font-semibold">No negotiation </strong> — buyers pay the price you set. </span>
                        </div>
                        <div class="flex text-xs mt-1">
                            <span class="mr-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            </span>
                            <span>Our buyer protection provides security, creates trust and <strong class="font-semibold">increases your chances of selling.</strong> </span>
                        </div>
                    </div>

                  <label class="flex items-center w-64">
                    <input {{ $advert->buy_direct === 'No' ? 'checked' : '' }} type="radio" name="buy_direct" value="No" class="form-radio text-dark_green accent-dark_green" >
                    <span class="ml-2 ">No, do not use “Buy direct”</span>
                  </label>
                </div>
            </div>
      </div>


       <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
            <div class="col-span-10 md:col-span-2">
                <div class="font-semibold">Description</div>
            </div>
            <div class="col-span-10 md:col-span-7">
                @if ($errors->has('description'))
                    <span class="text-red-400">{{ $errors->first('description') }}</span>
                @endif
                <input id="content" type="hidden" name="description" value="{{ old('description', $advert->description ?? '') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
                <trix-editor input="content" style="min-height: 80px;"></trix-editor>
                <div class="text-sm text-gray-500 mt-1">
                    <span id="word-count">0</span>/3500 characters
                </div>
            </div>
        </div>
   


    <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
        <div class="col-span-10 md:col-span-2">
            <div class="font-semibold">Pictures (recommended)</div>
        </div>
        <div class="col-span-10 md:col-span-5">
            @if ($errors->has('images[]'))
                <span class="text-red-400">{{ $errors->first('images[]') }}</span>
            @endif
            <div class="flex justify-start border-dashed border-2 border-gray-300">
                <!-- Camera Icon for File Upload -->
                <div class="flex items-center">
                    <label for="imageUpload" class="cursor-pointer">
                        <div class="flex items-center justify-center px-3 py-1 m-2 text-gray-700 border border-gray-300 hover:bg-primary transition">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                              <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                            </svg>
                        </div>
                        <input name="images[]" type="file" id="imageUpload" multiple class="hidden" accept="image/*">
                        <input type="hidden" name="existing_image_order" id="existing_image_order">

                    </label>
                </div>

                <!-- Image Preview Container -->
                <div id="preview" class="grid grid-cols-4 md:grid-cols-4 gap-4">
                    <!-- Display existing images -->

                   @foreach($advert->getMedia('images') as $media)
                        <div class="relative group cursor-move image-container" draggable="true" data-id="{{ $media->id }}">
                            <img src="{{ $media->getUrl('thumbnail') }}" class="w-full h-auto rounded-lg shadow">
                            <button type="button" class="absolute top-0 right-0 w-6 h-6 text-red-500 bg-white rounded-full hover:bg-red-100 delete-image flex items-center justify-center" data-id="{{ $media->id }}">&times;</button>
                            <input type="hidden" name="existing_images[]" value="{{ $media->id }}">
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="text-xs flex justify-start items-center">
                <img src="{{ asset('frontend/images/swap.png') }}" class="h-6 mx-2">
               Move to Change the Order
              </div>
        </div>
        <div class="col-span-10 md:col-span-3">
            <div class="text-xs">
               <span class="font-semibold"> Tip:</span>  Up to 20 images with a maximum size of 12 MB. To keep listings clear, please avoid uploading images with watermarks or text.
              </div>
        </div>
    </div>
    <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
    <div class="col-span-10 md:col-span-2">
        <div class="font-semibold">Location</div>
    </div>
    <div class="col-span-10 md:col-span-3">
        @if ($errors->has('state'))
            <span class="text-danger">{{ $errors->first('state') }}</span>
        @endif
        <select onchange="toggleLGA(this);" name="state" id="state" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
            <option value="" selected="selected">- Select State -</option>
            @foreach($states as $state)
            <option value="{{ $state->name }}" {{ ($advert->state == $state->name) ? "selected" : "" }}>{{ $state->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-span-10 md:col-span-3">
        @if ($errors->has('lga'))
            <span class="text-red-400">{{ $errors->first('lga') }}</span>
        @endif
        <select name="lga" id="lga" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white select-lga" required>
            @if($advert->state && $advert->lga)
                <option value="{{ $advert->lga }}" selected>{{ $advert->lga }}</option>
            @endif
        </select>
    </div>
</div>



    <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
            <div class="col-span-10 md:col-span-2">
                <div class="font-semibold">Name</div>
            </div>
            <div class="col-span-10 md:col-span-5">
                @if ($errors->has('name'))
                <span class="text-danger">{{ $errors->first('name') }}</span>
            @endif
            <input type="text" id="name" name="name" placeholder="Profile Name" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ $user->name }}" readonly>
            </div>
            <div class="col-span-10 md:col-span-3">
                <div class="text-xs">
                   <span class="font-semibold"> Tip:</span> Enter your name so that you increase the reliability of your ad.
                  </div>
            </div>
       </div>

       <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
            <div class="col-span-10 md:col-span-2">
                <div class="font-semibold">Show Contact?</div>
            </div>
            <div class="col-span-10 md:col-span-5">
                @if ($errors->has('show_contact'))
                <span class="text-danger">{{ $errors->first('show_contact') }}</span>
            @endif
            <select name="show_contact" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                <option value="Yes" {{ $advert->show_contact == 'Yes' ? 'selected' : '' }}>Yes</option>
                <option value="No" {{ $advert->show_contact == 'No' ? 'selected' : '' }}>No</option>
            </select>
            </div>
            <div class="col-span-10 md:col-span-3">
                <div class="text-xs">
                   <span class="font-semibold"> Tip:</span>Do you want users to see your contact details on the advert page?
                  </div>
            </div>
       </div>

       <div class="mt-5 py-3 border-b border-b-gray-200">
           <div class="font-semibold">Publish your ad</div>        
       </div>

       <div class="text-xs my-3">
           Our terms of use apply. Information about processing You can find your data in our privacy policy.
       </div>

       <div class="flex justify-start mb-10">
            <button type="submit" class="btn btn-secondary py-2 px-6">Update</button>
       </div>


    </form>
</section>


<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="{{ asset('frontend/js/lga.js') }}"></script>
<script src="{{ asset('dashboard/js/edit-ad-Aa.js') }}"></script>
<script src="{{ asset('dashboard/js/edit-sortable.js') }}"></script>
<script src="{{ asset('dashboard/js/word-count.js') }}"></script>

<script>
    // Pass PHP data to JavaScript
    window.advertData = {
        state: @json($advert->state ?? ''),
        lga: @json($advert->lga ?? '')
    };
</script>

@include('dashboard.layouts.footer')
