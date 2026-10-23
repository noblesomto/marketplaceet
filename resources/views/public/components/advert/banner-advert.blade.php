@if($isMobile)
    <div class="w-full">
        @foreach(getAdverts()->filter(fn($a) => $a->mobile_image) as $advert)
            <a href="{{ $advert->url }}" title="{{ $advert->company }}" target="_blank" rel="noopener noreferrer">
                <div class="w-full aspect-[2/1]">
                    <img class="object-cover w-full h-full"
                         src="{{ asset('uploads/advertising/'.$advert->mobile_image) }}"
                         alt="{{ $advert->company }} banner"
                         loading="lazy">
                </div>
            </a>
        @endforeach
    </div>
@else
    <div class="space-y-2">
        @foreach(getAdverts()->filter(fn($a) => $a->image) as $advert)
            <a href="{{ $advert->url }}" title="{{ $advert->company }}" target="_blank" rel="noopener noreferrer">
                <div class="w-full aspect-[3/1] md:aspect-[4/1]">
                    <img class="object-contain w-full h-full"
                         src="{{ asset('uploads/advertising/'.$advert->image) }}"
                         alt="{{ $advert->company }} banner"
                         loading="lazy">
                </div>
            </a>
        @endforeach
    </div>
@endif
