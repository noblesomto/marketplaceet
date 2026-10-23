@include('user.layouts.header')
@include('user.layouts.back-nav')
@include('user.layouts.search')

<section class="w-full md:w-3/6 bg-white mx-auto p-3 text-sm pb-20">
    <div class="border-b-2 border-b-gray-200 pt-4 px-2 font-bold text-dark_green mb-2">
        Account Settings
        @include('public.components.flash-message')
    </div>

    <!-- Professional Alert Modal -->
    <div id="deactivateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                        <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900">Delete Account</h3>
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-sm text-gray-500">
                        Are you sure you want to delete your account? This action cannot be undone. You will lose access to all your data and settings.
                    </p>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" id="cancelDeactivate" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                        Cancel
                    </button>
                    <a href="/user/disable-account" id="confirmDeactivate" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        Yes, Delete
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="w-full max-w-xl mx-auto mt-10 space-y-6">
        <!-- Accordion 1 - Open by default -->
        <div class="accordion border rounded-lg">
            <button class="accordion-header w-full px-4 py-2 text-left font-semibold bg-gray-100 hover:bg-gray-200 flex justify-between items-center">
                <span>Phone number & Email</span>
                <svg class="icon w-4 h-4 transform transition-transform rotate-180" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div class="accordion-content px-4 py-6 bg-white border-t">
                <form method="POST" action="{{ route('user.profile.phone') }}" enctype="multipart/form-data">
                @csrf
                    <div class="space-y-4">
                        <div class="">
                            <label class="text-sm">Phone Number</label>
                            <input type="text" maxlength="11" pattern="[0-9]*" name="phone" placeholder="Phone Number" value="{{ $user->phone }}" class="w-full px-3 py-2 border-b-2 border-2-gray-300" required>
                        </div>
                        <div class="">
                            <label class="text-sm">Verified Email Address</label>
                            <input type="text"  name="email" placeholder="Email Address" value="{{ $user->email }}" class="w-full px-3 py-2 border-b-2 border-2-gray-300" readonly>
                        </div>
                        <div class="">
                            <button type="submit" class="bg-primary py-2 text-xs lg:text-lg rounded-lg px-4">
                                <span>Update</span>
                            </button>
                         </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Accordion 2 -->
        <div class="accordion border rounded-lg">
            <button class="accordion-header w-full px-4 py-2 text-left font-semibold bg-gray-100 hover:bg-gray-200 flex justify-between items-center">
                <span>Change Password</span>
                <svg class="icon w-4 h-4 transform transition-transform" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 9l-7 7-7-7" />
                </svg>
            </button>
            <div class="accordion-content hidden px-4 py-2 bg-white border-t">
                <form method="POST" action="{{ route('user.change.password') }}" enctype="multipart/form-data">
                @csrf
                    <div class="space-y-4">
                        <div class="">
                            <label class="text-sm">Current Password</label>
                            @if ($errors->has('old_password'))
                                <span class="text-red-700 text-sm">{{ $errors->first('old_password') }}</span>
                            @endif

                            <input type="password" name="old_password" placeholder="Current Password"  class="w-full px-3 py-2 border-b-2 border-2-gray-300" required>
                        </div>

                        <div class="">
                            <label class="text-sm">New Password</label>
                            @if ($errors->has('password'))
                                <span class="text-red-700 text-sm">{{ $errors->first('password') }}</span>
                            @endif

                            <input type="password" name="password" placeholder="New Password"  class="w-full px-3 py-2 border-b-2 border-2-gray-300" required>
                        </div>
                        <div class="">
                            <label class="text-sm">Confirm Password</label>
                            @if ($errors->has('password_confirmation'))
                                <span class="text-danger">{{ $errors->first('password_confirmation') }}</span>
                            @endif
                            <input type="password"  name="password_confirmation" placeholder="Confirm Password"  class="w-full px-3 py-2 border-b-2 border-2-gray-300" required>
                        </div>

                        <div class="">
                            <button type="submit" class="bg-primary py-2 text-xs lg:text-lg rounded-lg px-4">
                                <span>Update</span>
                            </button>
                         </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="mt-6 text-red-700 font-semibold">
            <a href="#" id="deactivateLink" class="hover:text-red-800 transition-colors duration-200">Delete Account</a>
        </div>
    </div>
</section>

<script>
    // Accordion functionality
    document.querySelectorAll('.accordion-header').forEach(header => {
        header.addEventListener('click', () => {
            const content = header.nextElementSibling;
            const icon = header.querySelector('.icon');

            content.classList.toggle('hidden');
            icon.classList.toggle('rotate-180');
        });
    });

    // Professional alert modal functionality
    document.addEventListener('DOMContentLoaded', function() {
        const deactivateLink = document.getElementById('deactivateLink');
        const deactivateModal = document.getElementById('deactivateModal');
        const cancelDeactivate = document.getElementById('cancelDeactivate');
        const confirmDeactivate = document.getElementById('confirmDeactivate');

        // Show modal when deactivate link is clicked
        deactivateLink.addEventListener('click', function(e) {
            e.preventDefault();
            deactivateModal.classList.remove('hidden');
        });

        // Hide modal when cancel button is clicked
        cancelDeactivate.addEventListener('click', function() {
            deactivateModal.classList.add('hidden');
        });

        // Hide modal when clicking outside the modal
        deactivateModal.addEventListener('click', function(e) {
            if (e.target === deactivateModal) {
                deactivateModal.classList.add('hidden');
            }
        });

        // Optional: Add keyboard support (ESC key to close)
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !deactivateModal.classList.contains('hidden')) {
                deactivateModal.classList.add('hidden');
            }
        });
    });
</script>

@include('user.layouts.footer')
