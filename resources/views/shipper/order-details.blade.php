@include('public.layouts.header')

@php
    $seller    = $ship->advert->user ?? null;
    $imgUrl    = $ship->advert->getFirstMediaUrl('images', 'thumbnail');
    $city      = $ship->cityLocation;
    $stateName = $city->state->name ?? ($ship->state ?? '—');
    $statusMap = [
        'pending'   => ['label' => 'Pending',          'color' => 'bg-yellow-100 text-yellow-700'],
        'shipped'   => ['label' => 'Shipped',           'color' => 'bg-blue-100 text-blue-700'],
        'pickup'    => ['label' => 'Ready for Pickup',  'color' => 'bg-purple-100 text-purple-700'],
        'delivered' => ['label' => 'Delivered',         'color' => 'bg-green-100 text-green-700'],
        'canceled'  => ['label' => 'Canceled',          'color' => 'bg-red-100 text-red-700'],
    ];
    $currentStatus = $ship->shipping_status ?? 'pending';
    $badge = $statusMap[$currentStatus] ?? $statusMap['pending'];
@endphp

<div class="min-h-screen bg-gray-50 flex flex-col">

    {{-- Header --}}
    <header class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-5xl mx-auto px-4 py-4 flex items-center justify-between">
            <a href="/"><img src="{{ asset('frontend/images/logo.png') }}" class="h-8" alt="Marketplace Ethiopia"></a>
            <div class="flex items-center gap-4">
                <a href="/shipper/index"
                   class="text-sm text-green-700 hover:text-green-800 font-medium flex items-center gap-1">
                    <i class="bi bi-search"></i> Check Another Code
                </a>
                <a href="/shipper/logout"
                   class="text-sm text-red-600 hover:text-red-700 font-medium flex items-center gap-1">
                    <i class="bi bi-box-arrow-right"></i> Log Out
                </a>
            </div>
        </div>
    </header>

    <main class="flex-1 max-w-5xl mx-auto w-full px-4 py-8">

        {{-- Page title + status --}}
        <div class="flex items-center justify-between mb-6 flex-wrap gap-2">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Order Details</h1>
                <p class="text-sm text-gray-500">Shipping code: <span class="font-mono font-semibold text-gray-700">{{ $ship->ship_code }}</span></p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $badge['color'] }}">
                {{ $badge['label'] }}
            </span>
        </div>

        {{-- Alerts --}}
        @if (session('success'))
            <div class="mb-5 flex items-center gap-2 bg-green-50 border border-green-200 text-green-700 rounded-lg px-4 py-3 text-sm">
                <i class="bi bi-check-circle-fill"></i>
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-5 flex items-center gap-2 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
                <i class="bi bi-exclamation-circle-fill"></i>
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

            {{-- LEFT COLUMN --}}
            <div class="space-y-5">

                {{-- Product --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-2">
                        <i class="bi bi-bag text-green-700"></i>
                        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Item</h2>
                    </div>
                    <div class="p-5 flex items-center gap-4">
                        @if ($imgUrl)
                            <img src="{{ $imgUrl }}" alt="{{ $ship->advert->ad_title }}"
                                 class="w-20 h-20 rounded-lg object-cover flex-shrink-0 border border-gray-100">
                        @else
                            <div class="w-20 h-20 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                                <i class="bi bi-image text-gray-400 text-2xl"></i>
                            </div>
                        @endif
                        <div>
                            <p class="font-semibold text-gray-800 text-sm">{{ $ship->advert->ad_title }}</p>
                            <p class="text-xs text-gray-500 mt-0.5">Sold item</p>
                        </div>
                    </div>
                </div>

                {{-- Sender (Seller) --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-2">
                        <i class="bi bi-person-check text-green-700"></i>
                        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Sender (Seller)</h2>
                    </div>
                    <div class="p-5 space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Name</span>
                            <span class="font-medium text-gray-800">{{ $seller->name ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Phone</span>
                            <a href="tel:{{ $seller->phone ?? '' }}" class="font-medium text-green-700">{{ $seller->phone ?? '—' }}</a>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">State</span>
                            <span class="font-medium text-gray-800">{{ $seller->state ?? '—' }}</span>
                        </div>
                    </div>
                </div>

                {{-- Receiver (Buyer) --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-2">
                        <i class="bi bi-person text-green-700"></i>
                        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Receiver (Buyer)</h2>
                    </div>
                    <div class="p-5 space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Name</span>
                            <span class="font-medium text-gray-800">{{ $ship->first_name }} {{ $ship->last_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Phone</span>
                            <a href="tel:{{ $ship->phone }}" class="font-medium text-green-700">{{ $ship->phone }}</a>
                        </div>
                    </div>
                </div>

            </div>

            {{-- RIGHT COLUMN --}}
            <div class="space-y-5">

                {{-- Delivery Location --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-2">
                        <i class="bi bi-geo-alt text-green-700"></i>
                        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Delivery / Pickup Location</h2>
                    </div>
                    <div class="p-5 space-y-3 text-sm">
                        @if ($ship->shipping)
                            <div class="flex items-center gap-3 pb-2 border-b border-gray-100">
                                @if ($ship->shipping->logo)
                                    <img src="{{ Str::startsWith($ship->shipping->logo, 'http') ? $ship->shipping->logo : asset('uploads/shipping/'.$ship->shipping->logo) }}"
                                         class="h-7 object-contain" alt="{{ $ship->shipping->company }}">
                                @endif
                                <span class="font-semibold text-gray-800">{{ $ship->shipping->company }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-gray-500">Address</span>
                            <span class="font-medium text-gray-800 text-right max-w-[60%]">{{ $city->address ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">City</span>
                            <span class="font-medium text-gray-800">{{ $city->city ?? '—' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">State</span>
                            <span class="font-medium text-gray-800">{{ $stateName }}</span>
                        </div>
                    </div>
                </div>

                {{-- Current tracking --}}
                @if ($ship->tracking_id)
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-2">
                            <i class="bi bi-truck text-green-700"></i>
                            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Current Tracking</h2>
                        </div>
                        <div class="p-5 space-y-3 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Tracking ID</span>
                                <span class="font-mono font-semibold text-gray-800">{{ $ship->tracking_id }}</span>
                            </div>
                            @if ($ship->shipping_status_date)
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Last Updated</span>
                                    <span class="font-medium text-gray-800">{{ \Carbon\Carbon::parse($ship->shipping_status_date)->format('d M Y, H:i') }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Update form --}}
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-100 flex items-center gap-2">
                        <i class="bi bi-pencil-square text-green-700"></i>
                        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">Update Shipping Status</h2>
                    </div>
                    <div class="p-5">
                        <form method="POST" action="/shipper/update-shipping/{{ $ship->ship_code }}">
                            @csrf

                            @if ($errors->any())
                                <div class="mb-4 bg-red-50 border border-red-200 text-red-700 rounded-lg px-4 py-3 text-sm">
                                    @foreach ($errors->all() as $error)
                                        <p>{{ $error }}</p>
                                    @endforeach
                                </div>
                            @endif

                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                    Tracking ID <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="tracking_id"
                                    value="{{ old('tracking_id', $ship->tracking_id) }}"
                                    placeholder="Enter carrier tracking number"
                                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                                    required>
                            </div>

                            <div class="mb-5">
                                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                                    Shipping Status <span class="text-red-500">*</span>
                                </label>
                                <select name="shipping_status"
                                    class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    <option value="pending"   {{ $currentStatus === 'pending'   ? 'selected' : '' }}>⏳ Pending</option>
                                    <option value="shipped"   {{ $currentStatus === 'shipped'   ? 'selected' : '' }}>📦 Shipped</option>
                                    <option value="pickup"    {{ $currentStatus === 'pickup'    ? 'selected' : '' }}>🏪 Ready for Pickup</option>
                                    <option value="delivered" {{ $currentStatus === 'delivered' ? 'selected' : '' }}>✅ Delivered</option>
                                    <option value="canceled"  {{ $currentStatus === 'canceled'  ? 'selected' : '' }}>❌ Canceled</option>
                                </select>
                            </div>

                            <button type="submit"
                                class="w-full bg-green-700 hover:bg-green-800 text-white font-semibold py-2.5 px-6 rounded-lg text-sm transition-colors flex items-center justify-center gap-2">
                                <i class="bi bi-check-lg"></i>
                                Update Status
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </main>

</div>

@include('public.layouts.footer')
