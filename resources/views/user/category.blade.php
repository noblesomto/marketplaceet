@include('user.layouts.header')
@include('user.layouts.nav')
@include('user.layouts.nav')
@include('user.layouts.search')

<section class="w-full md:w-3/6 bg-white mx-auto p-3">
    <div class="border-b-2 border-b-gray-200 pt-10 px-2 font-bold text-dark_green mb-2">
        Ad Details
        @include('public.components.layouts.flash-message')
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
                    <input type="radio" name="ad_type" value="Private" class="form-radio text-dark_green"  required>
                    <span class="ml-2 ">I offer</span>
                  </label>

                  <label class="flex items-center w-64">
                    <input type="radio" name="ad_type" value="Commercial" class="form-radio text-dark_green" >
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
            <input type="text" name="ad_title" placeholder="Ad Title" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="{{ old('ad_title') }}" required>
            </div>
            <div class="col-span-10 md:col-span-3">
                <div class="text-xs">
                   <span class="font-semibold"> Tip:</span> With a meaningful title, you sell better.
                  </div>
            </div>
       </div>


       
     <div class="grid grid-cols-10 gap-2 md:gap-10 py-3 border-b border-b-gray-200">
            <div class="col-span-10 md:col-span-2">
                <div class="font-semibold">Description</div>
            </div>
            <div class="col-span-10 md:col-span-5">
                @if ($errors->has('description'))
                    <span class="text-red-400">{{ $errors->first('description') }}</span>
                @endif
            <textarea name="description" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>{{ old('description') }}</textarea>
            </div>
            <div class="col-span-10 md:col-span-3">
                
            </div>
       </div>
   



       <div class="mt-5 py-3 border-b border-b-gray-200">
           <div class="font-semibold">Publish your ad</div>        
       </div>

       <div class="text-xs my-3">
           Our terms of use apply. Information about processing You can find your data in our privacy policy.
       </div>

       <div class="flex justify-start mb-10">
            <button type="submit" class="btn btn-secondary">Submit</button>
       </div>


    </form>
</section>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    document.getElementById('category').addEventListener('change', function () {
        var countryId = this.value;
        
        // Fetch states
        axios.get('/fetch-subcat/' + countryId)
            .then(function (response) {
                var stateSelect = document.getElementById('subcategory');
                stateSelect.innerHTML = '<option value="">Select Sub Category</option>'; // Reset state dropdown
                document.getElementById('brand').innerHTML = '<option value="">Select Brand</option>'; // Reset city dropdown
                 var div1 = document.getElementById("div1");
                var div2 = document.getElementById("div2");
                // Hide all divs initially
            div1.classList.add("hidden");
            div2.classList.add("hidden");

                response.data.forEach(function (subcat) {
                    var option = document.createElement('option');
                    option.value = subcat.subcat_id;
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

        // Fetch cities
        axios.get('/fetch-brand/' + stateId)
            .then(function (response) {
                var citySelect = document.getElementById('brand');
                citySelect.innerHTML = '<option value="">Select Brand</option>'; // Reset city dropdown
                
                response.data.forEach(function (brand) {
                    var option = document.createElement('option');
                    option.value = brand.subcat_id;
                    option.text = brand.brand;
                    citySelect.appendChild(option);

                    // Show the relevant div based on the selection
            if (option.value === "31361") {
                div1.classList.remove("hidden");
            } else if (option.value === "84676") {
                div2.classList.remove("hidden");
            }
                });
            })
            .catch(function (error) {
                console.error(error);
            });
    });
</script>

<!-- JavaScript for Image Upload, Sorting, and Deleting -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const imageUpload = document.getElementById('imageUpload');
            const previewContainer = document.getElementById('preview');
            let imagesArray = [];

            // Handle file input change
            imageUpload.addEventListener('change', function(event) {
                const files = Array.from(event.target.files);
                imagesArray.push(...files);
                renderPreview();
            });

            // Render image previews
            function renderPreview() {
                previewContainer.innerHTML = ''; // Clear previous content

                imagesArray.forEach((file, index) => {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const div = document.createElement('div');
                        div.classList.add('relative', 'group', 'cursor-move');
                        div.setAttribute('draggable', true); // Enable drag

                        div.innerHTML = `
                            <img src="${e.target.result}" class="w-full h-auto rounded-lg shadow">
                            <button class="absolute top-0 right-0 w-4 h-4 text-red-500 bg-white rounded-full hover:bg-red-100 delete-image flex items-center justify-center" data-index="${index}">&times;</button>
                        `;
                        previewContainer.appendChild(div);

                        // Handle delete button
                        const deleteButton = div.querySelector('.delete-image');
                        deleteButton.addEventListener('click', function() {
                            deleteImage(index);
                        });

                        // Drag-and-drop functionality
                        div.addEventListener('dragstart', (e) => dragStart(e, index));
                        div.addEventListener('dragover', (e) => dragOver(e));
                        div.addEventListener('drop', (e) => drop(e, index));
                    };
                    reader.readAsDataURL(file);
                });
            }

            // Delete image function
            function deleteImage(index) {
                imagesArray.splice(index, 1); // Remove from array
                renderPreview(); // Re-render the previews
            }

            // Drag and drop functionality
            let draggedItemIndex = null;
            
            function dragStart(e, index) {
                draggedItemIndex = index;
                e.target.classList.add('opacity-50');
            }

            function dragOver(e) {
                e.preventDefault(); // Allow drop by preventing default behavior
            }

            function drop(e, index) {
                e.preventDefault();
                const draggedItem = imagesArray.splice(draggedItemIndex, 1)[0];
                imagesArray.splice(index, 0, draggedItem); // Reorder the array
                renderPreview(); // Re-render the previews
            }
        });
    </script>

    <script>
        function showHideDiv() {
            var selectedValue = document.getElementById("subcategory").value;
            
            // Get the div elements
            var div1 = document.getElementById("div1");
            var div2 = document.getElementById("div2");
            
            // Hide all divs initially
            div1.classList.add("hidden");
            div2.classList.add("hidden");
            
            // Show the relevant div based on the selection
            if (selectedValue === "31361") {
                div1.classList.remove("hidden");
            } else if (selectedValue === "84676") {
                div2.classList.remove("hidden");
            }
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"
        integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.0/dist/js/bootstrap.min.js"></script>

<script src="{{ asset('frontend/js/lga.js') }}"></script>
@include('user.layouts.footer')
