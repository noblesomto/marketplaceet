@if(isset($seoH1))
<section class="w-full max-w-[95rem] mx-auto mt-3 px-1">
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 md:p-5">
        <h1 class="text-lg md:text-xl font-bold text-gray-900 tracking-tight">{{ $seoH1 }}</h1>
        @if(isset($seoIntro))
        <p class="mt-2 text-sm text-gray-600 leading-relaxed">{{ $seoIntro }}</p>
        @endif
    </div>
</section>
@endif
