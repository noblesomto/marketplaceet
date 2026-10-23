{{--
    Mobile-only category back nav header.
    Variables expected:
      $backUrl   – URL the back arrow links to
      $pageTitle – Page title shown centered in the bar
--}}
<div class="block lg:hidden bg-dark_green text-white">
    <div class="flex items-center h-12 px-3 gap-2">
        <a href="{{ $backUrl }}"
           aria-label="Go back"
           class="flex items-center justify-center w-9 h-9 rounded-full hover:bg-white/10 transition-colors shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <span class="flex-1 text-center font-semibold text-[15px] truncate pr-9">{{ $pageTitle }}</span>
    </div>
</div>
