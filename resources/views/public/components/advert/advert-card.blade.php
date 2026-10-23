
<div class="group relative h-full flex flex-col">
    <div class="bg-white rounded-lg shadow-md group-hover:shadow-lg border border-gray-200 flex flex-col h-full">

        <!-- Image wrapper: outer div is the position context (no overflow-hidden so stars can hang below) -->
        <div class="w-full h-[200px] lg:h-[220px] relative">
            @php
                $image = $row->cardImage();
            @endphp

            <!-- Inner div clips the image to rounded top corners -->
            <div class="absolute inset-0 overflow-hidden rounded-t-lg">
                <img
                    src="{{ $image
                        ? ($image->hasGeneratedConversion('thumb-md')
                            ? $image->getUrl('thumb-md')
                            : $image->getUrl('thumbnail'))
                        : asset('frontend/images/default.png') }}"
                    alt="{{ $row->ad_title }}"
                    width="800" height="600"
                    loading="lazy"
                    decoding="async"
                    class="w-full h-full object-cover"
                />

                <div class="absolute top-1 right-1 flex space-x-2">
                    @if(optional($row->owner)->verified=='yes')
                        <div class="bg-green-50  px-1 rounded text-[14px]">
                            <span title="verified User">
                                <i class="bi bi-patch-check-fill text-secondary_dark"></i>
                            </span>
                        </div>
                    @endif
                    @if($row->views >= setViews())
                    <div class="bg-white opacity-8 flex space-x-2 py-1 px-2 rounded text-[13px]">
                        <span title="Popuplar Ad">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-fire" viewBox="0 0 16 16">
                                <path d="M8 16c3.314 0 6-2 6-5.5 0-1.5-.5-4-2.5-6 .25 1.5-1.25 2-1.25 2C11 4 9 .5 6 0c.357 2 .5 4-2 6-1.25 1-2 2.729-2 4.5C2 14 4.686 16 8 16m0-1c-1.657 0-3-1-3-2.75 0-.75.25-2 1.25-3C6.125 10 7 10.5 7 10.5c-.375-1.25.5-3.25 2-3.5-.179 1-.25 2 1 3 .625.5 1 1.364 1 2.25C11 14 9.657 15 8 15"/>
                            </svg>
                        </span>
                    </div>
                    @endif
                </div>
                <div class="absolute top-1 left-2">
                    @if ($row->featured == 'Yes')
                        <div class="bg-gray-50 inline-block px-1 py-0.5 rounded text-[12px]" title="Boosted Ad">
                            <span>
                                <i class="bi bi-rocket-takeoff"></i>
                            </span>
                            <span class="font-semibold">Boost</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Stars button: absolute within the outer (non-clipping) wrapper.
                 bottom: -38px positions the button so its top is 2px above the image's
                 bottom edge — the button hangs 38px below the image, 2px inside it. --}}
            <a href="/related/{{ $row->ad_id }}"
               class="absolute z-[2] left-2 w-8 h-8
                      flex items-center justify-center
                      rounded-full bg-black/20 backdrop-blur-sm
                      shadow-md hover:bg-black/60 transition"
               style="bottom: 10px">
                <i class="bi bi-stars text-2xl text-yellow-100"></i>
            </a>
        </div>

        <!-- Price badge -->
        <div class="relative -mt-10 mb-2 flex justify-end">
            <div class="price-badge">
                @if($row->category==3)
                    <div class="bg-secondary_dark text-white h-8 px-2 text-sm font-semibold inline-flex items-center">
                        {{ $row->salary }}
                    </div>
                @elseif($row->category==18)
                    <div class="bg-secondary_dark text-white h-8 px-2 text-sm font-semibold inline-flex items-center">
                        {{ $row->expected_salary }}
                    </div>
                @elseif($row->contact_price=="yes")
                    <div class="bg-secondary_dark text-white h-8 px-2 text-sm font-semibold inline-flex items-center">
                        Contact For Price
                    </div>
                @else
                    <div class="bg-secondary_dark text-white h-8 px-2 text-sm font-semibold inline-flex items-center">
                        Br  {{ number_format($row->price, 0, '.', ',') }} {{ Str::limit($row->price_type, 1) }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Content -->
        <div class="p-3 flex flex-col flex-grow space-y-2">
            <h1 class="font-bold text-sm">
                {{-- Stretched link: ::after covers the entire card via position:absolute inset-0,
                     making the whole card clickable without wrapping everything in <a>. --}}
                <a href="{{ url($row->state_slug . '/' . $row->title_slug .'/'. $row->ad_id) }}"
                   class="after:absolute after:inset-0 after:content-[''] after:z-[1]">
                    {{ Str::limit($row->ad_title, 50) }}
                </a>
            </h1>
            <div class="flex items-center justify-between text-xs mt-auto">
                <div class="text-gray-500 truncate flex">
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                          <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                    </span>
                    <p class="text-xs text-gray-500 truncate">{{ $row->state }}, {{ $row->lga }}</p>
                </div>
            </div>

            <div class="flex items-center justify-between">
                @if($row->buy_direct=="Yes")
                <div class="flex items-center  bg-blue-50 rounded-full px-2 py-1 w-fit">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-3 h-3 text-blue-600 mr-1">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                    <span class="text-xs text-blue-600">Buy Direct</span>
                </div>
                @endif

                @if($row->sold=="Yes")
                <span class="flex items-center gap-1 bg-red-100 text-red-800 px-2 py-1 rounded cursor-not-allowed" title="This advert is already sold">
                    <span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </span>
                    <span class="font-semibold text-xs">Sold</span>
                </span>
                @endif
            </div>
        </div>
    </div>
</div>
