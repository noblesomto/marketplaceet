@include('user.layouts.header')
@include('user.layouts.back-nav')
@include('user.layouts.search')

<section class="w-full md:w-3/6  mx-auto p-3 text-sm ">


    <div class="border-b-2 bg-white border-b-gray-200 p-4 font-bold text-dark_green mb-2 flex justify-between items-center rounded-lg">
        <span>About Account</span>
        <div class="flex items-center space-x-4">

            <a title="Logout" href="/user/logout" ><i class="bi bi-box-arrow-right text-lg lg:text-2xl"></i></a>
        </div>
    </div>

    <div class=" mx-auto bg-white p-4 md:p-10 mt-2  rounded-lg">
    	<div class="w-full bg-white shadow p-3">
			<div class="flex flex-col">
		        <div class="flex">
                    <img class="w-10 h-10 rounded-full object-cover" src="{{ $user->profile_thumbnail_url }}" alt="{{ $user->name }}">
                </div>
		        <div class="mt-2 ">
		  <div class="block lg:hidden">
		      <div class="w-48 font-bold">Profile</div>
		    <div class="border border-gray-200 my-2"></div>
		  </div>
		  <div class="flex justify-start ">
		    <div>
		      <div class="text-dark_green text-sm font-semibold">{{ $user->name }}</div>
              @if($user->verified=='yes')
                <div class="bg-green-100  flex space-x-2 py-1 px-2 rounded-lg mt-2">
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-check" viewBox="0 0 16 16">
                          <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m1.679-4.493-1.335 2.226a.75.75 0 0 1-1.174.144l-.774-.773a.5.5 0 0 1 .708-.708l.547.548 1.17-1.951a.5.5 0 1 1 .858.514M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/>
                          <path d="M8.256 14a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z"/>
                        </svg>
                    </span>
                    <span class="text-xs">Verified Seller</span>
                </div>
            @endif
		      <div class="flex justify-start items-center bg-purple-200 rounded-lg px-2 py-1 text-xs mt-2">
		        <span class="mr-1">
		          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3">
		          <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" />
		        </svg>
		        </span>
		        <span>Top Satisfaction</span> 
		      </div>

		      <div class="flex justify-start items-center bg-purple-200 rounded-lg px-2 py-1 text-xs mt-1">
		        <span class="mr-1">
		          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3">
		          <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
		        </svg>
		        </span>
		        <span>Very Friendly</span>
		      </div>

		      <div class="flex justify-start items-center bg-purple-200 rounded-lg px-2 py-1 text-xs mt-1">
		        <span class="mr-1">
		          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-3">
		          <path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
		        </svg>
		        </span>
		        <span>Very Reliable</span>
		      </div>

		      <div class="flex justify-start items-center  rounded-lg px-2 py-1 text-xs mt-2">
		        <span class="mr-1">
		          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
		          <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
		        </svg>
		        </span>
		        <span>{{ $user->acc_type }} User</span> 
		      </div>

		      <div class="flex justify-start items-center  rounded-lg px-2 py-1 text-xs mt-1">
		        <span class="mr-1">
		          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
		          <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0Z" />
		        </svg>
		        </span>
		        <span>Active since {{ date('j F Y', strtotime($user->created_at)); }}</span> 
		      </div>

		    </div>

		  </div>
		  <div class="border border-gray-200 my-2"></div>
		  <div class="flex justify-between">
		    
		    @if($count_ads >= 1)
		    <a href="/user/my-ads">
		    	<div class="text-dark_green text-sm">{{ $count_ads }} ads online</div>
		    </a>
		    @else
		    <div class="flex-col items-center justify-center text-center pb-20">
			    <img src="{{ asset('frontend/images/empty-box.png') }}" class="mx-auto mb-4">
			    <p class="font-semibold mb-2">No Ads at the moment</p>
			    <p class="text-gray-600 mb-4">
			        Post your ad now and easily manage everything in one place.
			    </p>
			    <a href="/user/post-ad" class="bg-primary py-2 text-xs lg:text-lg rounded-lg px-4 mt-4">Post an Ad</a>
			</div>
		    @endif

		  </div>

		</div>
		    </div>
		</div>
    </div>

</section>





@include('user.layouts.footer')
