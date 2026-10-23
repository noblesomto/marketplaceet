@include('public.layouts.header')

<div class="bg-white min-h-screen">
    <section class="w-full flex justify-center items-center h-24 border-b border-gray-200 shadow-sm">
        <a href="/">
            <img src="{{ asset('frontend/images/logo.png') }}" alt="{{ config('app.name') }}" class="h-9">
        </a>
    </section>

    <section class="w-full md:w-2/6 mx-auto bg-white p-6 md:p-8 mt-1 lg:mt-10 rounded-xl shadow-sm">
        <div class="w-full mx-auto">
            @include('public.components.flash-message')
            
            <form method="POST" action="/authenticate">
                @csrf
                <div class="mb-6">
                    <h2 class="font-bold text-2xl md:text-3xl text-gray-800">Enter 6 Digits OTP</h2>
                    <p class="text-gray-500 mt-2">We've sent a verification code to your device</p>
                </div>

                <div class="mb-6">
                    @if ($errors->has('otp'))
                        <span class="text-red-600 text-sm">{{ $errors->first('otp') }}</span>
                    @endif
                    <label class="block text-gray-700 mb-2">Enter OTP *</label>
                    <input type="number" id="otp" name="otp" placeholder="Enter 6 digits OTP" 
                           class="w-full px-4 py-3 text-base md:text-lg border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           inputmode="numeric"
                           pattern="[0-9]*"
                           maxlength="6"
                           required>
                </div>

                <div class="mt-8">
                    <button type="submit" class="w-full bg-secondary-200 hover:bg-secondary-100 text-lg text-dark_green font-bold py-3 px-4 rounded-full flex justify-center items-center transition-colors">
                        <span>Verify OTP</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 ml-2">
                            <path fill-rule="evenodd" d="M16.72 7.72a.75.75 0 0 1 1.06 0l3.75 3.75a.75.75 0 0 1 0 1.06l-3.75 3.75a.75.75 0 1 1-1.06-1.06l2.47-2.47H3a.75.75 0 0 1 0-1.5h16.19l-2.47-2.47a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>

                <div class="mt-6 flex justify-center text-base text-gray-600">
                    OTP not received?
                    <form method="POST" action="{{ route('resend.otp') }}" style="display: inline;">
                        @csrf
                        <button type="submit" class="ml-2 font-semibold text-blue-600 hover:text-blue-800 underline bg-transparent border-0 cursor-pointer hover:underline">
                            Resend OTP
                        </button>
                    </form>
                </div>
            </form>
        </div>
    </section>
</div>

@include('public.layouts.footer')

<style>
    /* Prevent zooming on input focus in mobile devices */
    input[type="number"] {
        font-size: 16px !important;
    }
    
    /* Remove number input spinners */
    input::-webkit-outer-spin-button,
    input::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    
    input[type=number] {
        -moz-appearance: textfield;
    }
</style>