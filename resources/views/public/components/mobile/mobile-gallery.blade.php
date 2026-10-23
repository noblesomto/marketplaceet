
<!-- Discover what's trending Section -->
<section class="bg-white px-2">
    <div class="flex justify-start items-center ml-2 my-1 text-gray-500">
        <span>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 8.25V6a2.25 2.25 0 0 0-2.25-2.25H6A2.25 2.25 0 0 0 3.75 6v8.25A2.25 2.25 0 0 0 6 16.5h2.25m8.25-8.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-7.5A2.25 2.25 0 0 1 8.25 18v-1.5m8.25-8.25h-6a2.25 2.25 0 0 0-2.25 2.25v6" />
            </svg>
        </span>
        <span>Discover what's trending</span>
    </div>

    <div class="flex overflow-x-auto space-x-2 px-1 pb-5 snap-x snap-proximity scrollbar-hide overscroll-x-contain">
        @foreach ($gallery as $row)
        <div class="relative flex-none w-[45%] md:w-1/4 snap-start">
            <div class="relative overflow-hidden">
                @php
                    $media = $row->cardImage();
                    $thumbSmUrl = $media && $media->hasGeneratedConversion('thumb-sm')
                        ? $media->getUrl('thumb-sm')
                        : ($media ? $media->getUrl('thumbnail') : asset('frontend/images/default.png'));
                    $thumbnailUrl = $media ? $media->getUrl('thumbnail') : asset('frontend/images/default.png');
                @endphp

                @if($loop->index < 3)
                    {{-- First 3 images: Eager load with high priority --}}
                    <img
                        alt="{{ $row->ad_title }}"
                        class="w-full h-[160px] object-cover transition duration-300 ease-in-out hover:scale-110 rounded-lg"
                        width="300"
                        height="262"
                        @if($loop->first)
                            fetchpriority="high"
                        @endif
                        @if($media)
                            srcset="{{ $thumbnailUrl }} 400w, {{ $thumbSmUrl }} 600w"
                            sizes="(max-width: 768px) 45vw, 25vw"
                        @endif
                        src="{{ $thumbSmUrl }}"
                        onerror="this.onerror=null;this.src='{{ asset('frontend/images/default.png') }}';"
                    />
                @else
                    {{-- Remaining images: Lazy load --}}
                    <img
                        alt="{{ $row->ad_title }}"
                        class="w-full h-[160px] object-cover transition duration-300 ease-in-out hover:scale-110 rounded-lg"
                        width="300"
                        height="262"
                        loading="lazy"
                        @if($media)
                            srcset="{{ $thumbnailUrl }} 400w, {{ $thumbSmUrl }} 600w"
                            sizes="(max-width: 768px) 45vw, 25vw"
                        @endif
                        src="{{ $thumbSmUrl }}"
                        onerror="this.onerror=null;this.src='{{ asset('frontend/images/default.png') }}';"
                    />
                @endif

                <div class="absolute top-2 right-2 space-y-1">
                    @if(optional($row->owner)->verified=='yes')
                        <div class="bg-green-50 px-1 rounded text-[14px]">
                            <span title="verified User">
                                <i class="bi bi-patch-check-fill text-secondary_dark"></i>
                            </span>
                        </div>
                    @endif
                    @if($row->views >= setViews())
                    <div class="bg-white opacity-8 flex space-x-2 py-1 px-2 rounded text-[13px]">
                        <span title="Popular Ad">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-fire" viewBox="0 0 16 16">
                                <path d="M8 16c3.314 0 6-2 6-5.5 0-1.5-.5-4-2.5-6 .25 1.5-1.25 2-1.25 2C11 4 9 .5 6 0c.357 2 .5 4-2 6-1.25 1-2 2.729-2 4.5C2 14 4.686 16 8 16m0-1c-1.657 0-3-1-3-2.75 0-.75.25-2 1.25-3C6.125 10 7 10.5 7 10.5c-.375-1.25.5-3.25 2-3.5-.179 1-.25 2 1 3 .625.5 1 1.364 1 2.25C11 14 9.657 15 8 15"/>
                            </svg>
                        </span>
                    </div>
                    @endif
                </div>
                <div class="absolute top-2 left-2">
                    @if ($row->featured == 'Yes')
                        <div class="bg-gray-50 inline-block px-2 py-1 rounded text-[12px]" title="Boosted Ad">
                            <span><i class="bi bi-rocket-takeoff"></i></span>
                            <span class="font-semibold">Boost</span>
                        </div>
                    @endif
                </div>

                {{-- Related tag: z-[2] sits above the stretched card link (z-[1]) --}}
                <a href="/related/{{ $row->ad_id }}" class="absolute bottom-1 left-2 z-[2] w-10 h-10
                  flex items-center justify-center
                  rounded-full
                  bg-black/20 backdrop-blur-sm
                  shadow-md
                  hover:bg-black/60 transition">
                    <i class="bi bi-stars text-xl text-yellow-100"></i>
                </a>
            </div>

            <p class="text-sm text-gray-600 leading-4 mt-1">
                {{-- Stretched link: ::after covers the card container, making the whole card clickable --}}
                <a href="{{ url($row->state_slug . '/' . $row->title_slug .'/'. $row->ad_id) }}"
                   class="after:absolute after:inset-0 after:content-[''] after:z-[1]">
                    {{ Str::limit($row->ad_title, 40) }}
                </a>
            </p>
            <div class="flex justify-between">
                @if($row->category==3)
                <span class="text-sm font-bold text-green-600">{{ $row->salary }}</span>
                @elseif($row->category==18)
                    <span class="text-sm font-bold text-green-600">{{ $row->expected_salary }}</span>
                @elseif($row->contact_price=="yes")
                    <span class="text-sm font-bold text-green-600">Contact For Price</span>
                @else
                <span class="text-xs font-semibold text-dark_green">Br  {{ number_format($row->price, 0, '.', ',') }} {{ Str::limit($row->price_type, 10) }}</span>
                @endif
            </div>
            @if($row->buy_direct=="Yes")
            <div class="flex items-center mt-1">
                <span class="mr-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-4 accent-bg_primary">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                </span>
                <span>Buy Direct</span>
            </div>
            @endif
        </div>
        @endforeach
    </div>
</section>

<!-- Vehicles Section -->
<section class="bg-white px-2">
    <div class="flex justify-between">
        <div class="flex justify-start items-center ml-2 my-1 text-gray-500">
            <span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 8.25V6a2.25 2.25 0 0 0-2.25-2.25H6A2.25 2.25 0 0 0 3.75 6v8.25A2.25 2.25 0 0 0 6 16.5h2.25m8.25-8.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-7.5A2.25 2.25 0 0 1 8.25 18v-1.5m8.25-8.25h-6a2.25 2.25 0 0 0-2.25 2.25v6" />
                </svg>
            </span>
            <span>Vehicles</span>
        </div>
        <a href="/category/vehicles">
            <div class="flex justify-start items-center ml-2 my-1 text-dark_green font-semibold">
                <span>See all</span>
                <span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </span>
            </div>
        </a>
    </div>

    <div class="flex overflow-x-auto space-x-2 px-1 pb-5 snap-x snap-proximity scrollbar-hide overscroll-x-contain">
        @foreach ($cars as $row)
        <div class="relative flex-none w-[45%] md:w-1/4 snap-start">
            <div class="relative overflow-hidden">
                @php
                    $media = $row->cardImage();
                    $thumbSmUrl = $media && $media->hasGeneratedConversion('thumb-sm')
                        ? $media->getUrl('thumb-sm')
                        : ($media ? $media->getUrl('thumbnail') : asset('frontend/images/default.png'));
                    $thumbnailUrl = $media ? $media->getUrl('thumbnail') : asset('frontend/images/default.png');
                @endphp

                <img
                    alt="{{ $row->ad_title }}"
                    class="w-full h-[160px] object-cover transition duration-300 ease-in-out hover:scale-110 rounded-lg"
                    width="300"
                    height="262"
                    loading="lazy"
                    @if($media)
                        srcset="{{ $thumbSmUrl }} 300w, {{ $thumbnailUrl }} 150w"
                        sizes="(max-width: 768px) 45vw, 25vw"
                    @endif
                    src="{{ $thumbSmUrl }}"
                    onerror="this.onerror=null;this.src='{{ asset('frontend/images/default.png') }}';"
                />

                <div class="absolute top-2 right-2 space-y-1">
                    @if(optional($row->owner)->verified=='yes')
                        <div class="bg-green-50 px-1 rounded text-[14px]">
                            <span title="verified User">
                                <i class="bi bi-patch-check-fill text-secondary_dark"></i>
                            </span>
                        </div>
                    @endif
                    @if($row->views >= setViews())
                    <div class="bg-white opacity-8 flex space-x-2 py-1 px-2 rounded text-[13px]">
                        <span title="Popular Ad">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-fire" viewBox="0 0 16 16">
                                <path d="M8 16c3.314 0 6-2 6-5.5 0-1.5-.5-4-2.5-6 .25 1.5-1.25 2-1.25 2C11 4 9 .5 6 0c.357 2 .5 4-2 6-1.25 1-2 2.729-2 4.5C2 14 4.686 16 8 16m0-1c-1.657 0-3-1-3-2.75 0-.75.25-2 1.25-3C6.125 10 7 10.5 7 10.5c-.375-1.25.5-3.25 2-3.5-.179 1-.25 2 1 3 .625.5 1 1.364 1 2.25C11 14 9.657 15 8 15"/>
                            </svg>
                        </span>
                    </div>
                    @endif
                </div>
                <div class="absolute top-2 left-2">
                    @if ($row->featured == 'Yes')
                        <div class="bg-gray-50 inline-block px-2 py-1 rounded text-[12px]" title="Boosted Ad">
                            <span><i class="bi bi-rocket-takeoff"></i></span>
                            <span class="font-semibold">Boost</span>
                        </div>
                    @endif
                </div>

                {{-- Related tag: z-[2] sits above the stretched card link (z-[1]) --}}
                <a href="/related/{{ $row->ad_id }}" class="absolute bottom-1 left-2 z-[2] w-10 h-10
                  flex items-center justify-center
                  rounded-full
                  bg-black/20 backdrop-blur-sm
                  shadow-md
                  hover:bg-black/60 transition">
                    <i class="bi bi-stars text-2xl text-yellow-100"></i>
                </a>
            </div>

            <p class="text-sm text-gray-600 leading-4 mt-1">
                <a href="{{ url($row->state_slug . '/' . $row->title_slug .'/'. $row->ad_id) }}"
                   class="after:absolute after:inset-0 after:content-[''] after:z-[1]">
                    {{ Str::limit($row->ad_title, 40) }}
                </a>
            </p>
            <div class="flex justify-between">
                @if($row->category==3)
                <span class="text-sm font-bold text-green-600">{{ $row->salary }}</span>
                @elseif($row->category==18)
                    <span class="text-sm font-bold text-green-600">{{ $row->expected_salary }}</span>
                @elseif($row->contact_price=="yes")
                    <span class="text-sm font-bold text-green-600">Contact For Price</span>
                @else
                <span class="text-xs font-semibold text-dark_green">Br  {{ number_format($row->price, 0, '.', ',') }} {{ Str::limit($row->price_type, 10) }}</span>
                @endif
            </div>
            @if($row->buy_direct=="Yes")
            <div class="flex items-center mt-1">
                <span class="mr-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-4 accent-bg_primary">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                </span>
                <span>Buy Direct</span>
            </div>
            @endif
        </div>
        @endforeach
    </div>
</section>

<!-- Phone Section -->
<section class="bg-white px-2">
    <div class="flex justify-between">
        <div class="flex justify-start items-center ml-2 my-1 text-gray-500">
            <span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 8.25V6a2.25 2.25 0 0 0-2.25-2.25H6A2.25 2.25 0 0 0 3.75 6v8.25A2.25 2.25 0 0 0 6 16.5h2.25m8.25-8.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-7.5A2.25 2.25 0 0 1 8.25 18v-1.5m8.25-8.25h-6a2.25 2.25 0 0 0-2.25 2.25v6" />
                </svg>
            </span>
            <span>Phones &amp; Tablets</span>
        </div>
        <a href="/category/mobile-phones-and-tablets">
            <div class="flex justify-start items-center ml-2 my-1 text-dark_green font-semibold">
                <span>See all</span>
                <span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </span>
            </div>
        </a>
    </div>

    <div class="flex overflow-x-auto space-x-2 px-1 pb-5 snap-x snap-proximity scrollbar-hide overscroll-x-contain">
        @foreach ($phones as $row)
        <div class="relative flex-none w-[45%] md:w-1/4 snap-start">
            <div class="relative overflow-hidden">
                @php
                    $media = $row->cardImage();
                    $thumbSmUrl = $media && $media->hasGeneratedConversion('thumb-sm')
                        ? $media->getUrl('thumb-sm')
                        : ($media ? $media->getUrl('thumbnail') : asset('frontend/images/default.png'));
                    $thumbnailUrl = $media ? $media->getUrl('thumbnail') : asset('frontend/images/default.png');
                @endphp

                <img
                    alt="{{ $row->ad_title }}"
                    class="w-full h-[160px] object-cover transition duration-300 ease-in-out hover:scale-110 rounded-lg"
                    width="300"
                    height="262"
                    loading="lazy"
                    @if($media)
                        srcset="{{ $thumbSmUrl }} 300w, {{ $thumbnailUrl }} 150w"
                        sizes="(max-width: 768px) 45vw, 25vw"
                    @endif
                    src="{{ $thumbSmUrl }}"
                    onerror="this.onerror=null;this.src='{{ asset('frontend/images/default.png') }}';"
                />

                <div class="absolute top-2 right-2 space-y-1">
                    @if(optional($row->owner)->verified=='yes')
                        <div class="bg-green-50 px-1 rounded text-[14px]">
                            <span title="verified User">
                                <i class="bi bi-patch-check-fill text-secondary_dark"></i>
                            </span>
                        </div>
                    @endif
                    @if($row->views >= setViews())
                    <div class="bg-white opacity-8 flex space-x-2 py-1 px-2 rounded text-[13px]">
                        <span title="Popular Ad">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-fire" viewBox="0 0 16 16">
                                <path d="M8 16c3.314 0 6-2 6-5.5 0-1.5-.5-4-2.5-6 .25 1.5-1.25 2-1.25 2C11 4 9 .5 6 0c.357 2 .5 4-2 6-1.25 1-2 2.729-2 4.5C2 14 4.686 16 8 16m0-1c-1.657 0-3-1-3-2.75 0-.75.25-2 1.25-3C6.125 10 7 10.5 7 10.5c-.375-1.25.5-3.25 2-3.5-.179 1-.25 2 1 3 .625.5 1 1.364 1 2.25C11 14 9.657 15 8 15"/>
                            </svg>
                        </span>
                    </div>
                    @endif
                </div>
                <div class="absolute top-2 left-2">
                    @if ($row->featured == 'Yes')
                        <div class="bg-gray-50 inline-block px-2 py-1 rounded text-[12px]" title="Boosted Ad">
                            <span><i class="bi bi-rocket-takeoff"></i></span>
                            <span class="font-semibold">Boost</span>
                        </div>
                    @endif
                </div>

                {{-- Related tag: z-[2] sits above the stretched card link (z-[1]) --}}
                <a href="/related/{{ $row->ad_id }}" class="absolute bottom-1 left-2 z-[2] w-10 h-10
                  flex items-center justify-center
                  rounded-full
                  bg-black/20 backdrop-blur-sm
                  shadow-md
                  hover:bg-black/60 transition">
                    <i class="bi bi-stars text-2xl text-yellow-100"></i>
                </a>
            </div>

            <p class="text-sm text-gray-600 leading-4 mt-1">
                <a href="{{ url($row->state_slug . '/' . $row->title_slug .'/'. $row->ad_id) }}"
                   class="after:absolute after:inset-0 after:content-[''] after:z-[1]">
                    {{ Str::limit($row->ad_title, 40) }}
                </a>
            </p>
            <div class="flex justify-between">
                @if($row->category==3)
                <span class="text-sm font-bold text-green-600">{{ $row->salary }}</span>
                @elseif($row->category==18)
                    <span class="text-sm font-bold text-green-600">{{ $row->expected_salary }}</span>
                @elseif($row->contact_price=="yes")
                    <span class="text-sm font-bold text-green-600">Contact For Price</span>
                @else
                <span class="text-xs font-semibold text-dark_green">Br  {{ number_format($row->price, 0, '.', ',') }} {{ Str::limit($row->price_type, 10) }}</span>
                @endif
            </div>
            @if($row->buy_direct=="Yes")
            <div class="flex items-center mt-1">
                <span class="mr-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-4 accent-bg_primary">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                </span>
                <span>Buy Direct</span>
            </div>
            @endif
        </div>
        @endforeach
    </div>
</section>

<!-- Fashion & Beauty -->
<section class="bg-white px-2">
    <div class="flex justify-between">
        <div class="flex justify-start items-center ml-2 my-1 text-gray-500">
            <span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 8.25V6a2.25 2.25 0 0 0-2.25-2.25H6A2.25 2.25 0 0 0 3.75 6v8.25A2.25 2.25 0 0 0 6 16.5h2.25m8.25-8.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-7.5A2.25 2.25 0 0 1 8.25 18v-1.5m8.25-8.25h-6a2.25 2.25 0 0 0-2.25 2.25v6" />
                </svg>
            </span>
            <span>Fashion &amp; Beauty</span>
        </div>
        <a href="/category/fashion">
            <div class="flex justify-start items-center ml-2 my-1 text-dark_green font-semibold">
                <span>See all</span>
                <span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                      <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </span>
            </div>
        </a>
    </div>

    <div class="flex overflow-x-auto space-x-2 px-1 pb-5 snap-x snap-proximity scrollbar-hide overscroll-x-contain">
        @foreach ($fashion as $row)
        <div class="relative flex-none w-[45%] md:w-1/4 snap-start">
            <div class="relative overflow-hidden">
                @php
                    $media = $row->cardImage();
                    $thumbSmUrl = $media && $media->hasGeneratedConversion('thumb-sm')
                        ? $media->getUrl('thumb-sm')
                        : ($media ? $media->getUrl('thumbnail') : asset('frontend/images/default.png'));
                    $thumbnailUrl = $media ? $media->getUrl('thumbnail') : asset('frontend/images/default.png');
                @endphp

                <img
                    alt="{{ $row->ad_title }}"
                    class="w-full h-[160px] object-cover transition duration-300 ease-in-out hover:scale-110 rounded-lg"
                    width="300"
                    height="262"
                    loading="lazy"
                    @if($media)
                        srcset="{{ $thumbSmUrl }} 300w, {{ $thumbnailUrl }} 150w"
                        sizes="(max-width: 768px) 45vw, 25vw"
                    @endif
                    src="{{ $thumbSmUrl }}"
                    onerror="this.onerror=null;this.src='{{ asset('frontend/images/default.png') }}';"
                />

                <div class="absolute top-2 right-2 space-y-1">
                    @if(optional($row->owner)->verified=='yes')
                        <div class="bg-green-50 px-1 rounded text-[14px]">
                            <span title="verified User">
                                <i class="bi bi-patch-check-fill text-secondary_dark"></i>
                            </span>
                        </div>
                    @endif
                    @if($row->views >= setViews())
                    <div class="bg-white opacity-8 flex py-1 px-2 rounded text-[13px]">
                        <span title="Popular Ad">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="bi bi-fire" viewBox="0 0 16 16">
                                <path d="M8 16c3.314 0 6-2 6-5.5 0-1.5-.5-4-2.5-6 .25 1.5-1.25 2-1.25 2C11 4 9 .5 6 0c.357 2 .5 4-2 6-1.25 1-2 2.729-2 4.5C2 14 4.686 16 8 16m0-1c-1.657 0-3-1-3-2.75 0-.75.25-2 1.25-3C6.125 10 7 10.5 7 10.5c-.375-1.25.5-3.25 2-3.5-.179 1-.25 2 1 3 .625.5 1 1.364 1 2.25C11 14 9.657 15 8 15"/>
                            </svg>
                        </span>
                    </div>
                    @endif
                </div>
                <div class="absolute top-2 left-2">
                    @if ($row->featured == 'Yes')
                        <div class="bg-gray-50 inline-block px-2 py-1 rounded text-[12px]" title="Boosted Ad">
                            <span><i class="bi bi-rocket-takeoff"></i></span>
                            <span class="font-semibold">Boost</span>
                        </div>
                    @endif
                </div>

                {{-- Related tag: z-[2] sits above the stretched card link (z-[1]) --}}
                <a href="/related/{{ $row->ad_id }}" class="absolute bottom-1 left-2 z-[2] w-10 h-10
                  flex items-center justify-center
                  rounded-full
                  bg-black/20 backdrop-blur-sm
                  shadow-md
                  hover:bg-black/60 transition">
                    <i class="bi bi-stars text-2xl text-yellow-100"></i>
                </a>
            </div>

            <p class="text-sm text-gray-600 leading-4 mt-1">
                <a href="{{ url($row->state_slug . '/' . $row->title_slug .'/'. $row->ad_id) }}"
                   class="after:absolute after:inset-0 after:content-[''] after:z-[1]">
                    {{ Str::limit($row->ad_title, 40) }}
                </a>
            </p>
            <div class="flex justify-between">
                @if($row->category==3)
                <span class="text-sm font-bold text-green-600">{{ $row->salary }}</span>
                @elseif($row->category==18)
                    <span class="text-sm font-bold text-green-600">{{ $row->expected_salary }}</span>
                @elseif($row->contact_price=="yes")
                    <span class="text-sm font-bold text-green-600">Contact For Price</span>
                @else
                <span class="text-xs font-semibold text-dark_green">Br  {{ number_format($row->price, 0, '.', ',') }} {{ Str::limit($row->price_type, 10) }}</span>
                @endif
            </div>
            @if($row->buy_direct=="Yes")
            <div class="flex items-center mt-1">
                <span class="mr-1">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-4 accent-bg_primary">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                    </svg>
                </span>
                <span>Buy Direct</span>
            </div>
            @endif
        </div>
        @endforeach
    </div>
</section>
