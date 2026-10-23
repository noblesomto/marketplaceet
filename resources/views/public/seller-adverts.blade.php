@include('public.layouts.header')
@include('public.layouts.nav')
@include('public.components.mobile.mobile-nav')
@include('public.layouts.search')

@php
    $sellerSlug = \Illuminate\Support\Str::slug($owner->name);
    $storeUrl   = url("/seller/{$sellerSlug}/{$owner->user_id}");
    $labels     = feedback_rating_labels($owner->user_id);
    $followers  = countUserFollowers($owner->user_id);
    $joinDate   = date('F Y', strtotime($owner->created_at));
    $isOwner    = session()->get('user_id') && $user && $owner->user_id === $user->user_id;
    $loggedIn   = session()->get('user_id') != '';
@endphp

{{-- ===================== STORE HERO BANNER ===================== --}}
<div class="w-full bg-gradient-to-br from-dark_green via-green-800 to-green-900 text-white">
    <div class="max-w-[95rem] mx-auto px-4 py-10">
        <div class="flex flex-col md:flex-row items-center md:items-end gap-6">

            {{-- Avatar --}}
            <div class="shrink-0">
                <div class="w-24 h-24 md:w-28 md:h-28 rounded-full border-4 border-white/30 shadow-lg overflow-hidden bg-white/10">
                    @if($owner->hasMedia('profile_image'))
                        <img src="{{ $owner->profile_thumbnail_url }}"
                             alt="{{ $owner->name }}"
                             width="112" height="112"
                             class="w-full h-full object-cover">
                    @else
                        <img src="{{ $owner->profile_thumbnail_url }}"
                             alt="{{ $owner->name }}"
                             width="112" height="112"
                             class="w-full h-full object-cover">
                    @endif
                </div>
            </div>

            {{-- Name + badges --}}
            <div class="flex-1 text-center md:text-left">
                <div class="flex flex-col md:flex-row md:items-center gap-2 md:gap-3">

                   <div class="flex justify-center md:justify-start items-center text-center space-x-2">
                        <h1 class="text-2xl md:text-3xl font-bold tracking-tight">{{ $owner->name }}</h1>
                        @if($owner->verified =='yes')
                            <i title="verified Seller" class="bi bi-patch-check-fill text-2xl text-secondary_dark"></i>
                        @endif
                    </div>


                    @if($owner->acc_type === 'Commercial')
                        <span class="inline-flex items-center gap-1 bg-yellow-400/20 text-white text-xs font-semibold px-3 py-1 rounded-full self-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z" />
                            </svg>
                            Commercial
                        </span>
                    @endif
                </div>

                {{-- Stats row --}}
                <div class="flex flex-wrap justify-center md:justify-start gap-x-6 gap-y-1 mt-3 text-sm text-white/80">
                    <span class="flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                        </svg>
                        <strong class="text-white">{{ $count_ads }}</strong> ads
                    </span>
                    <a href="/seller/{{ $sellerSlug }}/{{ $owner->user_id }}/followers"
                        class="flex items-center gap-1 hover:text-white/60 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M4.5 6.375a4.125 4.125 0 1 1 8.25 0 4.125 4.125 0 0 1-8.25 0ZM14.25 8.625a3.375 3.375 0 1 1 6.75 0 3.375 3.375 0 0 1-6.75 0ZM1.5 19.125a7.125 7.125 0 0 1 14.25 0v.003l-.001.119a.75.75 0 0 1-.363.63 13.067 13.067 0 0 1-6.761 1.873c-2.472 0-4.786-.684-6.76-1.873a.75.75 0 0 1-.364-.63l-.001-.122ZM17.25 19.128l-.001.144a2.25 2.25 0 0 1-.233.96 10.088 10.088 0 0 0 5.06-1.01.75.75 0 0 0 .42-.643 4.875 4.875 0 0 0-6.957-4.611 8.586 8.586 0 0 1 1.71 5.157v.003Z" />
                        </svg>
                        <strong class="text-white">{{ $followers }}</strong>
                        <span>followers</span>
                    </a>
                    <a href="/seller/{{ $sellerSlug }}/{{ $owner->user_id }}/following"
                        class="flex items-center gap-1 hover:text-white/60 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                        </svg>
                        <strong class="text-white">{{ $following_count }}</strong>
                        <span>following</span>
                    </a>
                    @if($owner->city || $owner->state)
                        <span class="flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                            </svg>
                            {{ implode(', ', array_filter([$owner->city, $owner->state])) }}
                        </span>
                    @endif
                    <span class="flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                        </svg>
                        Member since {{ $joinDate }}
                    </span>
                </div>
            </div>

            {{-- Action buttons --}}
            <div class="flex items-center gap-3 shrink-0">
                {{-- Share / Copy link --}}
                <button id="share-store-btn"
                        data-url="{{ $storeUrl }}"
                        data-name="{{ $owner->name }}"
                        title="Share this store"
                        class="flex items-center gap-2 bg-white/15 hover:bg-white/25 text-white text-sm font-medium px-4 py-2 rounded-full border border-white/30 transition-all duration-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" />
                    </svg>
                    <span id="share-btn-label">Share Store</span>
                </button>

                {{-- Follow / Unfollow (guests & non-owners) --}}
                @if($loggedIn && !$isOwner)
                    <button data-user-id="{{ $owner->user_id }}"
                            class="follow-button flex items-center gap-2 bg-white text-dark_green text-sm font-semibold px-4 py-2 rounded-full border border-white hover:bg-green-50 transition-all duration-200">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                        </svg>
                        <span>Follow</span>
                    </button>
                @endif
            </div>
            <div>
                @if($loggedIn && $isOwner)
                    <span>Share your store link to reach more buyers</span>
                @endif
            </div>

        </div>
    </div>
</div>

{{-- ===================== RATING STRIP ===================== --}}
<div class="w-full bg-white border-b border-gray-100 shadow-sm">
    <div class="max-w-[95rem] mx-auto px-4 py-3 flex flex-wrap gap-3 justify-center md:justify-start text-xs">
        <span class="inline-flex items-center gap-1.5 {{ $labels['satisfaction']['color'] }} px-3 py-1 rounded-full font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.182 15.182a4.5 4.5 0 0 1-6.364 0M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z" />
            </svg>
            {{ $labels['satisfaction']['label'] }} Satisfied
        </span>
        <span class="inline-flex items-center gap-1.5 {{ $labels['friendly']['color'] }} px-3 py-1 rounded-full font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
            </svg>
            {{ $labels['friendly']['label'] }} Friendly
        </span>
        <span class="inline-flex items-center gap-1.5 {{ $labels['reliable']['color'] }} px-3 py-1 rounded-full font-medium">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 0 1 2.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 0 0 .322-1.672V2.75a.75.75 0 0 1 .75-.75 2.25 2.25 0 0 1 2.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 0 1-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 0 0-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 0 1-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 0 0-1.302 4.665c0 1.194.232 2.333.654 3.375Z" />
            </svg>
            {{ $labels['reliable']['label'] }} Reliable
        </span>
        <a href="{{ route('reviews.seller', $owner->user_id) }}" class="inline-flex items-center gap-1.5 bg-gray-100 text-gray-700 px-3 py-1 rounded-full font-medium hover:bg-gray-200 transition">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.499Z" />
            </svg>
            View Reviews
        </a>
    </div>
</div>

{{-- ===================== MAIN CONTENT ===================== --}}
<section class="w-full max-w-[95rem] mx-auto mt-6 pb-20">
    <div class="grid grid-cols-12 gap-5">

        {{-- Left sidebar (desktop only) --}}
        <div class="hidden lg:block col-span-2">
            @include('public.components.advert.side-advert')
        </div>

        {{-- Listings area --}}
        <div class="col-span-12 lg:col-span-8">

            {{-- Section header --}}
            <div class="flex items-center justify-between mb-4 px-2">
                <h2 class="text-base font-semibold text-gray-800">
                    {{ $count_ads }} {{ Str::plural('listing', $count_ads) }} by {{ $owner->name }}
                </h2>
                <span class="text-xs text-gray-400">Sorted by: Latest</span>
            </div>

            {{-- Ads grid --}}
            <div id="ads-container">
                @if($ads->isEmpty())
                    <div class="flex flex-col items-center justify-center bg-white rounded-xl border border-gray-100 p-16 text-center">
                        <img src="https://img.icons8.com/external-outline-andi-nur-abdillah/100/external-Empty-empty-state-(outline)-outline-andi-nur-abdillah.png"
                             width="80" height="80" alt="No listings" class="mb-4 opacity-60">
                        <p class="text-gray-500 font-medium">No active listings at the moment</p>
                        <p class="text-gray-400 text-sm mt-1">Check back soon for new ads from {{ $owner->name }}</p>
                    </div>
                @else
                    @include('public.components.advert.advert-list', ['ads' => $ads])
                @endif
            </div>

            {{-- Load more --}}
            @if(isset($hasMore) && $hasMore)
                <div class="mt-5 mb-4 flex justify-center">
                    <button id="load-more-btn"
                            class="bg-dark_green hover:bg-green-800 text-white font-semibold py-3 px-20 rounded-lg transition duration-200 ease-in-out hover:scale-105 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        <span id="load-more-text">Show More</span>
                        <span id="load-more-spinner" class="hidden">
                            <svg class="animate-spin h-5 w-5 inline-block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Loading...
                        </span>
                    </button>
                </div>
            @endif
        </div>

        {{-- Right sidebar (desktop only) --}}
        <div class="hidden lg:block col-span-2">
            @include('public.components.advert.side-advert')
        </div>

    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const shareBtn = document.getElementById('share-store-btn');
    if (shareBtn) {
        shareBtn.addEventListener('click', async function () {
            const url  = this.dataset.url;
            const name = this.dataset.name;
            const label = document.getElementById('share-btn-label');
            if (navigator.share) {
                try { await navigator.share({ title: name + ' — Store', url }); } catch (_) {}
                return;
            }
            try {
                await navigator.clipboard.writeText(url);
                label.textContent = 'Link Copied!';
                shareBtn.classList.add('bg-white/30');
                setTimeout(() => { label.textContent = 'Share Store'; shareBtn.classList.remove('bg-white/30'); }, 2500);
            } catch (_) {
                Swal.fire({ icon: 'info', title: 'Store link', text: url, confirmButtonColor: '#166534' });
            }
        });
    }

    const followButtons = document.querySelectorAll('.follow-button');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    followButtons.forEach(button => {
        const userId = button.dataset.userId;
        checkFollowingStatus(button, userId);
        button.addEventListener('click', () => toggleFollow(button, userId));
    });

    async function checkFollowingStatus(button, userId) {
        try {
            const res = await fetch(`/api/check-following/${userId}`, { headers: { 'Accept': 'application/json' } });
            if (res.ok) { const data = await res.json(); updateButtonUI(userId, data.isFollowing); }
        } catch (e) {}
    }

    async function toggleFollow(button, userId) {
        try {
            const res = await fetch('/api/toggle-follow', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body: JSON.stringify({ followee_id: userId })
            });
            const data = await res.json();
            if (res.ok) {
                updateButtonUI(userId, data.isFollowing);
                Swal.fire({ icon: 'success', title: data.isFollowing ? 'Followed!' : 'Unfollowed!', text: data.message, timer: 2000, showConfirmButton: false, toast: true, position: 'top-end' });
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Could not update follow status', confirmButtonColor: '#d33' });
            }
        } catch (e) {}
    }

    function updateButtonUI(userId, isFollowing) {
        document.querySelectorAll(`.follow-button[data-user-id="${userId}"]`).forEach(btn => {
            const icon = isFollowing
                ? `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clip-rule="evenodd"/></svg>`
                : `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z"/></svg>`;
            btn.innerHTML = `<span>${icon}</span><span>${isFollowing ? 'Unfollow' : 'Follow'}</span>`;
        });
    }

    const loadMoreBtn = document.getElementById('load-more-btn');
    if (loadMoreBtn) {
        const adsContainer    = document.getElementById('ads-container');
        const loadMoreText    = document.getElementById('load-more-text');
        const loadMoreSpinner = document.getElementById('load-more-spinner');
        const sellerSlug      = @json($sellerSlug);
        const sellerId        = @json($owner->user_id);
        let currentPage       = 2;
        loadMoreBtn.addEventListener('click', function () {
            loadMoreBtn.disabled = true;
            loadMoreText.classList.add('hidden');
            loadMoreSpinner.classList.remove('hidden');
            fetch(`/seller/${sellerSlug}/${sellerId}/load-more?page=${currentPage}`)
                .then(r => r.json())
                .then(data => {
                    if (data.error) { alert(data.error); return; }
                    adsContainer.insertAdjacentHTML('beforeend', data.html);
                    currentPage++;
                    if (!data.hasMore) loadMoreBtn.style.display = 'none';
                    loadMoreBtn.disabled = false;
                    loadMoreText.classList.remove('hidden');
                    loadMoreSpinner.classList.add('hidden');
                })
                .catch(() => {
                    alert('Failed to load more listings. Please try again.');
                    loadMoreBtn.disabled = false;
                    loadMoreText.classList.remove('hidden');
                    loadMoreSpinner.classList.add('hidden');
                });
        });
    }
});
</script>

@include('public.layouts.footer')
