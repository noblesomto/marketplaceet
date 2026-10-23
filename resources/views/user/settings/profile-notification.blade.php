@include('user.layouts.header')
@include('user.layouts.back-nav')
@include('user.layouts.search')


<section class="w-full md:w-3/6 bg-white mx-auto p-3 text-sm pb-20">
    <div class="border-b-2 border-b-gray-200 pt-4 px-2 font-bold text-dark_green mb-2">
        Notification Settings
        @include('public.components.flash-message')
    </div>
  
		            
		        	<div class="max-w-2xl mx-auto bg-white p-3 rounded-lg">
				        <form method="POST" action="{{ route('user.profile.notification') }}">
				            @csrf
				       
				        <div class="mb-4 mt-1">
				            
						<div class="grid grid-cols-10 gap-4">
							<div class="col-span-7">
								<div class="flex-col">
									<h4 class="font-semibold">Email Notifications</h4>
									<p>Do you want to get notifications about new ads posted?</p>
								</div>
							</div>
							<div class="col-span-3">
								<div class="flex items-center">
								    <input type="hidden" name="notifications" id="notificationsInput" value="{{ strtolower($user->notification) }}">
								    <button type="button" id="notificationsToggle" 
								            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2
								                  {{ strtolower($user->notification) === 'yes' ? 'bg-green-500 focus:ring-green-500' : 'bg-gray-200 focus:ring-gray-300' }}">
								        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform duration-200
								                    {{ strtolower($user->notification) === 'yes' ? 'translate-x-6' : 'translate-x-1' }}"></span>
								    </button>
								    <span id="notificationsLabel" class="ml-3 text-sm font-medium text-gray-900">
								        {{ ucfirst($user->notification) }}
								    </span>
								</div>
							</div>
						</div>
  
  




				        </div>

				
				                
				        </form>
				        
				    </div>
		
 
</div>

    
</section>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('notificationsToggle');
    const label = document.getElementById('notificationsLabel');
    const input = document.getElementById('notificationsInput');
    
    toggle.addEventListener('click', function() {
        const isActive = input.value === 'yes';
        const newValue = isActive ? 'no' : 'yes';
        
        // Update the UI
        this.classList.toggle('bg-green-500', !isActive);
        this.classList.toggle('bg-gray-200', isActive);
        this.classList.toggle('focus:ring-green-500', !isActive);
        this.classList.toggle('focus:ring-gray-300', isActive);
        
        const span = this.querySelector('span');
        span.classList.toggle('translate-x-6', !isActive);
        span.classList.toggle('translate-x-1', isActive);
        
        // Update the value and label
        input.value = newValue;
        label.textContent = newValue.charAt(0).toUpperCase() + newValue.slice(1);
        
        // Send AJAX request to update database
        updateNotifications(newValue);
    });
    
    function updateNotifications(value) {
        fetch('{{ route("user.update-notifications") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ notifications: value })
        })
        .then(response => {
            if (!response.ok) throw new Error('Update failed');
            return response.json();
        })
        .catch(error => {
            console.error('Error:', error);
            // Revert changes if update failed
            toggle.click();
        });
    }
});
</script>
@include('user.layouts.footer')
