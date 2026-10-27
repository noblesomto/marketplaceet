@include('admin.layouts.header')
@include('admin.layouts.nav')
<style>
  /* Make .hidden work with CategoryUIManager (which uses Tailwind-style hidden class) */
  .hidden { display: none !important; }
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/trix@2.0.8/dist/trix.css">

<main id="main" class="main">
  <div class="pagetitle">
    <h1>{{ $page_title }}</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/admin/dashboard">Home</a></li>
        <li class="breadcrumb-item active">Adverts</li>
      </ol>
    </nav>
  </div><!-- End Page Title -->

  <section class="section">
    <div class="row">
      <div class="col-lg-12">
        <div class="card">
          <div class="card-body">
            <div class="border-bottom border-secondary pb-3 mb-3">
              <h5 class="card-title mb-0">Ad Details</h5>
              @include('public.components.flash-message')
              @if ($errors->any())
                <div class="alert alert-danger">
                  <p>There were some issues with your submission:</p>
                  <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                      <li>{{ $error }}</li>
                    @endforeach
                  </ul>
                </div>
              @endif
            </div>

            <form action="/admin/edit-ad/{{ $advert->id }}" id="advertForm" method="POST" role="form" enctype="multipart/form-data">
              @csrf 
              
              <!-- Bid/Request Section -->
              <div class="row mb-3 pb-3 border-bottom">
                <div class="col-md-2">
                  <label class="form-label fw-bold">Bid/request</label>
                </div>
                <div class="col-md-6">
                  <div class="d-flex">
                    <div class="form-check me-4">
                      <input class="form-check-input" type="radio" {{ $advert->ad_type === 'Private' ? 'checked' : '' }} name="ad_type" value="Private" id="offerRadio" required>
                      <label class="form-check-label" for="offerRadio">I offer</label>
                    </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" {{ $advert->ad_type === 'Commercial' ? 'checked' : '' }} name="ad_type" value="Commercial" id="lookingRadio">
                      <label class="form-check-label" for="lookingRadio">I'm looking for</label>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Title Section -->
              <div class="row mb-3 pb-3 border-bottom">
                <div class="col-md-2">
                  <label class="form-label fw-bold">Title</label>
                </div>
                <div class="col-md-5">
                  @if ($errors->has('ad_title'))
                    <span class="text-danger">{{ $errors->first('ad_title') }}</span>
                  @endif
                  <div class="position-relative">
                    <input type="text" 
                           name="ad_title"
                           id="ad_title"
                           placeholder="Ad Title"
                           class="form-control"
                           value="{{ $advert->ad_title }}"
                           maxlength="75"
                           required>
                    <div class="form-text">
                      <span id="char-count">0</span>/75 characters
                    </div>
                  </div>
                </div>
                <div class="col-md-3">
                  <div class="small">
                    <span class="fw-bold">Tip:</span> With a meaningful title, you sell better.
                  </div>
                </div>
              </div>

              <!-- Category Selection -->
              <div class="row mb-3 pb-3 border-bottom">
                <div class="col-md-2">
                  <label class="form-label fw-bold">Select Category</label>
                </div>
                <div class="col-md-8">
                  <div class="row g-2">
                    <div class="col-md-4">
                      <select id="category" name="category" class="form-select" required data-selected="{{ $advert->category }}">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                          <option value="{{ $category->id }}" {{ $advert->category == $category->id ? 'selected' : '' }}>
                            {{ $category->category }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-4">
                      <select id="subcategory" name="subcategory" class="form-select" required data-selected="{{ $advert->sub_category }}">
                        <option value="">Select Subcategory</option>
                        @foreach($subcategories as $subcategory)
                          <option value="{{ $subcategory->id }}" {{ $advert->sub_category == $subcategory->id ? 'selected' : '' }}>
                            {{ $subcategory->sub_category }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                    <div class="col-md-4">
                      <select id="brand" name="brand" class="form-select" required data-selected="{{ $advert->brand }}">
                        <option value="">Select Brand</option>
                        @foreach($brands as $brand)
                          <option value="{{ $brand->id }}" {{ $advert->brand == $brand->id ? 'selected' : '' }}>
                            {{ $brand->brand }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                    <div id="divModel" class="col-md-4 {{ in_array($advert->sub_category, [2, 21, 23]) ? '' : 'hidden' }}">
                      <select id="model" name="model" class="form-select" data-selected="{{ $advert->detailModelId() }}">
                        <option value="">Select Model</option>
                        @foreach($models as $model)
                          <option value="{{ $model->id }}"
                            {{ $advert->detailModelId() === $model->id ? 'selected' : '' }}
                          >
                            {{ $model->model }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Item Condition -->
              <div id="itemCondition" class="row mb-3 pb-3 border-bottom {{ (in_array($advert->category, [1, 3, 7, 11, 18]) || in_array($advert->sub_category, [2, 6, 16, 17, 18, 19, 21, 23])) ? 'hidden' : '' }}">
                <div class="col-md-2">
                  <label class="form-label fw-bold">Item Condition *</label>
                </div>
                <div class="col-md-6">
                  @if ($errors->has('item_condition'))
                    <span class="text-danger">{{ $errors->first('item_condition') }}</span>
                  @endif
                  <select id="pr" name="item_condition" class="form-select w-50">
                    <option value="">Please Choose</option>
                    <option value="New" {{ $advert->item_condition == 'New' ? 'selected' : '' }}>New</option>
                    <option value="Foreign Used" {{ $advert->item_condition == 'Foreign Used' ? 'selected' : '' }}>Foreign Used</option>
                    <option value="Locally Used" {{ $advert->item_condition == 'Locally Used' ? 'selected' : '' }}>Locally Used</option>
                  </select>
                </div>
              </div>

              <!-- Car Details (Conditionally Shown) -->
              <div id="divCar" class="p-2 {{ in_array($advert->sub_category, [2, 21, 23]) ? '' : 'hidden' }}">
                <div class="container-fluid">
                    <!-- Mileage -->
                    <div class="row py-3 border-bottom border-gray-200">
                        <div class="col-12 col-md-2">
                            <div class="fw-semibold">Mileage *</div>
                        </div>
                        <div class="col-12 col-md-6">
                            @if ($errors->has('mileage'))
                                <span class="text-danger">{{ $errors->first('mileage') }}</span>
                            @endif
                            <div class="d-flex w-50">
                                <input type="text" name="mileage" placeholder="mileage" class="form-control me-2" value="{{ optional($advert->car)->mileage }}">
                                <span class="mt-2">Km</span>
                            </div>
                        </div>
                    </div>

                    <!-- Vehicle Condition -->
                    <div class="row py-3 border-bottom border-gray-200">
                        <div class="col-12 col-md-2">
                            <div class="fw-semibold">Vehicle Condition *</div>
                        </div>
                        <div class="col-12 col-md-6">
                            @if ($errors->has('condition'))
                                <span class="text-danger">{{ $errors->first('condition') }}</span>
                            @endif
                            <select id="pr" name="condition" class="form-select w-50">
                                <option value="">Please Choose</option>
                                <option value="Local used" {{ optional($advert->car)->condition == 'Local used' ? 'selected' : '' }}> Local used</option>
                                <option value="Foreign used" {{ optional($advert->car)->condition == 'Foreign used' ? 'selected' : '' }}>Foreign used</option>
                                <option value="Brand new" {{ optional($advert->car)->condition == 'Brand new' ? 'selected' : '' }}>Brand new</option>
                                </select>
                            </div>
                        </div>

                        <!-- Registration -->
                        <div class="row py-3 border-bottom border-gray-200">
                            <div class="col-12 col-md-2">
                                <div class="fw-semibold">Registration</div>
                            </div>
                            <div class="col-12 col-md-6">
                                @if ($errors->has('registration'))
                                    <span class="text-danger">{{ $errors->first('registration') }}</span>
                                @endif
                                <div class="d-flex w-50">
                                    <select id="pr" name="registration" class="form-select">
                                        <option value="">--Select Type--</option>
                                        <option value="Registered" {{ optional($advert->car)->registration == 'Registered' ? 'selected' : '' }}>Registered</option>
                                        <option value="Unregistered" {{ optional($advert->car)->registration == 'Unregistered' ? 'selected' : '' }}>Unregistered</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Fuel Type -->
                        <div class="row py-3 border-bottom border-gray-200">
                            <div class="col-12 col-md-2">
                                <div class="fw-semibold">Fuel Type *</div>
                            </div>
                            <div class="col-12 col-md-6">
                                @if ($errors->has('fuel'))
                                    <span class="text-danger">{{ $errors->first('fuel') }}</span>
                                @endif
                                <select id="pr" name="fuel" class="form-select w-50">
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
                                            {{ optional($advert->car)->fuel === $fuelType ? 'selected' : '' }}
                                        >
                                            {{ $fuelType }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Transmission -->
                        <div class="row py-3 border-bottom border-gray-200">
                            <div class="col-12 col-md-2">
                                <div class="fw-semibold">Transmission *</div>
                            </div>
                            <div class="col-12 col-md-6">
                                @if ($errors->has('transmission'))
                                    <span class="text-danger">{{ $errors->first('transmission') }}</span>
                                @endif
                                <select id="pr" name="transmission" class="form-select w-50">
                                    <option value="">Please Choose</option>
                                    <option value="Automatic" {{ optional($advert->car)->transmission == 'Automatic' ? 'selected' : '' }}>Automatic</option>
                                    <option value="Manual"    {{ optional($advert->car)->transmission == 'Manual'    ? 'selected' : '' }}>Manual</option>
                                    <option value="CVT"       {{ optional($advert->car)->transmission == 'CVT'       ? 'selected' : '' }}>CVT</option>
                                    <option value="AMT"       {{ optional($advert->car)->transmission == 'AMT'       ? 'selected' : '' }}>AMT</option>
                                    <option value="Other"     {{ optional($advert->car)->transmission == 'Other'     ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                        </div>

                        <!-- Vehicle Type -->
                        <div class="row py-3 border-bottom border-gray-200">
                            <div class="col-12 col-md-2">
                                <div class="fw-semibold">Vehicle Type *</div>
                            </div>
                            <div class="col-12 col-md-6">
                                @if ($errors->has('vehicle_type'))
                                    <span class="text-danger">{{ $errors->first('vehicle_type') }}</span>
                                @endif
                                <select id="pr" name="vehicle_type" class="form-select w-50">
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
                                            {{ optional($advert->car)->vehicle_type === $vehicleType ? 'selected' : '' }}
                                        >
                                            {{ $vehicleType }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Exterior Color -->
                        <div class="row py-3 border-bottom border-gray-200">
                            <div class="col-12 col-md-2">
                                <div class="fw-semibold">Exterior Color *</div>
                            </div>
                            <div class="col-12 col-md-6">
                                @if ($errors->has('exterior_color'))
                                    <span class="text-danger">{{ $errors->first('exterior_color') }}</span>
                                @endif
                                <select id="exterior_color" name="exterior_color" class="form-select w-50">
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

                        <!-- Number of Doors -->
                        <div class="row py-3 border-bottom border-gray-200">
                            <div class="col-12 col-md-2">
                                <div class="fw-semibold">Number of Doors</div>
                            </div>
                            <div class="col-12 col-md-6">
                                @if ($errors->has('doors'))
                                    <span class="text-danger">{{ $errors->first('doors') }}</span>
                                @endif
                                <select id="pr" name="doors" class="form-select w-50">
                                    <option value="">Please Choose</option>
                                    <option value="1 Door" {{ optional($advert->car)->doors == '1 Door' ? 'selected' : '' }}>1 Door</option>
                                    <option value="2 Doors" {{ optional($advert->car)->doors == '2 Doors' ? 'selected' : '' }}>2 Doors</option>
                                    <option value="3 Doors" {{ optional($advert->car)->doors == '3 Doors' ? 'selected' : '' }}>3 Doors</option>
                                    <option value="4 Doors" {{ optional($advert->car)->doors == '4 Doors' ? 'selected' : '' }}>4 Doors</option>
                                </select>
                            </div>
                        </div>

                        <!-- Material Interior -->
                        <div class="row py-3 border-bottom border-gray-200">
                            <div class="col-12 col-md-2">
                                <div class="fw-semibold">Material Interior</div>
                            </div>
                            <div class="col-12 col-md-6">
                                @if ($errors->has('material_interior'))
                                    <span class="text-danger">{{ $errors->first('material_interior') }}</span>
                                @endif
                                <select id="pr" name="material_interior" class="form-select w-50">
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
                                            @if(optional($advert->car)->material_interior == $material) selected @endif>
                                            {{ $material }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Exterior Equipment -->
                        <div class="row py-3 border-bottom border-gray-200">
                            <div class="col-12 col-md-2">
                                <div class="fw-semibold">Exterior Equipment</div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="row">
                                    <div class="col-6">
                                        <div>
                                            @php
                                                $exteriorEquipment = [
                                                    'Trailer hitch',
                                                    'Parking assistance',
                                                    'Alloy wheels',
                                                    'Xenon/LED headlights'
                                                ];
                                                $selectedEquipment = json_decode(optional($advert->car)->exterior_equipment) ?? [];
                                            @endphp

                                            @foreach(array_slice($exteriorEquipment, 0, 2) as $equipment)
                                                <div class="form-check">
                                                    <input id="exterior-{{ Str::slug($equipment) }}" 
                                                        type="checkbox" 
                                                        name="exterior_equipment[]" 
                                                        value="{{ $equipment }}" 
                                                        class="form-check-input"
                                                        {{ in_array($equipment, $selectedEquipment) ? 'checked' : '' }}>
                                                    <label for="exterior-{{ Str::slug($equipment) }}" class="form-check-label">
                                                        {{ $equipment }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div>
                                            @foreach(array_slice($exteriorEquipment, 2) as $equipment)
                                                <div class="form-check">
                                                    <input id="exterior-{{ Str::slug($equipment) }}" 
                                                        type="checkbox" 
                                                        name="exterior_equipment[]" 
                                                        value="{{ $equipment }}" 
                                                        class="form-check-input"
                                                        {{ in_array($equipment, $selectedEquipment) ? 'checked' : '' }}>
                                                    <label for="exterior-{{ Str::slug($equipment) }}" class="form-check-label">
                                                        {{ $equipment }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Interior Equipment -->
                        <div class="row py-3 border-bottom border-gray-200">
                            <div class="col-12 col-md-2">
                                <div class="fw-semibold">Interior Equipment</div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="row">
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
                                        $selectedInterior = json_decode(optional($advert->car)->interior) ?? [];
                                    @endphp
                                    
                                    <div class="col-6">
                                        <div>
                                            @foreach(array_slice($interiorEquipment, 0, 5) as $equipment)
                                                <div class="form-check">
                                                    <input id="interior-{{ Str::slug($equipment) }}" 
                                                        type="checkbox" 
                                                        name="interior[]" 
                                                        value="{{ $equipment }}" 
                                                        class="form-check-input"
                                                        {{ in_array($equipment, $selectedInterior) ? 'checked' : '' }}>
                                                    <label for="interior-{{ Str::slug($equipment) }}" class="form-check-label">
                                                        {{ $equipment }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div>
                                            @foreach(array_slice($interiorEquipment, 5) as $equipment)
                                                <div class="form-check">
                                                    <input id="interior-{{ Str::slug($equipment) }}" 
                                                        type="checkbox" 
                                                        name="interior[]" 
                                                        value="{{ $equipment }}" 
                                                        class="form-check-input"
                                                        {{ in_array($equipment, $selectedInterior) ? 'checked' : '' }}>
                                                    <label for="interior-{{ Str::slug($equipment) }}" class="form-check-label">
                                                        {{ $equipment }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Security -->
                        <div class="row py-3 border-bottom border-gray-200">
                            <div class="col-12 col-md-2">
                                <div class="fw-semibold">Security</div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="row">
                                    @php
                                        $securityFeatures = [
                                            'Anti-lock braking system (ABS)',
                                            'Service history maintained',
                                        ];
                                        $selectedSecurity = json_decode(optional($advert->car)->security) ?? [];
                                    @endphp
                                    
                                    <div class="col-6">
                                        <div>
                                            @foreach(array_slice($securityFeatures, 0, ceil(count($securityFeatures)/2)) as $feature)
                                                <div class="form-check">
                                                    <input id="security-{{ Str::slug($feature) }}" 
                                                        type="checkbox" 
                                                        name="security[]" 
                                                        value="{{ $feature }}" 
                                                        class="form-check-input"
                                                        {{ in_array($feature, $selectedSecurity) ? 'checked' : '' }}>
                                                    <label for="security-{{ Str::slug($feature) }}" class="form-check-label">
                                                        {{ $feature }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div>
                                            @foreach(array_slice($securityFeatures, ceil(count($securityFeatures)/2)) as $feature)
                                                <div class="form-check">
                                                    <input id="security-{{ Str::slug($feature) }}" 
                                                        type="checkbox" 
                                                        name="security[]" 
                                                        value="{{ $feature }}" 
                                                        class="form-check-input"
                                                        {{ in_array($feature, $selectedSecurity) ? 'checked' : '' }}>
                                                    <label for="security-{{ Str::slug($feature) }}" class="form-check-label">
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
              </div>

              <!-- Phone Details (Conditionally Shown) -->
              <div id="divPhone" class="mt-4 p-4 {{ $advert->sub_category == 6 ? '' : 'hidden' }}">
                <div class="container-fluid">
                        <!-- Phone Color -->
                        <div class="row py-3 border-bottom border-gray-200">
                            <div class="col-12 col-md-4">
                                <div class="fw-semibold">Phone Color *</div>
                            </div>
                            <div class="col-12 col-md-6">
                                @if ($errors->has('phone_color'))
                                    <span class="text-danger">{{ $errors->first('phone_color') }}</span>
                                @endif
                                <select id="Phonecolor" name="phone_color" class="form-select w-50">
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

                        <!-- Device -->
                        <div class="row py-3 border-bottom border-gray-200">
                            <div class="col-12 col-md-4">
                                <div class="fw-semibold">Device *</div>
                            </div>
                            <div class="col-12 col-md-6">
                                @if ($errors->has('device'))
                                    <span class="text-danger">{{ $errors->first('device') }}</span>
                                @endif
                                <select id="pr" name="device" class="form-select w-50">
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
                                                {{ optional($advert->phone)->device == $value ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Condition -->
                        <div class="row py-3 border-bottom border-gray-200">
                            <div class="col-12 col-md-4">
                                <div class="fw-semibold">Condition *</div>
                            </div>
                            <div class="col-12 col-md-6">
                                @if ($errors->has('phone_condition'))
                                    <span class="text-danger">{{ $errors->first('phone_condition') }}</span>
                                @endif
                                <select id="pr" name="phone_condition" class="form-select w-50">
                                    <option value="">Please Choose</option>
                                    @php
                                        $phoneConditions = [
                                            'New - Unboxed' => 'New <span class="text-xs">(New and Unboxed)</span>',
                                            'Foreign Used - No Packaging' => 'Foreign Used <span class="text-xs">(Without original packaging)</span>',
                                            'Used - Very Good' => 'Very Good (Well-maintained item with barely visible signs of wear)',
                                            'Used - Good' => 'Good (Used item with visible signs of wear)',
                                            'Used - In Order' => 'In Order (Used item with clearly visible signs of wear, but still usable)',
                                            'Used - Defect' => 'Defect (Defective item suitable for repair or spare parts)'
                                        ];
                                    @endphp
                                    
                                    @foreach($phoneConditions as $value => $label)
                                        <option value="{{ $value }}" 
                                                {{ optional($advert->phone)->condition == $value ? 'selected' : '' }}
                                                {!! $value !== 'New - Unboxed' ? 'data-description="'.htmlspecialchars($label).'"' : '' !!}>
                                            {!! $label !!}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- RAM / Storage — optional; blank ones are read from the title/description -->
                        @foreach(\App\Models\PhoneDetail::SPEC_INPUTS as $specField => $specInput)
                            @php $current = old($specInput, optional($advert->phone)->{$specField}); @endphp
                            <div class="row py-3 border-bottom border-gray-200">
                                <div class="col-12 col-md-4">
                                    <div class="fw-semibold">{{ \App\Support\PhoneSpecs::LABELS[$specField] }}</div>
                                </div>
                                <div class="col-12 col-md-6">
                                    @if ($errors->has($specInput))
                                        <span class="text-danger">{{ $errors->first($specInput) }}</span>
                                    @endif
                                    <select name="{{ $specInput }}" class="form-select w-50">
                                        <option value="">Not specified</option>
                                        @foreach(\App\Support\PhoneSpecs::options($specField) as $option)
                                            <option value="{{ $option }}" {{ $current === $option ? 'selected' : '' }}>{{ $option }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endforeach
                    </div>
              </div>

              <!-- Computer Specs (Laptops & Computers) — optional; blank ones are read from the title/description -->
              <div id="divComputer" class="mt-4 p-4 {{ old('subcategory', $advert->sub_category) == \App\Support\DeviceModels::COMPUTERS_SUBCATEGORY_ID ? '' : 'hidden' }}">
                <div class="container-fluid">
                    @foreach(\App\Support\ComputerSpecs::allOptions() as $field => $options)
                        @php $current = old($field, optional($advert->computer)->{$field}); @endphp
                        <div class="row py-3 border-bottom border-gray-200">
                            <div class="col-12 col-md-4">
                                <div class="fw-semibold">{{ \App\Support\ComputerSpecs::LABELS[$field] }}</div>
                            </div>
                            <div class="col-12 col-md-6">
                                @if ($errors->has($field))
                                    <span class="text-danger">{{ $errors->first($field) }}</span>
                                @endif
                                <select name="{{ $field }}" class="form-select w-50">
                                    <option value="">Not specified</option>
                                    @foreach($options as $option)
                                        <option value="{{ $option }}" {{ $current === $option ? 'selected' : '' }}>{{ $option }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endforeach
                </div>
              </div>

              <!-- Shipment Section -->
              <div id="shipment">
                <div class="row mb-3 pb-3 border-bottom">
                  <div class="col-md-2">
                    <label class="form-label fw-bold">Shipment</label>
                  </div>
                  <div class="col-md-5">
                    <div class="d-flex">
                      <div class="form-check me-4">
                        <input class="form-check-input" type="radio" {{ $advert->shipment === 'Ship' ? 'checked' : '' }} name="shipment" value="Ship" id="shipRadio" onclick="toggleDiv()">
                        <label class="form-check-label" for="shipRadio">Shipping Possible</label>
                      </div>
                      <div class="form-check">
                        <input class="form-check-input" type="radio" {{ $advert->shipment === 'Pickup' ? 'checked' : '' }} name="shipment" value="Pickup" id="pickupRadio" onclick="toggleDiv()">
                        <label class="form-check-label" for="pickupRadio">Only Pickup</label>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Shipping Methods -->
                @if(config('global.shipping_carriers_enabled'))
<div id="shipping" class="row mb-3 pb-3 border-bottom">
                  <div class="col-md-2">
                    <label class="form-label fw-bold">Shipping Method</label>
                  </div>
                  <div class="col-md-5">
                    <div id="shipping-methods">
                      @foreach($shippings as $row)
                        <div class="border border-primary rounded p-2 mt-2">
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="shipping[]" value="{{ $row->id }}" id="shipping-{{ $row->id }}" {{ in_array($row->id, $selectedShippingIds) ? 'checked' : '' }}>
                            <label class="form-check-label d-flex flex-column" for="shipping-{{ $row->id }}">
                              <div class="d-flex align-items-center">
                                <span><img class="w-10" src="{{ asset('uploads/shipping/'.$row->logo) }}"></span>
                                <span class="ms-2 fw-bold">{{ $row->company }}</span>
                              </div>
                              <p>Max. {{ $row->weight }} kg, {{ $row->description }}</p>
                            </label>
                          </div>
                        </div>
                      @endforeach
                    </div>
                    <p id="shipping-error" class="mt-2 text-danger d-none">Please select at least one shipping method.</p>
                  </div>
                    @endif
                </div>
              </div>

              <!-- Price Section -->
              <div id="price" class="row mb-3 pb-3 border-bottom">
                <div class="col-md-2">
                  <label class="form-label fw-bold">Price</label>
                </div>
                <div class="col-md-3">
                  @if ($errors->has('price'))
                    <span class="text-danger">{{ $errors->first('price') }}</span>
                  @endif
                  <div class="d-flex align-items-center">
                    <input type="text" name="price" class="form-control w-50" value="{{ $advert->price }}">
                    <span class="ms-2">Birr</span>
                  </div>
                </div>
                <div id="services" class="col-md-2 mt-1">
                  <div class="form-check">
                    <input type="hidden" name="contact_price" value="no">
                    <input class="form-check-input" type="checkbox" name="contact_price" value="yes" id="contactPrice" {{ $advert->contact_price == 'yes' ? 'checked' : '' }}>
                    <label class="form-check-label" for="contactPrice">Contact For Price</label>
                  </div>
                </div>
                <div class="col-md-3">
                  <select id="price_type" name="price_type" class="form-select">
                    <option value="Fixed" {{ ($advert->price_type =='Fixed') ? "selected" : ""; }}>Fixed Price</option>
                    <option value="Negotiable" {{ ($advert->price_type =='Negotiable') ? "selected" : ""; }}>Negotiable</option>
                    <option value="Give Away" {{ ($advert->price_type =='Give Away') ? "selected" : ""; }}>Give Away</option>
                  </select>
                </div>
              </div>

              <!-- Salary Section -->
              <div id="salary" class="row mb-3 pb-3 border-bottom">
                <div class="col-md-2">
                  <label class="form-label fw-bold">Salary</label>
                </div>
                <div class="col-md-5">
                  @if ($errors->has('salary'))
                    <span class="text-danger">{{ $errors->first('salary') }}</span>
                  @endif
                  <select id="salary" name="salary" class="form-select w-50">
                    <option value="">--Select Salary--</option>
                    @foreach(['Commission','Below Br 2,000','Br 2,000 - Br 4,000','Br 4,000 - Br 6,000','Br 6,000 - Br 8,000','Br 8,000 - Br 10,000','Br 10,000 - Br 12,000','Br 12,000 - Br 14,000','Br 14,000 - Br 16,000','Br 16,000 - Br 18,000','Br 18,000 - Br 20,000','Br 20,000 - Br 22,000','Br 22,000 - Br 25,000','Br 25,000 - Br 30,000','Br 30,000 - Br 35,000','Br 35,000 - Br 40,000','Br 40,000 - Br 45,000','Br 45,000 - Br 50,000','Above Br 50,000'] as $sal)
                      <option value="{{ $sal }}" {{ $advert->salary == $sal ? 'selected' : '' }}>{{ $sal }}</option>
                    @endforeach
                  </select>
                </div>
              </div>

              <!-- Expected Salary Section -->
              <div id="expectedSalary" class="row mb-3 pb-3 border-bottom">
                <div class="col-md-2">
                  <label class="form-label fw-bold">Expected Salary</label>
                </div>
                <div class="col-md-5">
                  @if ($errors->has('expected_salary'))
                    <span class="text-danger">{{ $errors->first('expected_salary') }}</span>
                  @endif
                  <select id="expected_salary" name="expected_salary" class="form-select w-50">
                    <option value="">--Select Expected Salary--</option>
                    @foreach(['Below Br 5,000','Br 5,000 - Br 7,500','Br 7,500 - Br 10,000','Br 10,000 - Br 12,000','Br 12,000 - Br 14,000','Br 14,000 - Br 16,000','Br 16,000 - Br 18,000','Br 18,000 - Br 20,000','Br 20,000 - Br 22,000','Br 22,000 - Br 25,000','Br 25,000 - Br 30,000','Br 30,000 - Br 35,000','Br 35,000 - Br 40,000','Br 40,000 - Br 45,000','Br 45,000 - Br 50,000','Above Br 50,000'] as $expSal)
                      <option value="{{ $expSal }}" {{ $advert->expected_salary == $expSal ? 'selected' : '' }}>{{ $expSal }}</option>
                    @endforeach
                  </select>
                </div>
              </div>

              <!-- Quantity Section (Commercial Users Only) -->
             
                <div id="quantity" class="row mb-3 pb-3 border-bottom">
                  <div class="col-md-2">
                    <label class="form-label fw-bold">Item Quantity</label>
                  </div>
                  <div class="col-md-5">
                    @if ($errors->has('quantity'))
                      <span class="text-danger">{{ $errors->first('quantity') }}</span>
                    @endif
                    <input type="number" name="quantity" placeholder="Item Quantity" class="form-control" value="{{ $advert->quantity }}" min="1" max="100">
                  </div>
                </div>
           

              <!-- Buy Direct Section -->
              <div id="buyDirect" class="row mb-3 pb-3 border-bottom {{ in_array($advert->sub_category, [2, 21, 23]) ? 'hidden' : '' }}">
                <div class="col-md-2">
                  <label class="form-label fw-bold">Bid/request</label>
                </div>
                <div class="col-md-6">
                  <div class="d-flex flex-column">
                    <div class="form-check">
                      <input class="form-check-input" type="radio" {{ $advert->buy_direct === 'Yes' ? 'checked' : '' }} name="buy_direct" value="Yes" id="buyDirectYes" required>
                      <label class="form-check-label" for="buyDirectYes">Yes, I would like to use the benefits of "Buy Direct" for free</label>
                    </div>
                    <div class="my-2 border border-secondary p-2 rounded small">
                      <div class="my-2 w-100 border border-gray-300 p-2 rounded text-sm">
            <div class="d-flex align-items-start">
                <span class="me-1 mt-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                </span>
                <span>
                    This item can be paid for using the new "Buy Now" feature. <br>
                    <a class="fw-semibold text-success" href="">Learn More</a>
                </span>
            </div>
            <div class="d-flex align-items-start mt-1 small">
                <span class="me-1 mt-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 16px; height: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                </span>
                <span><strong class="fw-semibold">No negotiation</strong> - your price is what counts.</span>
            </div>
            <div class="d-flex align-items-start mt-1 small">
                <span class="me-1 mt-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 16px; height: 16px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                </span>
                <span>Our buyer protection provides security, creates trust and <strong class="fw-semibold">increases your chances of selling.</strong></span>
            </div>
        </div>
                </div>
                    <div class="form-check">
                      <input class="form-check-input" type="radio" {{ $advert->buy_direct === 'No' ? 'checked' : '' }} name="buy_direct" value="No" id="buyDirectNo">
                      <label class="form-check-label" for="buyDirectNo">No, do not use "Buy direct"</label>
                    </div>
                  </div>
                </div>
              </div>


              <!-- Description Section -->
              <div class="row mb-3 pb-3 border-bottom">
                <div class="col-md-2">
                  <label class="form-label fw-bold">Description</label>
                </div>
                <div class="col-md-7">
                  @if ($errors->has('description'))
                    <span class="text-danger">{{ $errors->first('description') }}</span>
                  @endif
                 
                  <input id="content" type="hidden" name="description" value="{{ old('description', $advert->description ?? '') }}" required>
                  <trix-editor input="content" class="border rounded" style="min-height: 200px;"></trix-editor>
                  <div class="form-text">
                    <span id="word-count">0</span>/3500 characters
                  </div>
                </div>
              </div>

              <!-- Images Section -->
              <div class="row py-3 border-bottom border-2 border-light">
    <!-- Left Column -->
    <div class="col-12 col-md-2 mb-3 mb-md-0">
        <div class="fw-semibold">Pictures (recommended)</div>
    </div>

    <!-- Middle Column -->
    <div class="col-12 col-md-5 mb-3 mb-md-0">
        @error('images')
            <span class="text-danger small">{{ $message }}</span>
        @enderror
        <div id="image-error" class="text-danger small d-none"></div>

        <div class="d-flex align-items-start border border-2 border-secondary border-dashed p-2 rounded">
            <!-- Camera Icon for File Upload -->
            <div class="d-flex align-items-center">
                <label for="imageUpload" class="cursor-pointer">
                    <div class="d-flex align-items-center justify-content-center px-3 py-1 m-2 text-secondary border border-secondary rounded hover-bg-primary transition">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                             stroke="currentColor" width="40" height="40">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                        </svg>
                    </div>
                    <input name="images[]" type="file" id="imageUpload" multiple class="d-none" accept="image/*">
                    <input type="hidden" name="existing_image_order" id="existing_image_order">
                </label>
            </div>

            <!-- Image Preview Container -->
            <div id="preview" class="row g-3 ms-2">
                @foreach($advert->getMedia('images') as $media)
                    <div class="col-6 col-md-3 position-relative image-container" draggable="true" data-id="{{ $media->id }}">
                        <img src="{{ $media->getUrl('thumbnail') }}" class="img-fluid rounded shadow-sm" alt="Ad Image">
                        <button type="button"
                                class="btn btn-sm btn-light text-danger border-0 position-absolute top-0 end-0 translate-middle rounded-circle delete-image d-flex align-items-center justify-content-center"
                                data-id="{{ $media->id }}"
                                style="width: 24px; height: 24px;">&times;
                        </button>
                        <input type="hidden" name="existing_images[]" value="{{ $media->id }}">
                    </div>
                @endforeach
            </div>
        </div>

        <div class="small d-flex align-items-center mt-2">
            <img src="{{ asset('frontend/images/swap.png') }}" class="me-2" style="height: 24px;">
            Move to Change the Order
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-12 col-md-3">
        <div class="small">
            <span class="fw-semibold">Tip:</span>
            Min {{ $minImages }}, max {{ $maxImages }} images. Maximum file size 20 MB. Avoid watermarks or text overlays.
        </div>
    </div>
</div>


              <!-- Location Section -->
              <div class="row mb-3 pb-3 border-bottom">
                <div class="col-md-2">
                  <label class="form-label fw-bold">Location</label>
                </div>
                <div class="col-md-3">
                  @if ($errors->has('state'))
                    <span class="text-danger">{{ $errors->first('state') }}</span>
                  @endif
                  <select onchange="toggleLGA(this);" name="state" id="state" class="form-select">
                    <option value="" selected="selected">- Select State -</option>
                    @foreach($states as $state)
                      <option value="{{ $state->name }}" {{ ($advert->state == $state->name) ? "selected" : "" }}>{{ $state->name }}</option>
                    @endforeach
                  </select>
                </div>
                <div class="col-md-3">
                  @if ($errors->has('lga'))
                    <span class="text-danger">{{ $errors->first('lga') }}</span>
                  @endif
                  <select name="lga" id="lga" class="form-select select-lga" required>
                    @if($advert->state && $advert->lga)
                      <option value="{{ $advert->lga }}" selected>{{ $advert->lga }}</option>
                    @endif
                  </select>
                </div>
              </div>

              <!-- Name Section -->
              <div class="row mb-3 pb-3 border-bottom">
                <div class="col-md-2">
                  <label class="form-label fw-bold">Name</label>
                </div>
                <div class="col-md-5">
                  @if ($errors->has('name'))
                    <span class="text-danger">{{ $errors->first('name') }}</span>
                  @endif
                  <input type="text" name="name" placeholder="Profile Name" class="form-control" value="{{ $advert->user->name }}" readonly>
                </div>
                <div class="col-md-3">
                  <div class="small">
                    <span class="fw-bold">Tip:</span> Enter your name so that you increase the reliability of your ad.
                  </div>
                </div>
              </div>

              <!-- Show Contact Section -->
              <div class="row mb-3 pb-3 border-bottom">
                <div class="col-md-2">
                  <label class="form-label fw-bold">Show Contact?</label>
                </div>
                <div class="col-md-5">
                  @if ($errors->has('show_contact'))
                    <span class="text-danger">{{ $errors->first('show_contact') }}</span>
                  @endif
                  <select name="show_contact" class="form-select">
                    <option value="No" {{ $advert->show_contact == 'No' ? 'selected' : '' }}>No</option>
                    <option value="Yes" {{ $advert->show_contact == 'Yes' ? 'selected' : '' }}>Yes</option>
                  </select>
                </div>
                <div class="col-md-3">
                  <div class="small">
                    <span class="fw-bold">Tip:</span> Do you want users to see your contact details on the advert page?
                  </div>
                </div>
              </div>

              <!-- Submit Section -->
              <div class="mt-5 py-3 border-bottom">
                <h5 class="fw-bold">Publish your ad</h5>        
              </div>

              <div class="small my-3">
                Our terms of use apply. Information about processing You can find your data in our privacy policy.
              </div>

              <div class="d-flex justify-content-start mb-3">
                <button type="submit" class="btn btn-primary py-2 px-4">Update</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</main>
<script src="https://cdn.jsdelivr.net/npm/trix@2.0.8/dist/trix.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
<script>
  window.MIN_IMAGES = {{ $minImages }};
  window.MAX_IMAGES = {{ $maxImages }};
</script>
<script src="{{ asset('frontend/js/lga.js') }}"></script>
<script src="{{ asset('dashboard/js/category-ui-manager.js') }}"></script>
<script src="{{ asset('dashboard/js/device-model-field.js') }}?v=2"></script>
<script src="{{ asset('backend/js/edit-advert.js') }}?v=4"></script>
<script src="{{ asset('backend/js/edit-sortable.js') }}"></script>


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
            '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>' +
            'Updating...';
    });
</script>

@include('admin.layouts.footer')