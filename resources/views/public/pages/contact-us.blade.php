@include('public.layouts.header')
@include('public.layouts.nav')
@include('public.components.mobile.mobile-nav')
@include('public.layouts.search')

<section>
    <div class="max-w-2xl mx-auto bg-white p-3 md:p-10 mt-4 md:mt-10 pb-20 mb-5 rounded-lg">
        <div class="flex flex-col text-center">
            <h3 class="text-2xl font-bold">Contact Us</h3>
            <h6>Fill out the form below to reach us.</h6>
        </div>

        @include('public.components.flash-message')

        <form method="POST" action="/contact-us" id="contactForm">
            @csrf

            <div class="mb-4 mt-4">
                @if ($errors->has('name'))
                    <span class="text-red-700 py-1">{{ $errors->first('name') }}</span>
                @endif
                <label class="text-sm font-semibold">Full Name *</label>
                <input type="text" name="name" placeholder="Full Name"
                    value="{{ old('name') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
            </div>

            <div class="mb-4 mt-4">
                @if ($errors->has('subject'))
                    <span class="text-red-700 py-1">{{ $errors->first('subject') }}</span>
                @endif
                <label class="text-sm font-semibold">Subject *</label>
                <input type="text" name="subject" placeholder="Subject"
                    value="{{ old('subject') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
            </div>

            <div class="mb-4 mt-4">
                @if ($errors->has('phone'))
                    <span class="text-red-700 py-1">{{ $errors->first('phone') }}</span>
                @endif
                <label class="text-sm font-semibold">Phone *</label>
                <input type="text" name="phone" placeholder="Phone"
                    value="{{ old('phone') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
            </div>

            <div class="mb-6 mt-4">
                @if ($errors->has('email'))
                    <span class="text-red-900 my-1">{{ $errors->first('email') }}</span>
                @endif
                <label class="text-sm font-semibold">Email *</label>
                <input type="email" name="email" placeholder="Email Address"
                    value="{{ old('email') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" required>
            </div>

            <div class="mb-4 mt-4">
                @if ($errors->has('message'))
                    <span class="text-red-700 py-1">{{ $errors->first('message') }}</span>
                @endif
                <label class="text-sm font-semibold">Message *</label>
                <textarea name="message" placeholder="Enter your Message..."
                    class="w-full px-3 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent min-h-[150px]" required>{{ old('message') }}</textarea>
            </div>

            @if ($errors->has('g-recaptcha-response'))
                <div class="mb-4 rounded-md bg-red-50 p-3">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">{{ $errors->first('g-recaptcha-response') }}</h3>
                        </div>
                    </div>
                </div>
            @endif

            <div class="mt-8">
                <button type="button"
                    id="contact-submit-btn"
                    class="flex justify-center items-center bg-transparent hover:bg-primary text-dark_green font-semibold hover:text-dark_green py-3 px-6 border-2 border-dark_green hover:border-dark_green rounded-full transition-all">
                    <span>Submit</span>
                    <span class="ml-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                            <path fill-rule="evenodd" d="M16.72 7.72a.75.75 0 0 1 1.06 0l3.75 3.75a.75.75 0 0 1 0 1.06l-3.75 3.75a.75.75 0 1 1-1.06-1.06l2.47-2.47H3a.75.75 0 0 1 0-1.5h16.19l-2.47-2.47a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                        </svg>
                    </span>
                </button>
            </div>

            <p class="text-xs text-center text-gray-500 mt-4 leading-relaxed">
                This site is protected by reCAPTCHA and the Google
                <a href="https://policies.google.com/privacy" class="text-dark_green hover:underline">Privacy Policy</a> and
                <a href="https://policies.google.com/terms" class="text-dark_green hover:underline">Terms of Service</a> apply.
            </p>
        </form>

        <div class="mt-2 text-sm">
            For direct support, email <a class="font-semibold" href="mailto:support@marketplace.et">support@marketplace.et</a> or <a href="https://wa.me/{{ preg_replace('/\D/', '', config('global.site_phone')) }}" target="_blank" class="text-gray-700 hover:text-black rounded-md font-semibold">contact us on WhatsApp</a>
        </div>
    </div>
</section>

<script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const submitBtn = document.getElementById('contact-submit-btn');
        const form = document.getElementById('contactForm');

        submitBtn.addEventListener('click', function(e) {
            e.preventDefault();

            // Validate form first
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Check if reCAPTCHA is loaded
            if (typeof grecaptcha === 'undefined') {
                alert('Security check is loading. Please wait a moment and try again.');
                return;
            }

            // Disable button to prevent double submission
            submitBtn.disabled = true;
            const originalContent = submitBtn.innerHTML;
            submitBtn.innerHTML = '<svg class="animate-spin h-5 w-5 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>';

            // Execute reCAPTCHA v3
            grecaptcha.ready(function() {
                grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', {action: 'contact'})
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
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalContent;
                    });
            });
        });
    });
</script>

<style>
    /* Hides the floating Google Recaptcha Badge */
    .grecaptcha-badge {
        visibility: hidden;
    }
</style>

@include('public.layouts.footer')
