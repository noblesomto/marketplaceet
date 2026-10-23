@include('public.layouts.header')

<div class="min-h-screen bg-gray-50 flex flex-col">

    {{-- Header --}}
    <header class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="/"><img src="{{ asset('frontend/images/logo.png') }}" class="h-8" alt="Marketplace Ethiopia"></a>
            <a href="/shipper/logout"
               class="text-sm text-red-600 hover:text-red-700 font-medium flex items-center gap-1">
                <i class="bi bi-box-arrow-right"></i> Log Out
            </a>
        </div>
    </header>

    {{-- Body --}}
    <main class="flex-1 flex items-center justify-center px-4 py-12">
        <div class="w-full max-w-md">

            {{-- Icon + title --}}
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-green-100 mb-4">
                    <i class="bi bi-box-seam text-3xl text-green-700"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Verify Shipment</h1>
                <p class="text-gray-500 text-sm mt-1">Enter the 10-digit shipping code to view order details</p>
            </div>

            {{-- Alerts --}}
            @if (session('error'))
                <div class="mb-4 flex items-start gap-2 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
                    <i class="bi bi-exclamation-circle-fill mt-0.5"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
                    @foreach ($errors->all() as $error)
                        <p class="flex items-center gap-1"><i class="bi bi-exclamation-circle-fill"></i> {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            {{-- Form card --}}
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6">
                <form method="POST" action="/shipper/get-shipping">
                    @csrf

                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Shipping Code <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="text"
                            name="ship_code"
                            value="{{ strtoupper(old('ship_code')) }}"
                            placeholder="e.g. EMXTUQARWM"
                            maxlength="10"
                            autocomplete="off"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm font-mono uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('ship_code') border-red-400 @enderror"
                            required
                        >
                        <p class="text-xs text-gray-400 mt-1">10 characters — letters and numbers only</p>
                    </div>

                    <button type="submit"
                        class="w-full bg-green-700 hover:bg-green-800 text-white font-semibold py-3 px-6 rounded-lg text-sm transition-colors flex items-center justify-center gap-2">
                        <i class="bi bi-search"></i>
                        Look Up Order
                    </button>
                </form>
            </div>

        </div>
    </main>

</div>

@include('public.layouts.footer')
