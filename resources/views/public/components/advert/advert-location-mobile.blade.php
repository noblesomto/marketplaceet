<div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-all duration-200 h-full flex flex-col">
    <a href="{{ url($row->state_slug . '/' . $row->title_slug .'/'. $row->ad_id) }}" class="block group h-full flex flex-col">
        <!-- Image -->
        <div class="h-[180px] w-full overflow-hidden relative">
            @php
                $image = $row->cardImage();
                $thumbSmUrl = $image && $image->hasGeneratedConversion('thumb-sm')
                    ? $image->getUrl('thumb-sm')
                    : ($image ? $image->getUrl('thumbnail') : asset('frontend/images/default.png'));
                $thumbnailUrl = $image ? $image->getUrl('thumbnail') : asset('frontend/images/default.png');

                // Mobile optimization: First 4 items (2 rows) are above the fold
                $isAboveFold = isset($loop) && $loop->index < 4;
            @endphp

            @if($isAboveFold)
                {{-- Above-the-fold images on mobile: Eager load with priority --}}
                <img
                    src="{{ $thumbSmUrl }}"
                    alt="{{ $row->ad_title }}"
                    class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                    width="200"
                    height="150"
                    @if($loop->index < 2)
                        fetchpriority="high"
                    @endif
                    @if($image)
                        srcset="{{ $thumbnailUrl }} 200w, {{ $thumbSmUrl }} 400w"
                        sizes="50vw"
                    @endif
                />
            @else
                {{-- Below-the-fold images: Lazy load --}}
                <img
                    src="{{ $thumbSmUrl }}"
                    alt="{{ $row->ad_title }}"
                    class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                    width="200"
                    height="150"
                    loading="lazy"
                    @if($image)
                        srcset="{{ $thumbnailUrl }} 200w, {{ $thumbSmUrl }} 400w"
                        sizes="50vw"
                    @endif
                />
            @endif

            <div class="absolute top-1 right-1 space-y-1">
                @if(optional($row->owner)->verified=='yes')
                    <div class="bg-green-50 px-0.5 rounded text-[14px]">
                        <span title="verified User">
                            <i class="bi bi-patch-check-fill text-secondary_dark"></i>
                        </span>
                    </div>
                @endif
                @if($row->views >= setViews())
                    <div class="bg-white opacity-8 flex space-x-2 py-1 px-2 rounded text-[13px]">
                        <span title="Popular Ad">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-fire" viewBox="0 0 16 16">
                                <path d="M8 16c3.314 0 6-2 6-5.5 0-1.5-.5-4-2.5-6 .25 1.5-1.25 2-1.25 2C11 4 9 .5 6 0c.357 2 .5 4-2 6-1.25 1-2 2.729-2 4.5C2 14 4.686 16 8 16m0-1c-1.657 0-3-1-3-2.75 0-.75.25-2 1.25-3C6.125 10 7 10.5 7 10.5c-.375-1.25.5-3.25 2-3.5-.179 1-.25 2 1 3 .625.5 1 1.364 1 2.25C11 14 9.657 15 8 15"/>
                            </svg>
                        </span>
                    </div>
                @endif
            </div>
            <div class="absolute top-1 left-2">
                @if ($row->featured == 'Yes')
                    <div class="bg-gray-50 inline-block px-1 py-0.25 rounded text-[12px]" title="Boosted Ad">
                        <span>
                            <i class="bi bi-rocket-takeoff"></i>
                        </span>
                        <span class="font-semibold">Boost</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Content -->
        <div class="p-3 flex flex-col flex-grow">
            <!-- Location & Sold Status -->
            <div class="flex justify-between items-start mb-1">
                <div class="flex gap-2">
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                          <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                    </span>
                    <span class="text-xs text-gray-500 truncate">{{ $row->lga }}</span>
                </div>
                @if($row->sold=="Yes")
                <span class="flex items-center gap-1 bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs" title="Sold">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span>Sold</span>
                </span>
                @endif
            </div>

            <!-- Title -->
            <h2 class="text-sm font-semibold text-gray-800 line-clamp-2 leading-tight mb-2">
                {{ $row->ad_title }}
            </h2>

            @if($row->category==3)
            <span class="text-sm font-bold text-green-800">
                {{ $row->salary }}
            </span>
            @elseif($row->category==18)
                <span class="text-sm font-bold text-green-800">
                    {{ $row->expected_salary }}
                </span>
            @elseif($row->contact_price=="yes")
                <span class="text-sm font-bold text-green-800">
                    Contact For Price
                </span>
            @else
                <!-- Price -->
                <div class="mt-auto">
                    <div class="flex justify-between items-center gap-4 mb-1">
                        <span class="text-sm font-bold text-green-800">
                            Br {{ number_format($row->price, 0, '.', ',') }}
                        </span>
                        <span class="text-xs text-gray-500 truncate">
                            {{ $row->price_type }}
                        </span>
                    </div>

                    <div class="flex flex-col md:flex-row md:items-center md:justify-between my-2 gap-1 md:gap-0">
                        <!-- Buy Direct Badge -->
                        @if($row->buy_direct == "Yes")
                            <div class="flex items-center bg-blue-50 rounded-full px-2 py-1 w-fit">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                    stroke="currentColor" class="w-3 h-3 text-blue-600 mr-1">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                                </svg>
                                <span class="text-xs text-blue-600">Buy Direct</span>
                            </div>
                        @endif
                    </div>
                </div>

            @endif

        </div>
    </a>
</div>
