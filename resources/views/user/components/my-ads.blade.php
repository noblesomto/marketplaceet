@if (!$ads->isEmpty())
    <div class="space-y-3">
    @foreach ($ads as $row)
    @php
        $isSold     = $row->sold === 'Yes';
        $isFeatured = $row->featured === 'Yes';
        $adStatus   = $row->ad_status;
        $imgCount   = $row->images_count ?? $row->getMedia('images')->count();
        $adLink     = url($row->state_slug . '/' . $row->title_slug . '/' . $row->ad_id);
        $hasShipping = !in_array($row->category, [1, 3, 11, 18]) && $row->buy_direct == 'Yes';
        $banReason  = $adStatus === 'banned'
            ? $row->moderationLogs->first(fn($log) => in_array($log->action, ['banned', 'rejected']))
            : null;
    @endphp

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden {{ $isSold ? 'opacity-70' : '' }}">

        {{-- Card body: thumbnail + details + menu --}}
        <div class="flex items-start gap-3 p-3 md:gap-4 md:p-4">

            {{-- Thumbnail --}}
            <a href="{{ $adLink }}" class="flex-shrink-0 relative w-20 h-20 sm:w-24 sm:h-24 md:w-36 md:h-36 rounded-lg overflow-hidden bg-gray-100 block">
                @php $cover = $row->cardImage(); @endphp
                @if($cover)
                    <img src="{{ $cover->getUrl('thumbnail') }}"
                         alt="{{ $row->ad_title ?? 'Ad' }}"
                         class="w-full h-full object-cover"
                         onerror="this.src='{{ asset('frontend/images/default.png') }}'">
                @else
                    <img src="{{ asset('frontend/images/default.png') }}"
                         alt="No image"
                         class="w-full h-full object-cover">
                @endif
                @if($imgCount > 0)
                    <span class="absolute bottom-1 right-1 bg-black/60 text-white text-[10px] rounded px-1 leading-4">
                        <i class="bi bi-images"></i> {{ $imgCount }}
                    </span>
                @endif
                @if($isSold)
                    <span class="absolute inset-0 flex items-center justify-center bg-black/40">
                        <span class="bg-green-600 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">SOLD</span>
                    </span>
                @endif
            </a>

            {{-- Details --}}
            <div class="flex-1 min-w-0">

                {{-- Location + date --}}
                <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 text-[11px] md:text-xs text-gray-400 mb-1 md:mb-2">
                    @if($row->state)
                        <span><i class="bi bi-geo-alt-fill"></i> {{ $row->state }}</span>
                    @endif
                    <span><i class="bi bi-calendar3"></i> {{ date('d M Y', strtotime($row->created_at)) }}</span>
                </div>

                {{-- Title --}}
                <a href="{{ $adLink }}"
                   class="block text-sm md:text-base font-semibold text-gray-900 leading-snug hover:text-dark_green transition-colors mb-1 md:mb-2 line-clamp-2">
                    {{ Str::limit($row->ad_title, 70) }}
                </a>

                {{-- Price --}}
                <div class="text-dark_green font-bold text-sm md:text-lg mb-1.5 md:mb-2">
                    @if($row->category == 3)
                        {{ $row->salary }}
                    @elseif($row->category == 18)
                        {{ $row->expected_salary }}
                    @elseif($row->contact_price === 'yes')
                        <span class="text-gray-500 font-medium">Contact for Price</span>
                    @else
                        Br {{ number_format($row->price, 0, '.', ',') }}
                        @if($row->price_type)
                            <span class="text-gray-400 text-[11px] font-normal">{{ $row->price_type }}</span>
                        @endif
                    @endif
                </div>

                {{-- Status badge --}}
                <div>
                    @if($adStatus === 'active' && !$isSold)
                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-green-100 text-green-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span> Active
                        </span>
                    @elseif($adStatus === 'disabled' && !$isSold)
                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">
                            <i class="bi bi-slash-circle text-[10px]"></i> Disabled
                        </span>
                    @elseif($adStatus === 'banned')
                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-red-100 text-red-600">
                            <i class="bi bi-x-circle text-[10px]"></i> Disabled
                        </span>
                    @elseif($adStatus === 'pending_review')
                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-blue-100 text-blue-600">
                            <i class="bi bi-hourglass-split text-[10px]"></i> Under review
                        </span>
                    @endif
                    @if($isFeatured && !$isSold)
                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-700 ml-1">
                            <i class="bi bi-rocket-takeoff-fill text-[10px]"></i> Boosted
                        </span>
                    @endif
                    @if($banReason)
                        <div class="mt-1.5 text-[11px] text-gray-500">
                            <span class="font-semibold text-red-600">{{ $banReason->reason_category_label }}</span>
                            @if($banReason->reason_note)
                                — {{ Str::limit($banReason->reason_note, 80) }}
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- Three-dots menu --}}
            <div class="relative dropdown flex-shrink-0">
                <button type="button"
                        class="dropdown-button w-7 h-7 flex items-center justify-center rounded-full hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors"
                        aria-haspopup="true" aria-expanded="false">
                    <i class="bi bi-three-dots-vertical text-sm"></i>
                </button>
                <div class="dropdown-menu hidden absolute right-0 z-50 mt-1 w-36 rounded-lg bg-white shadow-lg border border-gray-100" style="top:100%;">
                    <a href="javascript:void(0)"
                       data-delete-url="/user/delete-ad/{{ $row->id }}"
                       class="delete-ad-btn flex items-center gap-2 px-3 py-2.5 text-xs text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                        <i class="bi bi-trash"></i> Delete Ad
                    </a>
                </div>
            </div>
        </div>

        {{-- Action button strip --}}
        <div class="border-t border-gray-100 bg-gray-50 px-3 py-2 md:px-4 md:py-3 flex flex-wrap gap-1.5 md:gap-2">

            {{-- Mark Sold / Sold badge --}}
            @if($isSold)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 md:px-4 md:py-2 rounded-md text-[11px] md:text-xs font-semibold bg-green-100 text-green-700 cursor-default">
                    <i class="bi bi-check-circle-fill"></i> Sold
                </span>
            @else
                <a href="javascript:void(0)"
                   class="mark-sold-btn inline-flex items-center gap-1 px-2.5 py-1 md:px-4 md:py-2 rounded-md text-[11px] md:text-xs font-semibold bg-white border border-gray-200 text-gray-600 hover:bg-green-50 hover:border-green-300 hover:text-green-700 transition-colors cursor-pointer"
                   data-mark-sold-url="/user/mark-sold/{{ $row->id }}">
                    <i class="bi bi-check-circle"></i> Mark Sold
                </a>
            @endif

            {{-- Edit Ad --}}
            @if($isSold)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 md:px-4 md:py-2 rounded-md text-[11px] md:text-xs font-semibold bg-white border border-gray-100 text-gray-300 cursor-not-allowed">
                    <i class="bi bi-pencil"></i> Edit
                </span>
            @else
                <a href="/user/edit-ad/{{ $row->id }}"
                   class="inline-flex items-center gap-1 px-2.5 py-1 md:px-4 md:py-2 rounded-md text-[11px] md:text-xs font-semibold bg-white border border-gray-200 text-gray-600 hover:bg-blue-50 hover:border-blue-300 hover:text-blue-700 transition-colors">
                    <i class="bi bi-pencil"></i> Edit
                </a>
            @endif

            {{-- Boost Ad --}}
            @if($isSold)
                <span class="inline-flex items-center gap-1 px-2.5 py-1 md:px-4 md:py-2 rounded-md text-[11px] md:text-xs font-semibold bg-white border border-gray-100 text-gray-300 cursor-not-allowed">
                    <i class="bi bi-rocket-takeoff"></i> Boost
                </span>
            @elseif($isFeatured)
                <a href="/user/boosted-ad/{{ $row->id }}"
                   class="inline-flex items-center gap-1 px-2.5 py-1 md:px-4 md:py-2 rounded-md text-[11px] md:text-xs font-semibold bg-amber-100 border border-amber-200 text-amber-700 hover:bg-amber-200 transition-colors">
                    <i class="bi bi-rocket-takeoff-fill"></i> Boosted
                </a>
            @else
                <a href="/user/boost-ad/{{ $row->id }}"
                   class="inline-flex items-center gap-1 px-2.5 py-1 md:px-4 md:py-2 rounded-md text-[11px] md:text-xs font-semibold bg-white border border-gray-200 text-gray-600 hover:bg-amber-50 hover:border-amber-300 hover:text-amber-700 transition-colors">
                    <i class="bi bi-rocket-takeoff"></i> Boost
                </a>
            @endif

            {{-- Ad status toggle (not shown for sold) --}}
            @if(!$isSold)
                @if($adStatus === 'active')
                    <a href="javascript:void(0)"
                       class="ad-status-btn inline-flex items-center gap-1 px-2.5 py-1 md:px-4 md:py-2 rounded-md text-[11px] md:text-xs font-semibold bg-white border border-gray-200 text-gray-500 hover:bg-red-50 hover:border-red-300 hover:text-red-600 transition-colors"
                       data-status-url="/user/ad-status/disabled/{{ $row->id }}"
                       data-current-status="active" data-new-status="disabled"
                       title="Click to disable">
                        <i class="bi bi-toggle-on text-green-500"></i> Disable
                    </a>
                @elseif($adStatus === 'disabled')
                    <a href="javascript:void(0)"
                       class="ad-status-btn inline-flex items-center gap-1 px-2.5 py-1 md:px-4 md:py-2 rounded-md text-[11px] md:text-xs font-semibold bg-white border border-gray-200 text-gray-500 hover:bg-green-50 hover:border-green-300 hover:text-green-600 transition-colors"
                       data-status-url="/user/ad-status/active/{{ $row->id }}"
                       data-current-status="disabled" data-new-status="active"
                       title="Click to activate">
                        <i class="bi bi-toggle-off text-gray-400"></i> Activate
                    </a>
                @elseif($adStatus === 'banned')
                    <a href="javascript:void(0)"
                       class="resubmit-ad-btn inline-flex items-center gap-1 px-2.5 py-1 md:px-4 md:py-2 rounded-md text-[11px] md:text-xs font-semibold bg-white border border-blue-200 text-blue-600 hover:bg-blue-50 transition-colors"
                       data-resubmit-url="/user/resubmit-ad/{{ $row->id }}">
                        <i class="bi bi-arrow-repeat"></i> Resubmit for review
                    </a>
                @elseif($adStatus === 'pending_review')
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 md:px-4 md:py-2 rounded-md text-[11px] md:text-xs font-semibold bg-white border border-gray-100 text-gray-400 cursor-default">
                        <i class="bi bi-hourglass-split"></i> Awaiting review
                    </span>
                @endif
            @endif

            {{-- Shipping Status (sold Buy Direct ads only) --}}
            @if($isSold && $hasShipping)
                <a href="/user/ad-shipping/{{ $row->id }}"
                   class="inline-flex items-center gap-1 px-2.5 py-1 md:px-4 md:py-2 rounded-md text-[11px] md:text-xs font-semibold bg-blue-50 border border-blue-200 text-blue-700 hover:bg-blue-100 transition-colors">
                    <i class="bi bi-truck"></i> Shipping
                </a>
            @endif

        </div>
    </div>
    @endforeach
    </div>

@else
    <div class="flex flex-col items-center justify-center py-16 text-center bg-white rounded-xl border border-gray-100 shadow-sm">
        <i class="bi bi-badge-ad text-5xl text-gray-200 mb-4"></i>
        <div class="text-base font-semibold text-gray-700 mb-1">No adverts yet</div>
        <p class="text-sm text-gray-400 mb-5">Your listings will appear here once you post an ad.</p>
        <a href="/user/post-ad"
           class="inline-flex items-center gap-2 px-5 py-2.5 bg-dark_green text-white rounded-lg text-sm font-semibold hover:bg-green-800 transition-colors">
            <i class="bi bi-plus-lg"></i> Post Your First Ad
        </a>
    </div>
@endif

<script>
(function () {
    /* Dropdown toggle */
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.dropdown-button');
        const anyDropdown = e.target.closest('.dropdown');
        if (btn && anyDropdown) {
            const menu = anyDropdown.querySelector('.dropdown-menu');
            const willOpen = menu.classList.contains('hidden');
            document.querySelectorAll('.dropdown .dropdown-menu').forEach(m => m.classList.add('hidden'));
            if (willOpen) {
                menu.classList.remove('hidden');
                btn.setAttribute('aria-expanded', 'true');
            } else {
                btn.setAttribute('aria-expanded', 'false');
            }
            return;
        }
        if (!anyDropdown) {
            document.querySelectorAll('.dropdown .dropdown-menu').forEach(m => m.classList.add('hidden'));
            document.querySelectorAll('.dropdown-button[aria-expanded="true"]').forEach(b => b.setAttribute('aria-expanded', 'false'));
        }
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') document.querySelectorAll('.dropdown .dropdown-menu').forEach(m => m.classList.add('hidden'));
    });

    /* Delete Ad */
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.delete-ad-btn');
        if (!btn) return;
        e.preventDefault();
        const url = btn.getAttribute('data-delete-url');
        Swal.fire({
            title: 'Delete this advert?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel'
        }).then(result => {
            if (!result.isConfirmed) return;
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;
            form.innerHTML = `<input type="hidden" name="_token" value="{{ csrf_token() }}">
                              <input type="hidden" name="_method" value="DELETE">`;
            document.body.appendChild(form);
            form.submit();
        });
    });

    /* Mark Sold */
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.mark-sold-btn');
        if (!btn) return;
        e.preventDefault();
        Swal.fire({
            title: 'Mark as Sold?',
            text: 'This will mark the advert as sold and hide it from search results.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#326916',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, mark sold',
            cancelButtonText: 'Cancel'
        }).then(result => {
            if (result.isConfirmed) window.location.href = btn.getAttribute('data-mark-sold-url');
        });
    });

    /* Ad Status Toggle */
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.ad-status-btn');
        if (!btn) return;
        e.preventDefault();
        const newStatus = btn.getAttribute('data-new-status');
        const isDisabling = newStatus === 'disabled';
        Swal.fire({
            title: isDisabling ? 'Disable Ad?' : 'Activate Ad?',
            text: isDisabling
                ? 'Your ad will be hidden from public view. You can reactivate it anytime.'
                : 'Your ad will be visible to the public again.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: isDisabling ? '#ef4444' : '#326916',
            cancelButtonColor: '#6b7280',
            confirmButtonText: isDisabling ? 'Yes, disable' : 'Yes, activate',
            cancelButtonText: 'Cancel'
        }).then(result => {
            if (result.isConfirmed) window.location.href = btn.getAttribute('data-status-url');
        });
    });

    /* Resubmit for review */
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.resubmit-ad-btn');
        if (!btn) return;
        e.preventDefault();
        Swal.fire({
            title: 'Resubmit for review?',
            text: 'Our team will review your changes before making this ad visible again.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#326916',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, resubmit',
            cancelButtonText: 'Cancel'
        }).then(result => {
            if (result.isConfirmed) window.location.href = btn.getAttribute('data-resubmit-url');
        });
    });
})();
</script>
