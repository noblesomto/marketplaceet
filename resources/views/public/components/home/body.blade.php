

<section class="w-full md:w-4/6 mx-auto bg-white md:bg-body">
  <div class="block lg:hidden bg-white pt-3 ml-2">
    <span class="text-lg ">eBay Classifieds is now Classifieds </span>
  </div>

  <div class="my-5">
    @foreach(getAdverts() as $advert)
      <img class="object-cover w-full h-36 md:h-64" src="{{ asset('uploads/advertising/'.$advert->image) }}">
    @endforeach
  </div>

  <div class="block lg:hidden">
    @include('public.components.mobile.mobile-gallery')
  </div>

  <div class="grid grid-cols-8 gap-3">
        <div class="col-span-2 invisible md:visible bg-amber-100">Category List here </div>
        <div class="col-span-8 md:col-span-6 ">
            <div class="bg-white p-2">@include('public.components.home.gallery')</div>
            <div>@include('public.components.home.homeads')</div>
        </div>
  </div>
</section>