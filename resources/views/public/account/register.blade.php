@include('public.layouts.header')
@include('public.layouts.nav')
@include('public.components.mobile.mobile-nav')
@include('public.layouts.search')

<section class="min-h-screen bg-gray-50 py-10 px-1 sm:px-6 lg:px-8 font-sans">

    <!-- Main Card Container -->
    <div class="max-w-xl mx-auto bg-white rounded-2xl shadow-xl overflow-hidden">

        <!-- Header -->
        <div class="bg-white border-b border-gray-100 px-2 py-2 text-center">
            <h2 class="text-xl font-semibold text-gray-900 tracking-tight">Register in less than a minute</h2>
            <p class="mt-2 text-sm text-gray-500">Create your account to start buying and selling.</p>
        </div>

        <div class="px-2 lg:px-8 py-4">
            <!-- Flash Message -->
            @include('public.components.flash-message')

            <!-- Added ID 'register-form' for JS targeting -->
            <form id="register-form" method="POST" action="/register" class="space-y-2">
                @csrf

                <!-- Account Type Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3 text-center">
                        How would you like to use Marketplace Ethiopia?
                    </label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Private Option -->
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="acc_type" value="Private" class="peer sr-only" id="showDivRadio" onclick="showDiv('Profile Name')" required>
                            <div class="p-2 rounded-xl border-2 border-gray-200 hover:border-dark_green/50 peer-checked:border-dark_green peer-checked:bg-green-50 transition-all duration-200 flex  items-center justify-center text-center space-x-2">
                                <svg class="w-6 h-6 text-gray-400 peer-checked:text-dark_green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                <span class="block text-base font-bold text-gray-900 peer-checked:text-dark_green">Private</span>
                            </div>
                        </label>

                        <!-- Commercial Option -->
                        <label class="relative cursor-pointer group">
                            <input type="radio" name="acc_type" value="Commercial" class="peer sr-only" onclick="showDiv('Company Name')">
                            <div class="p-2 rounded-xl border-2 border-gray-200 hover:border-dark_green/50 peer-checked:border-dark_green peer-checked:bg-green-50 transition-all duration-200 flex items-center justify-center text-center space-x-2">
                                <svg class="w-6 h-6 text-gray-400 peer-checked:text-dark_green" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                <span class="block text-base font-bold text-gray-900 peer-checked:text-dark_green">Commercial</span>
                            </div>
                        </label>
                    </div>
                    <div class="text-center mt-3">
                        <button type="button" class="text-xs font-medium text-dark_green hover:text-green-700 underline transition-colors" onclick="showModal()">
                            When do I act commercially?
                        </button>
                    </div>
                </div>

                <!-- Dynamic Name Field -->
                <div id="myDiv" class="hidden transition-all duration-300 ease-in-out">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-dark_green focus:border-transparent outline-none transition-all"
                           placeholder="Profile Name">
                    @if ($errors->has('name'))
                        <p class="mt-1 text-xs text-red-600">{{ $errors->first('name') }}</p>
                    @endif
                </div>

                <!-- Phone Field -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                           class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-dark_green focus:border-transparent outline-none transition-all"
                           placeholder="08012345678" inputmode="numeric">
                    @if ($errors->has('phone'))
                        <p class="mt-1 text-xs text-red-600">{{ $errors->first('phone') }}</p>
                    @endif
                </div>



                <hr class="border-gray-100 my-6">

                <!-- Login Details -->
                <div>
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Login Details</h3>

                    <div class="space-y-4">
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-dark_green focus:border-transparent outline-none transition-all"
                                   placeholder="you@example.com" required>
                            @if ($errors->has('email'))
                                <p class="mt-1 text-xs text-red-600">{{ $errors->first('email') }}</p>
                            @endif
                        </div>

                        <div class="relative">
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                            <div class="relative">
                                <input type="password" id="password" name="password"
                                       class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-dark_green focus:border-transparent outline-none transition-all pr-12"
                                       placeholder="••••••••" required>
                                <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center px-4 text-gray-400 hover:text-gray-600 focus:outline-none">
                                    <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M10 3C5.4 3 1.73 6.11.4 10c1.33 3.89 5 7 9.6 7s8.27-3.11 9.6-7C18.27 6.11 14.6 3 10 3zM10 15a5 5 0 110-10 5 5 0 010 10zm0-8a3 3 0 100 6 3 3 0 000-6z" />
                                    </svg>
                                </button>
                            </div>
                            @if ($errors->has('password'))
                                <p class="mt-1 text-xs text-red-600">{{ $errors->first('password') }}</p>
                            @endif
                        </div>
                    </div>
                </div>


                <!-- Recaptcha Error Display -->
                @if ($errors->has('g-recaptcha-response'))
                    <div class="rounded-md bg-red-50 p-3">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" /></svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Security Check Failed. Please try again.</h3>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Submit Button with Invisible Recaptcha Binding -->
                <button type="button"
                    id="register-btn"
                    class="w-full bg-secondary_dark hover:bg-dark_green text-white font-bold py-4 px-4 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200">
                Register for Free
            </button>

                <!-- Legal Text (Updated for Hidden Badge Compliance) -->
                <p class="text-xs text-center text-gray-500 mt-4 leading-relaxed">
                    By registering, you agree to our <a class="text-dark_green font-semibold hover:underline" href="/our-terms">Terms of Use</a>.
                    This site is protected by reCAPTCHA and the Google
                    <a href="https://policies.google.com/privacy" class="text-dark_green hover:underline">Privacy Policy</a> and
                    <a href="https://policies.google.com/terms" class="text-dark_green hover:underline">Terms of Service</a> apply.
                </p>
            </form>
        </div>
    </div>
</section>

<!-- Commercial Use Modal (Same as before) -->
<div id="myModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                <div class="bg-gray-50 px-4 py-3 sm:px-6 flex justify-between items-center border-b border-gray-100">
                    <h3 class="text-lg font-bold leading-6 text-gray-900" id="modal-title">Information on commercial use</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-200">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="px-4 py-5 sm:p-6">
                    <p class="text-sm text-gray-600 mb-6">In Marketplace Ethiopia, we separate private from business: private and commercial users must meet different requirements.</p>
                    <div class="space-y-2">
                        <div class="border rounded-lg overflow-hidden">
                            <button class="w-full px-4 py-3 bg-white hover:bg-gray-50 text-left flex justify-between items-center transition-colors" onclick="toggleAccordion('accordion1')">
                                <span class="font-semibold text-gray-800 text-sm">When am I a commercial user?</span>
                                <svg id="icon1" class="h-5 w-5 text-gray-400 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div id="accordion1" class="max-h-0 overflow-hidden transition-all duration-300 ease-in-out bg-gray-50">
                                <div class="px-4 py-3 text-sm text-gray-600">
                                    <p class="mb-2">You are trading commercially on Marketplace Ethiopia if you:</p>
                                    <ul class="list-disc pl-5 space-y-1">
                                        <li>Buy or create items to sell</li>
                                        <li>Offer services</li>
                                        <li>Regularly offer large quantities of items</li>
                                        <li>Sell similar goods over a longer period of time</li>
                                        <li>Buy or sell for your company</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="border rounded-lg overflow-hidden">
                            <button class="w-full px-4 py-3 bg-white hover:bg-gray-50 text-left flex justify-between items-center transition-colors" onclick="toggleAccordion('accordion2')">
                                <span class="font-semibold text-gray-800 text-sm">Does this cost me anything?</span>
                                <svg id="icon2" class="h-5 w-5 text-gray-400 transform transition-transform duration-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div id="accordion2" class="max-h-0 overflow-hidden transition-all duration-300 ease-in-out bg-gray-50">
                                <div class="px-4 py-3 text-sm text-gray-600">
                                    <p>Posting Ad on Marketplace Ethiopia is basically free. Both commercial and private users can place ads free of charge.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="pb-10"></div>
@if (config('services.recaptcha.enabled', false))
<script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
@endif
<script>
    function showDiv(placeholderText) {
        document.getElementById('myDiv').classList.remove('hidden');
        document.getElementById('name').placeholder = placeholderText;
    }

    function hideDiv() {
        document.getElementById('myDiv').classList.add('hidden');
    }

    // Modal functions
    function showModal() {
        document.getElementById('myModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        document.getElementById('myModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Toggle password visibility
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.innerHTML = '<path fill-rule="evenodd" d="M10 3C5.4 3 1.73 6.11.4 10c1.33 3.89 5 7 9.6 7s8.27-3.11 9.6-7C18.27 6.11 14.6 3 10 3zM10 15a5 5 0 110-10 5 5 0 010 10zm-7.5-5a8.24 8.24 0 017.5-5 8.24 8.24 0 017.5 5 8.24 8.24 0 01-7.5 5 8.24 8.24 0 01-7.5-5z" clip-rule="evenodd"/>';
        } else {
            passwordInput.type = 'password';
            eyeIcon.innerHTML = '<path d="M10 3C5.4 3 1.73 6.11.4 10c1.33 3.89 5 7 9.6 7s8.27-3.11 9.6-7C18.27 6.11 14.6 3 10 3zM10 15a5 5 0 110-10 5 5 0 010 10zm0-8a3 3 0 100 6 3 3 0 000-6z"/>';
        }
    }

    // Toggle accordion items
    function toggleAccordion(id) {
        const content = document.getElementById(id);
        const icon = document.getElementById('icon' + id.slice(-1));

        if (content.style.maxHeight) {
            content.style.maxHeight = null;
            icon.classList.remove('rotate-180');
        } else {
            content.style.maxHeight = content.scrollHeight + "px";
            icon.classList.add('rotate-180');
        }
    }

    // Recaptcha submit handler
    document.addEventListener('DOMContentLoaded', function() {
        const registerBtn = document.getElementById('register-btn');
        const form = document.getElementById('register-form');

        registerBtn.addEventListener('click', function(e) {
            e.preventDefault();

            // Validate form first
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Check if account type is selected
            const accTypeSelected = document.querySelector('input[name="acc_type"]:checked');
            if (!accTypeSelected) {
                alert('Please select an account type (Private or Commercial)');
                return;
            }

            @if (config('services.recaptcha.enabled', false))
            // Check if reCAPTCHA is loaded
            if (typeof grecaptcha === 'undefined') {
                alert('Security check is loading. Please wait a moment and try again.');
                return;
            }

            // Disable button to prevent double submission
            registerBtn.disabled = true;
            registerBtn.innerHTML = '<svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

            // Execute reCAPTCHA
            grecaptcha.ready(function() {
                grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', {action: 'register'})
                    .then(function(token) {
                        // Remove any existing recaptcha response inputs
                        const existingInput = form.querySelector('input[name="g-recaptcha-response"]');
                        if (existingInput) {
                            existingInput.remove();
                        }

                        // Add token to form
                        let input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'g-recaptcha-response';
                        input.value = token;
                        form.appendChild(input);

                        // Submit form
                        form.submit();
                    })
                    .catch(function(error) {
                        console.error('reCAPTCHA error:', error);
                        alert('Security verification failed. Please refresh the page and try again.');
                        registerBtn.disabled = false;
                        registerBtn.innerHTML = 'Register for Free';
                    });
            });
            @else
            // reCAPTCHA disabled, submit form directly
            form.submit();
            @endif
        });
    });
</script>

<style>
    /* Hides the floating Google Recaptcha Badge */
    /* Only allowed because we added the legal text in the footer manually */
    .grecaptcha-badge {
        visibility: hidden;
    }
</style>

@include('public.layouts.footer')
