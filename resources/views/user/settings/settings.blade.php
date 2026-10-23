@include('user.layouts.header')
@include('user.layouts.back-nav')
@include('user.layouts.search')

<section class="w-full md:w-3/6 bg-white mx-auto p-3 text-sm pb-20">
    <div class="border-b-2 border-b-gray-200 pt-4 px-2 font-bold text-dark_green mb-2 flex justify-between">
        <span>Account Section</span>
        <div class="space-x-4">

            <a title="Logout" href="/user/logout" ><i class="bi bi-box-arrow-right text-lg lg:text-2xl"></i></a>
        </div>
    </div>

    <div class="max-w-2xl mx-auto bg-white md:p-10 mt-4 md:mt-10 mb-20 rounded-lg">
    	<div class="w-full">
			<div class="flex flex-col space-y-4">
		        <div class="mt-2 border-b-2 border-b-gray-200 pb-2">
		        	<a href="/user/profile-address">
		        		<div class="flex items-center space-x-4">
			        		<div>
			        			<i class="bi bi-person-square text-2xl"></i>
			        		</div>
			        		<div>
			        			<h4 class="font-semibold text-lg">Profile Information</h4>
			        			<p>Profile Name, Address</p>
			        		</div>
			        	</div>
		        	</a>
				</div>


				<div class="mt-2 border-b-2 border-b-gray-200 pb-2">
		        	<a href="/user/profile-info">
		        		<div class="flex items-center space-x-4">
			        		<div>
			        			<i class="bi bi-gear text-2xl"></i>
			        		</div>
			        		<div>
			        			<h4 class="font-semibold text-lg">Account Settings</h4>
			        			<p>Verified Phone Number, Email Address</p>
			        		</div>
			        	</div>
		        	</a>
				</div>

				<div class="mt-2 border-b-2 border-b-gray-200 pb-2">
		        	<a href="/user/get-verified">
		        		<div class="flex items-center space-x-4">
			        		<div>
			        			<i class="bi bi-person-check text-2xl"></i>
			        		</div>
			        		<div>
			        			<h4 class="font-semibold text-lg">Account Verification</h4>
			        			<p>Get Trusted, Get a Verification Badge</p>
			        		</div>
			        	</div>
		        	</a>
				</div>

				<div class="mt-2 border-b-2 border-b-gray-200 pb-2">
		        	<a href="/user/payments">
		        		<div class="flex items-center space-x-4">
			        		<div>
			        			<i class="bi bi-bag-dash text-2xl"></i>
			        		</div>
			        		<div>
			        			<h4 class="font-semibold text-lg">Payments</h4>
			        			<p>Payout Accounts</p>
			        		</div>
			        	</div>
		        	</a>
				</div>

				<div class="mt-2 border-b-2 border-b-gray-200 pb-2">
		        	<a href="/user/profile-notification">
		        		<div class="flex items-center space-x-4">
			        		<div>
			        			<i class="bi bi-bell text-2xl"></i>
			        		</div>
			        		<div>
			        			<h4 class="font-semibold text-lg">Notifications</h4>
			        			<p>Receive Emails</p>
			        		</div>
			        	</div>
		        	</a>
				</div>

				<div class="mt-2 border-b-2 border-b-gray-200 pb-2">
		        	<a href="/contact-us">
		        		<div class="flex items-center space-x-4">
			        		<div>
			        			<i class="bi bi-globe text-2xl"></i>
			        		</div>
			        		<div>
			        			<h4 class="font-semibold text-lg">Help & Feed Back</h4>
			        			<p>Help Center, Report a Problem, Give Feedback</p>
			        		</div>
			        	</div>
		        	</a>
				</div>

				<div class="mt-2 border-b-2 border-b-gray-200 pb-2">
		        	<a href="/user/logout">
		        		<div class="flex items-center space-x-4">
			        		<div>
			        			<i class="bi bi-box-arrow-right text-2xl"></i>
			        		</div>
			        		<div>
			        			<h4 class="font-semibold text-lg">Sign Out</h4>
			        			<p>{{ $user->email }}</p>
			        		</div>
			        	</div>
		        	</a>
				</div>
		    </div>
		</div>
    </div>

    
</section>



@include('user.layouts.footer')
