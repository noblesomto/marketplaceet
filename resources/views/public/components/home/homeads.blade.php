<div class="flex justify-between h-16  -mb-2 px-2">
	<div class="flex items-center font-bold text-xl">
		Recent Listings
	</div>
	<div class="flex justify-end">
		<div class="text-dark_green text-xl  font-semibold mr-3 flex items-center"><a href="/user/post-ad">Place Ad Here</a> </div>
	
	</div>
</div>

<div class="container mx-auto">
    <div class="container mx-auto">
        <div id="listings-container" class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-2">
            @foreach ($listings as $row)
                @include('public.components.advert.advert-card', ['row' => $row])
            @endforeach
        </div>

        <!-- Load More Button for Desktop -->
        <div class="flex justify-center m-3">
            <button id="load-more-btn-desktop"
                    class="bg-dark_green hover:bg-green-700 text-white font-semibold py-3 px-20 rounded-lg shadow-md transition duration-200 ease-in-out transform hover:scale-105">
                Show More
            </button>
            <div id="loading-desktop" class="hidden flex items-center">
                <svg class="animate-spin h-8 w-8 text-dark_green mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span class="text-gray-600 font-medium">Loading more...</span>
            </div>
        </div>

        <!-- End of results message for Desktop -->
        <div id="no-more-ads-desktop" class="hidden text-center py-6 text-gray-500 pb-20">
            <p class="font-medium text-lg">You've reached the end of listings</p>
            <p class="text-sm mt-2">Check back later for new items!</p>
        </div>
    </div>
</div>


</div>

