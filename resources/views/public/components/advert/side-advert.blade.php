<div class="p-1">
    @foreach(getSideAdverts() as $advert)
      <a href="{{ $advert->url }}" title="{{ $advert->company }}" target="_blank"><img class="object-cover w-full h-full" src="{{ asset('uploads/advertising/'.$advert->image) }}"></a>
    @endforeach
</div>