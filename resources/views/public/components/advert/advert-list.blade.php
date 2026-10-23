@forelse($ads as $row)
    <a href="{{ url($row->state_slug . '/' . $row->title_slug .'/'. $row->ad_id) }}">
      <div class="bg-white my-2 py-1 border-b-1 border-b-gray-300 h-[170px] sm:h-[180px] md:h-[220px]">
         <div class="flex w-full h-full">
              <div class="flex-[45%] xs-max:flex-[42%] sm:flex-[42%] lg:flex-[35%] mr-1 relative h-full p-2">
                <img alt="{{ $row->ad_title }}" class="w-full h-full object-cover rounded-lg" src="{{ $row->hasMedia('images') ? $row->getFirstMediaUrl('images', 'thumbnail') : asset('frontend/images/default.png') }}" onerror="this.onerror=null;this.src='{{ asset('frontend/images/default.png') }}';">
                <div class="absolute top-3 right-3 flex space-x-1">
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
                <div class="absolute top-3 left-3">
                    @if ($row->featured == 'Yes')
                        <div class="bg-gray-50 inline-block px-1 py-0.5 rounded text-[12px]" title="Boosted Ad">
                            <span>
                                <i class="bi bi-rocket-takeoff"></i>
                            </span>
                            <span class="font-semibold">Boost</span>
                        </div>
                    @endif
                </div>
                <div class="absolute bottom-3 right-3 bg-black w-6 h-5 text-xs text-white flex justify-center items-center">{{ $row->getMedia('images')->count() }}</div>
              </div>
              <div class="flex-[55%] xs:flex-[58%] sm:flex-[58%] lg:flex-[65%] relative h-full overflow-hidden space-y-1">
                <div class="flex justify-between text-xs">
                  <div class="flex justify-start items-center text-sm md:mr-5">
                    <div class="flex gap-2">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                            </svg>
                        </span>
                      <span class="text-xs">{{ $row->state }}</span>
                    </div>
                    </div>
                  <div data-nosnippet aria-hidden="true">
                    <div class="flex justify-start mr-5 text-xs md:mt-2">
                      <span class="mr-3 hidden lg:block"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                    </svg>
                    </span>  <span class="text-xs" data-nosnippet>{{ date('d.m.Y', strtotime($row->created_at)) }}</span></div>
                  </div>
                </div>
                <div class="font-semibold leading-5 md:font-bold text-sm md:text-base mt-1 line-clamp-2"> {{ Str::limit($row->ad_title, 50) }}</div>

                <div class="text-xs mt-1 hidden lg:block line-clamp-2">{!! Str::limit(strip_tags($row->description), 80) !!}</div>
                @if($row->category==3)
                    <div class="text-dark_green font-bold text-sm my-1">
                        {{ $row->salary }}
                    </div>
                    @elseif($row->category==18)
                        <div class="text-dark_green font-bold text-sm my-1">
                            {{ $row->expected_salary }}
                        </div>
                    @elseif($row->contact_price=="yes")
                        <div class="text-dark_green font-bold text-sm my-1">
                            Contact For Price
                        </div>
                    @else
                <div class="flex items-center justify-between text-xs mt-1">
                    <div class="flex justify-start text-dark_green font-bold text-sm my-1">
                      <div class="mr-2">Br  {{ number_format($row->price, 0, '.', ',') }} </div>
                      <div>{{ $row->price_type }}</div>
                    </div>
                </div>
                @endif

                <div class="mb-2 pb-4">
                    <div class="flex justify-between">
                        <div class="flex-col space-y-2 pb-2">
                            @if($row->sub_category == 2)
                                @if(optional($row->car)->condition)
                                <span class="bg-gray-100 p-1 mr-2 text-xs">{{ $row->car->condition }}</span>
                                @endif
                                @if(optional($row->car)->registration)
                                <div class="flex items-center">
                                    <span class="bg-gray-100 p-1 mr-2 text-xs">{{ $row->car->registration }}</span>
                                </div>
                                @endif
                            @elseif($row->sub_category == 6)
                                @if(optional($row->phone)->condition)
                                <span class="bg-gray-100 p-1 mr-2 text-xs">{{ $row->phone->condition }}</span>
                                @endif
                            @elseif(!empty($row->item_condition))
                                <span class="bg-gray-100 p-1 mr-2 text-xs">{{ $row->item_condition }}</span>
                            @endif
                        </div>

                        @if($row->sold == "Yes")
                            <span class="flex items-center gap-1 bg-red-100 text-red-800 px-2 py-1 rounded cursor-not-allowed mr-1 lg:mr-4 text-xs h-fit whitespace-nowrap " title="This advert is already sold">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <span class="font-semibold">Sold</span>
                        </span>
                        @endif
                    </div>
                </div>

                <div class="absolute bottom-0 left-0 right-0 flex items-center justify-between text-xs">
                    @if($row->shipment=="Ship")
                        <span class="bg-gray-100 p-1 text-xs">Shipping Possible</span>
                    @endif
                    @if($row->buy_direct=="Yes")
                          <div class="flex items-center bg-blue-50 rounded-full px-2 py-1 w-fit mr-1 lg:mr-4">
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
          </div>
      </div>
</a>
@empty
    <div class="flex flex-col items-center justify-center py-10 text-gray-500">
        {{-- Empty box SVG --}}
        <svg xmlns="http://www.w3.org/2000/svg"
             class="w-20 h-20 mb-4"
             fill="none"
             viewBox="0 0 24 24"
             stroke="currentColor">
            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="1.5"
                  d="M3 7l9 5 9-5M3 7v10l9 5 9-5V7M3 7l9-5 9 5" />
        </svg>

        <p class="text-sm">You have no item listed yet, <a class="text-dark_green font-semibold" href="/user/post-ad">Post an Ad</a> </p>
    </div>
@endforelse
