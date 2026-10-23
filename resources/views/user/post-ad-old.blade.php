@include('user.layouts.header')
@include('user.layouts.nav')
@include('user.layouts.back-nav')
@include('user.layouts.search')

<section class="w-full md:w-3/6 bg-white mx-auto p-3 text-sm mb-10">
    <div class="border-b-2 border-b-gray-200 pt-10 px-2 font-bold text-dark_green mb-2">
        Ad Details

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

    <form method="POST" action="/user/post-ad" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
            <div class="col-span-10 lg:col-span-2">
                <div class="font-semibold">Bid/request</div>
            </div>
            <div class="col-span-10 lg:col-span-6 ">
                <div class="flex justify-start w-64">
                  <label class="flex items-center  w-full">
                    <input type="radio" name="ad_type" value="Private" class="form-radio text-dark_green accent-dark_green"  required>
                    <span class="ml-2 ">I offer</span>
                  </label>

                  <label class="flex items-center w-64">
                    <input type="radio" name="ad_type" value="Commercial" class="form-radio text-dark_green accent-dark_green" >
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
                       value="{{ old('ad_title') }}"
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
                <div class="font-semibold">Select Category </div>
            </div>
            <div class="col-span-10 md:col-span-8">

                <div class="grid grid-cols-6 gap-2 space-y-3">
                    <div class="col-span-6 md:col-span-2">
                        @if ($errors->has('category'))
                            <span class="text-red-400">{{ $errors->first('category') }}</span>
                        @endif
                        <label for="category" class="block text-gray-700">Category:</label>
                        <select id="category" name="category" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm bg-white" required>
                            <option value="">Category</option>
                           @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->category }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-6 md:col-span-2">
                        @if ($errors->has('subcategory'))
                            <span class="text-red-400">{{ $errors->first('subcategory') }}</span>
                        @endif
                        <label for="subcategory" class="block text-gray-700" onchange="showHideDiv()">Sub Category:</label>
                        <select id="subcategory" name="subcategory" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm bg-white" required>
                            <option value="" >Select Subcategory</option>
                        </select>
                    </div>

                     <div class="col-span-6 md:col-span-2">
                        @if ($errors->has('brand'))
                            <span class="text-red-400">{{ $errors->first('brand') }}</span>
                        @endif
                        <label for="brand" class="block text-gray-700">Select Brand:</label>
                        <select id="brand" name="brand" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm bg-white" required>
                            <option value="">Select Options</option>
                        </select>
                    </div>

                    <div id="divModel" class="col-span-6 md:col-span-2 hidden">
                        @if ($errors->has('model'))
                            <span class="text-red-400">{{ $errors->first('model') }}</span>
                        @endif
                        <label for="model" class="block text-gray-700">Select Model:</label>
                        <select id="model" name="model" class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                            <option value="">Select Model</option>
                        </select>
                    </div>


                </div>
            </div>
    
       </div>

       <div id="itemCondition" class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
            <div class="col-span-10 md:col-span-2">
                <div class="font-semibold">Item Condition *</div>
            </div>
            <div class="col-span-10 md:col-span-6">
                @if ($errors->has('item_condition'))
                    <span class="text-red-400">{{ $errors->first('item_condition') }}</span>
                @endif
            <select id="pr" name="item_condition" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                <option value="">Please Choose</option>
                <option value="New">New</option>
                <option value="Foreign Used">Foreign Used</option>
                <option value="Locally Used">Locally Used</option>
            </select>
            </div>
       </div>

        <!-- Div 1 (Initially Hidden) -->
    <div id="divCar" class="p-2 hidden">
        <div class="">
            <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Mileage *</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('mileage'))
                        <span class="text-red-400">{{ $errors->first('mileage') }}</span>
                    @endif
                    <div class="flex w-2/4">
                        <input type="text" name="mileage" placeholder="mileage" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ old('mileage') }}">
                        <span class="mt-3">Km</span>
                    </div>
                </div>
           </div>

            <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Vehicle Condition *</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('condition'))
                        <span class="text-red-400">{{ $errors->first('condition') }}</span>
                    @endif
                <select id="pr" name="condition" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="">Please Choose</option>
                    <option value="Local used">Local used</option>
                    <option value="Foreign used">Foreign used</option>
                    <option value="Brand new">Brand new</option>
                </select>
                </div>
           </div>

           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Registration</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('registration'))
                        <span class="text-red-400">{{ $errors->first('registration') }}</span>
                    @endif

                    <div class="flex w-2/4">
                        <select id="pr" name="registration" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                            <option value="">--Select Type--</option>
                            <option value="Registered">Registered</option>
                            <option value="Unregistered">Unregistered</option>
                        </select>

                    </div>
                </div>
           </div>

           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Fuel Type *</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('fuel'))
                        <span class="text-red-400">{{ $errors->first('fuel') }}</span>
                    @endif
                <select id="pr" name="fuel" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="">Please Choose</option>
                    <option value="Petrol">Petrol</option>
                    <option value="Diesel">Diesel</option>
                    <option value="Natural gas CNG">Natural gas CNG</option>
                    <option value="LPG">LPG</option>
                    <option value="Hybrid">Hybrid</option>
                    <option value="Electric">Electric</option>
                </select>
                </div>
           </div>

           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Transmission *</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('transmission'))
                        <span class="text-red-400">{{ $errors->first('transmission') }}</span>
                    @endif
                <select id="pr" name="transmission" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="">Please Choose</option>
                    <option value="Automatic">Automatic</option>
                    <option value="Manual">Manual</option>
                </select>
                </div>
           </div>

           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Vehicle Type *</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('vehicle_type'))
                        <span class="text-red-400">{{ $errors->first('vehicle_type') }}</span>
                    @endif
                <select id="pr" name="vehicle_type" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="">Please Choose</option>
                    <option value="Small Car">Small Car</option>
                    <option value="Station Wagon">Station Wagon</option>
                    <option value="Limousine">Limousine</option>
                    <option value="Convertible">Convertible</option>
                    <option value="SUV/Off Road Vehicle">SUV/Off Road Vehicle</option>
                    <option value="Van/Bus">Van/Bus</option>
                    <option value="Coupe">Coupe</option>
                    <option value="Truck">Truck</option>
                    <option value="Others">Others</option>
                </select>
                </div>
           </div>

            <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Exterior Color</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('exterior_color'))
                        <span class="text-red-400">{{ $errors->first('exterior_color') }}</span>
                    @endif
                <select id="Carcolor" name="exterior_color" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
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
                        <option value="{{ $value }}">
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                </div>
           </div>
           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Number of Doors *</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('doors'))
                        <span class="text-red-400">{{ $errors->first('doors') }}</span>
                    @endif
                <select id="pr" name="doors" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="">Please Choose</option>
                    <option value="1 Door">1 Door</option>
                    <option value="2 Doors">2 Doors</option>
                    <option value="3 Doors">3 Doors</option>
                    <option value="4 Doors">4 Doors</option>
                </select>
                </div>
           </div>

           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Material Interior</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    @if ($errors->has('material_interior'))
                        <span class="text-red-400">{{ $errors->first('material_interior') }}</span>
                    @endif
                <select id="pr" name="material_interior" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="">Please Choose</option>
                    <option value="Full Grain Leather">Full Grain Leather</option>
                    <option value="Partial Leather">Partial Leather</option>
                    <option value="Material">Material</option>
                    <option value="Velour">Velour</option>
                    <option value="Alcantara">Alcantara</option>
                </select>
                </div>
           </div>
           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Exterior Equipment</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    <div class="grid grid-cols-10">
                        <div class="col-span-5">
                            <div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="exterior_equipment[]" value="Trailer hitch" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Trailer hitch</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="exterior_equipment[]" value="Parking assistance" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Parking assistance</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-5">
                            <div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="exterior_equipment[]" value="Alloy wheels" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Alloy wheels</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="exterior_equipment[]" value="Xenon/LED headlights" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Xenon/LED headlights</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
           </div>
           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Interior Equipment</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    <div class="grid grid-cols-10">
                        <div class="col-span-5">
                            <div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="interior[]" value="Air conditioning" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Air conditioning</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="interior[]" value="Navigation system" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Navigation system</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="interior[]" value="Radio/tuner" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Radio/tuner</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="interior[]" value="Bluetooth" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Bluetooth</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="interior[]" value="Hands-free system" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Hands-free system</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-5">
                            <div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="interior[]" value="Sunroof/panoramic roof" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Sunroof/panoramic roof</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="interior[]" value="Seat heating" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Seat heating</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="interior[]" value="Cruise control" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Cruise control</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="interior[]" value="Non-smoking vehicle" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Non-smoking vehicle</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
           </div>
           <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
                <div class="col-span-10 md:col-span-2">
                    <div class="font-semibold">Security</div>
                </div>
                <div class="col-span-10 md:col-span-6">
                    <div class="grid grid-cols-10">
                        <div class="col-span-5">
                            <div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="security[]" value="Anti-lock braking system (ABS)" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Anti-lock braking system (ABS)</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-span-5">
                            <div>
                                <div class="flex items-center">
                                    <input id="link-checkbox" type="checkbox" name="security[]" value="Service history maintained" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                    <label for="link-checkbox" class="ms-2 font-medium ">Service history maintained</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
           </div>

        </div>
    </div>

    <!-- Div 2 (Initially Hidden) -->
<div id="divPhone" class="mt-6 hidden">
    <div class="bg-white space-y-3">

        <!-- Phone Color -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center border-b pb-5">
            <label class="font-semibold text-gray-700">
                Phone Color <span class="text-red-500">*</span>
            </label>

            <div class="md:col-span-2">
                @if ($errors->has('phone_color'))
                    <p class="text-sm text-red-500 mb-1">
                        {{ $errors->first('phone_color') }}
                    </p>
                @endif

                <select
                    name="phone_color"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-white shadow-sm
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Choose Color</option>

                    <!-- Basic -->
                    <option>Black</option>
                    <option>White</option>
                    <option>Gray</option>
                    <option>Silver</option>
                    <option>Gold</option>

                    <!-- Vibrant -->
                    <option>Blue</option>
                    <option>Red</option>
                    <option>Green</option>
                    <option>Yellow</option>
                    <option>Orange</option>
                    <option>Purple</option>
                    <option>Pink</option>

                    <!-- Premium -->
                    <option>Rose Gold</option>
                    <option>Bronze</option>
                    <option>Copper</option>
                    <option>Midnight</option>
                    <option>Space Gray</option>
                    <option>Midnight Green</option>

                    <!-- Unique -->
                    <option>Lavender</option>
                    <option>Aqua</option>
                    <option>Teal</option>
                    <option>Turquoise</option>
                    <option>Coral</option>
                    <option>Champagne</option>
                    <option>Graphite</option>
                    <option>Starlight</option>
                    <option>Twilight</option>
                    <option>Gradient</option>
                    <option>Transparent</option>
                    <option>Others</option>
                </select>
            </div>
        </div>

        <!-- Device -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center border-b pb-5">
            <label class="font-semibold text-gray-700">
                Device <span class="text-red-500">*</span>
            </label>

            <div class="md:col-span-2">
                @if ($errors->has('device'))
                    <p class="text-sm text-red-500 mb-1">
                        {{ $errors->first('device') }}
                    </p>
                @endif

                <select
                    name="device"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-white shadow-sm
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Please Choose</option>
                    <option value="Device">Device</option>
                    <option value="Accessories">Accessories</option>
                    <option value="Device & Accessories">Device & Accessories</option>
                </select>
            </div>
        </div>

        <!-- Condition -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-center">
            <label class="font-semibold text-gray-700">
                Condition <span class="text-red-500">*</span>
            </label>

            <div class="md:col-span-2">
                @if ($errors->has('phone_condition'))
                    <p class="text-sm text-red-500 mb-1">
                        {{ $errors->first('phone_condition') }}
                    </p>
                @endif

                <select
                    name="phone_condition"
                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg bg-white shadow-sm
                           focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">Please Choose</option>
                    <option value="New - Unboxed">New (Unboxed)</option>
                    <option value="Foreign Used - No Packaging">Foreign Used (No Packaging)</option>
                    <option value="Used - Very Good">Very Good</option>
                    <option value="Used - Good">Good</option>
                    <option value="Used - In Order">In Order</option>
                    <option value="Used - Defect">Defect</option>
                </select>
            </div>
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
                        value="{{ old('price') ? number_format(old('price'), 0, '.', ',') : '' }}"
                    >
                    <input type="hidden" name="price" id="price_hidden" value="{{ old('price') }}">
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

                    >
                    Contact For Price
                </label>
            </div>
            <div class="col-span-10 md:col-span-3">
                <select id="price" name="price_type" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                    <option value="Fixed">Fixed Price</option>
                    <option value="Negotiable">Negotiable</option>
                    <option value="Give Away">Give Away</option>
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
                <option value="Commission">Commission</option>
                <option value="Below Br 2,000">Below Br 2,000</option>
                <option value="Br 2,000 - Br 4,000">Br 2,000 - Br 4,000</option>
                <option value="Br 4,000 - Br 6,000">Br 4,000 - Br 6,000</option>
                <option value="Br 6,000 - Br 8,000">Br 6,000 - Br 8,000</option>
                <option value="Br 8,000 - Br 10,000">Br 8,000 - Br 10,000</option>
                <option value="Br 10,000 - Br 12,000">Br 10,000 - Br 12,000</option>
                <option value="Br 12,000 - Br 14,000">Br 12,000 - Br 14,000</option>
                <option value="Br 14,000 - Br 16,000">Br 14,000 - Br 16,000</option>
                <option value="Br 16,000 - Br 18,000">Br 16,000 - Br 18,000</option>
                <option value="Br 18,000 - Br 20,000">Br 18,000 - Br 20,000</option>
                <option value="Br 20,000 - Br 22,000">Br 20,000 - Br 22,000</option>
                <option value="Br 22,000 - Br 25,000">Br 22,000 - Br 25,000</option>
                <option value="Br 25,000 - Br 30,000">Br 25,000 - Br 30,000</option>
                <option value="Br 30,000 - Br 35,000">Br 30,000 - Br 35,000</option>
                <option value="Br 35,000 - Br 40,000">Br 35,000 - Br 40,000</option>
                <option value="Br 40,000 - Br 45,000">Br 40,000 - Br 45,000</option>
                <option value="Br 45,000 - Br 50,000">Br 45,000 - Br 50,000</option>
                <option value="Above Br 50,000">Above Br 50,000</option>
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
                <option value="Below Br 5,000">Below Br 5,000</option>
                <option value="Br 5,000 - Br 7,500">Br 5,000 - Br 7,500</option>
                <option value="Br 7,500 - Br 10,000">Br 7,500 - Br 10,000</option>
                <option value="Br 10,000 - Br 12,000">Br 10,000 - Br 12,000</option>
                <option value="Br 12,000 - Br 14,000">Br 12,000 - Br 14,000</option>
                <option value="Br 14,000 - Br 16,000">Br 14,000 - Br 16,000</option>
                <option value="Br 16,000 - Br 18,000">Br 16,000 - Br 18,000</option>
                <option value="Br 18,000 - Br 20,000">Br 18,000 - Br 20,000</option>
                <option value="Br 20,000 - Br 22,000">Br 20,000 - Br 22,000</option>
                <option value="Br 22,000 - Br 25,000">Br 22,000 - Br 25,000</option>
                <option value="Br 25,000 - Br 30,000">Br 25,000 - Br 30,000</option>
                <option value="Br 30,000 - Br 35,000">Br 30,000 - Br 35,000</option>
                <option value="Br 35,000 - Br 40,000">Br 35,000 - Br 40,000</option>
                <option value="Br 40,000 - Br 45,000">Br 40,000 - Br 45,000</option>
                <option value="Br 45,000 - Br 50,000">Br 45,000 - Br 50,000</option>
                <option value="Above Br 50,000">Above Br 50,000</option>
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
                <input type="number" id="name" name="quantity" placeholder="Item Quantity" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="1" min="1" max="100">
            </div>
            <div class="col-span-10 md:col-span-3">
                
            </div>
       </div>
       @endif

       <!-- Shipment Section -->
    <div id="shipment" class="">
        <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
            <div class="col-span-10 lg:col-span-2">
                <div class="font-semibold">Shipment</div>
            </div>
            <div class="col-span-10 lg:col-span-5">
                <div class="flex justify-start">
                    <label class="flex items-center w-64">
                        <input type="radio" name="shipment" value="Ship" class="form-radio text-dark_green accent-dark_green" onchange="toggleShipping()">
                        <span class="ml-2">Shipping Possible</span>
                    </label>
                    <label class="flex items-center w-64">
                        <input checked type="radio" name="shipment" value="Pickup" class="form-radio text-dark_green accent-dark_green" onchange="toggleShipping()">
                        <span class="ml-2">Only Pickup</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Shipping Methods (Hidden by default) -->
        <div id="shipping" class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200 hidden">
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

        <div id="buyDirect" class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
            <div class="col-span-10 lg:col-span-2">
                <div class="font-semibold">Payment option</div>
            </div>
            <div class="col-span-10 lg:col-span-6 ">
                <div class="flex flex-col text-sm">
                  <label class="flex items-center  w-full">
                    <input type="radio" name="buy_direct" value="Yes" class="form-radio text-dark_green accent-dark_green"  required>
                    <span class="ml-2 ">Yes, I would like to sell this item using the free ‘Buy Direct’ payment option</span>
                    
                  </label>
                  <div class="my-2 w-full border border-gray-300 p-2 rounded-lg text-sm">
                        <div class="flex">
                            <span class="mr-1">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                                </svg>
                            </span>
                            <span>Selecting this option allows buyers to purchase your item using ‘Buy Direct.’ <br><a class="font-semibold text-dartk_green" href="/payments-refunds">Learn More</a> </span>
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
                    <input checked type="radio" name="buy_direct" value="No" class="form-radio text-dark_green accent-dark_green" >
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
            <input id="content" type="hidden" name="description" value="{{ old('description') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
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

            @if ($errors->has('images'))
                <span class="text-red-400">{{ $errors->first('images') }}</span>
            @endif

            <div id="image-error" class="mb-2 hidden text-sm text-red-500"></div>

            <div class="flex justify-start border-dashed border-2 border-gray-300 p-2">

                <!-- GOOD: Native Label (Android-friendly) -->
                <label for="imageUpload" class="cursor-pointer flex items-center">
                    <div class="flex items-center justify-center px-3 py-1 m-2 text-gray-700 border border-gray-300 hover:bg-primary transition">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                                </svg>
                    </div>
                </label>

                <input id="imageUpload" name="images[]" type="file" multiple accept="image/*" class="hidden">
                <input type="hidden" name="image_order" id="image_order">

                <!-- Preview -->
                <div id="preview" class="grid grid-cols-4 md:grid-cols-4 gap-4 p-2 w-full"></div>
            </div>

            <div class="text-xs flex justify-start items-center mt-2">
                <img src="{{ asset('frontend/images/swap.png') }}" class="h-6 mx-2">
                Move to Change the Order
            </div>
        </div>

        <div class="col-span-10 md:col-span-3">
            <div class="text-xs">
                <span class="font-semibold">Tip:</span> Up to 20 images with a max of 20MB. Avoid watermarks or text.
            </div>
        </div>
    </div>


    <div id="image-error" class="hidden mb-4 p-3 rounded-lg bg-red-100 text-red-800 text-sm font-medium"></div>


    <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
            <div class="col-span-10 md:col-span-2">
                <div class="font-semibold">Location</div>
            </div>
            <div class="col-span-10 md:col-span-3">
                @if ($errors->has('state'))
                <span class="text-danger">{{ $errors->first('state') }}</span>
            @endif
            <select onchange="toggleLGA(this);" name="state" id="state" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white">
                <option value="" selected="selected">-- Select State --</option>
                        @foreach ($states as $state)
                            <option value="{{ $state->name }}">{{ $state->name }}</option>
                        @endforeach
            </select>
            </div>
            <div class="col-span-10 md:col-span-3">
                @if ($errors->has('lga'))
                    <span class="text-red-400">{{ $errors->first('lga') }}</span>
                @endif
                <select name="lga" id="lga" class="w-48 px-3 py-2 border border-gray-300 rounded shadow-sm bg-white select-lga" required>
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
                <option value="Yes">Yes</option>
                <option value="No">No</option>
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

       <div class="my-3">
           @include('admin.components.post-boost')
       </div>

       <div class="text-xs mt-5 mb-3">
           Our terms of use apply. Information about processing You can find your data in our privacy policy.
       </div>

       <div class="flex justify-start mb-10">
            <button type="submit" class="btn btn-secondary py-2 px-6">Post Ad</button>
       </div>


    </form>
</section>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"
        integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
        integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo"
        crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"
        integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI"
        crossorigin="anonymous"></script>
<script src="{{ asset('frontend/js/lga.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script src='https://cdn.jsdelivr.net/npm/tesseract.js@4/dist/tesseract.min.js'></script>
<script src="{{ asset('dashboard/js/post-ad.js') }}"></script>
<script src="{{ asset('dashboard/js/word-count.js') }}"></script>
<script src="{{ asset('dashboard/js/sortable.js') }}"></script>
<script src="{{ asset('dashboard/js/submit.js') }}"></script>



@include('user.layouts.footer')
