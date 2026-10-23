<div class="my-2 space-y-6 text-sm">
    <div class="flex text-sm font-bold text-dark_green">
        <h5>Boost Ad to get more views & sell faster (Optional)</h5>
    </div>

    @if(isset($boostTypes) && $boostTypes->count() > 0)
        @foreach($boostTypes as $type)
        <!-- {{ $type->name }} -->
        <div class="w-full flex justify-start">
            <label class="w-full flex justify-start cursor-pointer">
                <div class="flex-[1] flex justify-center gap-2">
                    <input type="radio"
                           name="promotion"
                           id="boost_{{ $type->id }}"
                           value="{{ strtolower($type->name) }}"
                           class="hidden peer"
                           onclick="toggleRadio(this)"
                           data-boost-id="{{ $type->id }}"
                           data-boost-name="{{ $type->name }}">
                    <div class="h-5 w-5 border border-primary rounded bg-white peer-checked:bg-dark_green peer-checked:border-dark_green flex items-center justify-center">
                        <svg class="text-white h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>

                <div class="flex-[11] -mt-2">
                    <div class="space-y-1">
                        <div class="flex justify-start gap-2">
                            <span class="text-dark_green font-bold text-base">{{ $type->name }}</span>
                            <div class="font-bold">
                                Br {{ number_format(round($type->daily_rate * 7), 2) }}/week
                            </div>

                        </div>

                        <div class="flex gap-3">
                            <span class="font-semibold">{{ $type->description }}</span>
                        </div>
                    </div>
                </div>
            </label>
        </div>
        @endforeach
    @else
        <!-- Fallback to hardcoded options if dynamic data not available -->
        <div class="w-full flex justify-start">
            <label class="w-full flex justify-start cursor-pointer">
                <div class="flex-[1] flex justify-center gap-2">
                    <input type="radio" name="promotion" id="highlight" value="highlight" class="hidden peer" onclick="toggleRadio(this)">
                    <div class="h-5 w-5 border border-primary rounded bg-white peer-checked:bg-dark_green peer-checked:border-dark_green flex items-center justify-center">
                        <svg class="text-white h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
                <div class="flex-[11] -mt-2">
                    <div class="space-y-1">
                        <div class="flex justify-start gap-2">
                            <span class="text-dark_green font-bold text-base">Highlight</span>
                            <div class="font-bold">Br 1,500</div>
                        </div>
                        <div class="flex gap-3">
                            <span class="font-semibold">Up to 2x more visibility! your ad will be highlighted in color. (7 Days)</span>
                        </div>
                    </div>
                </div>
            </label>
        </div>

        <div class="w-full flex justify-start">
            <label class="w-full flex justify-start cursor-pointer">
                <div class="flex-[1] flex justify-center gap-2">
                    <input type="radio" name="promotion" id="repeated" value="repeated" class="hidden peer" onclick="toggleRadio(this)">
                    <div class="h-5 w-5 border border-primary rounded bg-white peer-checked:bg-dark_green peer-checked:border-dark_green flex items-center justify-center">
                        <svg class="text-white h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
                <div class="flex-[11] -mt-2">
                    <div class="space-y-1">
                        <div class="flex justify-start gap-2">
                            <span class="text-dark_green font-bold text-base">Repeated Pushing Up</span>
                            <div class="font-bold">Br 3,500</div>
                        </div>
                        <div class="flex gap-3">
                            <span class="font-semibold">Up to 5x more visibility! your ad will be pushed up every day for a week. (7 Days)</span>
                        </div>
                    </div>
                </div>
            </label>
        </div>

        <div class="w-full flex justify-start">
            <label class="w-full flex justify-start cursor-pointer">
                <div class="flex-[1] flex justify-center gap-2">
                    <input type="radio" name="promotion" id="top" value="top" class="hidden peer" onclick="toggleRadio(this)">
                    <div class="h-5 w-5 border border-primary rounded bg-white peer-checked:bg-dark_green peer-checked:border-dark_green flex items-center justify-center">
                        <svg class="text-white h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
                <div class="flex-[11] -mt-2">
                    <div class="space-y-1">
                        <div class="flex justify-start gap-2">
                            <span class="text-dark_green font-bold text-base">Top Ad</span>
                            <div class="font-bold">Br 7,500</div>
                        </div>
                        <div class="flex gap-3">
                            <span class="font-semibold">Up to 10x more visibility! your ad is at the top of visibility list. (14 Days)</span>
                        </div>
                    </div>
                </div>
            </label>
        </div>

        <div class="w-full flex justify-start">
            <label class="w-full flex justify-start cursor-pointer">
                <div class="flex-[1] flex justify-center gap-2">
                    <input type="radio" name="promotion" id="gallery" value="gallery" class="hidden peer" onclick="toggleRadio(this)">
                    <div class="h-5 w-5 border border-primary rounded bg-white peer-checked:bg-dark_green peer-checked:border-dark_green flex items-center justify-center">
                        <svg class="text-white h-3 w-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
                <div class="flex-[11] -mt-2">
                    <div class="space-y-1">
                        <div class="flex justify-start gap-2">
                            <span class="text-dark_green font-bold text-base">Gallery</span>
                            <div class="font-bold">Br 10,000</div>
                        </div>
                        <div class="flex gap-3">
                            <span class="font-semibold">Up to 15x more visibility! your ad will appear on the homepage. (14 Days)</span>
                        </div>
                    </div>
                </div>
            </label>
        </div>
    @endif
</div>

<script>
  let lastChecked = null;

  function toggleRadio(radio) {
    if (radio === lastChecked) {
      radio.checked = false;
      lastChecked = null;
      // Force UI update for Tailwind's peer-checked class
      const event = new Event('change');
      radio.dispatchEvent(event);
    } else {
      lastChecked = radio;
    }
  }
</script>
