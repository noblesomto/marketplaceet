@include('public.layouts.header')
@include('public.layouts.nav')
@include('public.components.mobile.mobile-nav')
@include('public.layouts.search')


<main class="min-h-[calc(100vh-200px)] bg-white p-5"> <!-- Adjust 200px based on your header/footer heights -->
  <div class="flex-col">
    <div><h4 class="font-semibold">Categories</h4></div>
    <div class="mt-4"><a class="text-xs" href="/all-categories">All Categories</a></div>
    <div class="flex bg-gray-200 p-2 mt-1 mb-2">
      <span class="font-semibold mr-2">{{ $cat->category }}</span>
      <span>({{ $count_cat }})</span>
    </div>

    @foreach($categories as $subCategory)
      <a href="{{ url('/category/' . $cat->category_slug . '/' . $subCategory->sub_cat_slug) }}">
        <div class="flex ml-3 mt-1">
            <span class="mr-1">{{ $subCategory->sub_category }}</span>
            <span>({{ $subCategory->advert_count }})</span>
        </div>
    </a>
    @endforeach
  </div>
</main>

@include('public.layouts.footer')
