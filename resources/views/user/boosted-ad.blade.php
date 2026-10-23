@include('user.layouts.header')
@include('user.layouts.nav')
@include('user.layouts.back-nav')
@include('user.layouts.search')

<section class="w-full md:w-3/6 bg-white mx-auto p-3 md:p-6 text-sm">
    <div class="border-b-2 border-b-gray-200 pt-4 px-2 font-bold text-dark_green text-lg md:text-xl">
        Boosted Advert
        @include('public.components.flash-message')
    </div>

    <div class="mt-6 bg-white rounded-lg shadow-sm">
        <div class="max-w-2xl mx-auto p-6 mb-10">
            <form method="POST" action="/boost/pay">
                @csrf
                <div class="font-semibold text-lg md:text-xl text-gray-800">Ad Details</div>

                <div class="mb-6 mt-4 flex flex-col md:flex-row gap-4">
                    <img class="h-24 w-24 md:h-40 md:w-40 object-cover rounded-lg" src="{{ $advert->getFirstMediaUrl('images', 'thumbnail') }}">
                    <div class="flex flex-col justify-center">
                        <div class="font-medium text-base md:text-xl text-gray-900">{{ $advert->ad_title }}</div>
                        <div class="text-sm text-gray-500 mt-1">Ad ID: {{ $advert->ad_id }}</div>
                    </div>
                </div>

                <!-- Boost Records Section -->
                <div class="mt-8">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">Boost History</h3>

                    @if($advert->boost->isEmpty())
                        <div class="bg-gray-50 p-4 rounded-lg text-center text-gray-500">
                            No active boosts found for this advert.
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($advert->boost as $boost)
                                @php
                                    $endDate = \Carbon\Carbon::parse($boost->start_date)->addDays($boost->duration)->startOfDay();
                                    $remainingDays = max(0, \Carbon\Carbon::now()->startOfDay()->diffInDays($endDate, false));
                                @endphp

                                <div class="border border-gray-200 rounded-lg p-4 hover:bg-gray-50 transition-colors">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <span class="font-medium">Boost ID:</span> {{ $boost->id }}
                                        </div>
                                        <span class="px-2 py-1 text-xs rounded-full
                                            {{ $boost->boost_status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}
                                            {{ $boost->boost_status === 'completed' ? 'bg-blue-100 text-blue-800' : '' }}
                                            {{ $boost->boost_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                            {{ ucfirst($boost->boost_status) }}
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mt-3 text-sm">
                                        <div>
                                            <span class="text-gray-500">Start Date:</span>
                                            <span class="font-medium">{{ date('j F Y', strtotime($boost->start_date)) }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Duration:</span>
                                            <span class="font-medium">{{ $boost->duration }} Days</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">End Date:</span>
                                            <span class="font-medium">{{ $endDate->format('j F Y') }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Remaining:</span>
                                            <span class="font-medium {{ $remainingDays === 0 ? 'text-green-600' : 'text-dark_green' }}">
                                                {{ $remainingDays === 0 ? 'Completed' : $remainingDays . ' days left' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Add action buttons if needed -->

            </form>
        </div>
    </div>
</section>

@include('user.layouts.footer')
