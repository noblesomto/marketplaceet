@include('public.layouts.header')

<div class="min-h-screen bg-gray-50">

    {{-- Top bar --}}
    <div class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-xl mx-auto px-4 h-14 flex items-center gap-3">
            <a href="/seller/{{ $sellerSlug }}/{{ $owner->user_id }}"
               class="flex-shrink-0 w-9 h-9 flex items-center justify-center rounded-full hover:bg-gray-100 transition-colors text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                </svg>
            </a>
            <div>
                <p class="text-xs text-gray-400 leading-none">{{ $owner->name }}</p>
                <h1 class="text-base font-bold text-gray-800 leading-tight">
                    {{ $tab === 'followers' ? 'Followers' : 'Following' }}
                </h1>
            </div>
        </div>
    </div>

    <div class="max-w-xl mx-auto">

        {{-- Tabs --}}
        <div class="bg-white border-b border-gray-200 flex">
            <a href="/seller/{{ $sellerSlug }}/{{ $owner->user_id }}/followers"
               class="flex-1 py-3.5 text-sm font-semibold text-center border-b-2 transition-colors
                      {{ $tab === 'followers' ? 'border-green-700 text-green-700' : 'border-transparent text-gray-400 hover:text-gray-600' }}">
                Followers
                <span class="ml-1 text-xs {{ $tab === 'followers' ? 'text-green-600' : 'text-gray-400' }}">{{ number_format($followers_count) }}</span>
            </a>
            <a href="/seller/{{ $sellerSlug }}/{{ $owner->user_id }}/following"
               class="flex-1 py-3.5 text-sm font-semibold text-center border-b-2 transition-colors
                      {{ $tab === 'following' ? 'border-green-700 text-green-700' : 'border-transparent text-gray-400 hover:text-gray-600' }}">
                Following
                <span class="ml-1 text-xs {{ $tab === 'following' ? 'text-green-600' : 'text-gray-400' }}">{{ number_format($following_count) }}</span>
            </a>
        </div>

        {{-- List --}}
        <div class="bg-white divide-y divide-gray-100" id="people-list">

            @forelse ($people as $p)
                <div class="flex items-center gap-3 px-4 py-3" data-user-row="{{ $p->user_id }}">

                    @php
                        $avatarColors = ['bg-green-100 text-green-700','bg-blue-100 text-blue-700','bg-purple-100 text-purple-700','bg-orange-100 text-orange-700','bg-pink-100 text-pink-700'];
                        $avatarColor  = $avatarColors[ord($p->name[0]) % count($avatarColors)];
                    @endphp

                    {{-- Avatar --}}
                    <a href="{{ $p->profile_url }}" class="flex-shrink-0">
                        <div class="w-11 h-11 rounded-full flex items-center justify-center text-sm font-bold {{ $avatarColor }}">
                            {{ $p->initials }}
                        </div>
                    </a>

                    {{-- Name + meta --}}
                    <div class="flex-1 min-w-0">
                        <a href="{{ $p->profile_url }}"
                           class="font-semibold text-sm text-gray-800 hover:text-green-700 leading-tight block truncate">{{ $p->name }}</a>
                        <p class="text-xs text-gray-400 leading-tight truncate">
                            @if ($p->state){{ $p->state }} · @endif{{ $p->active_ads }} {{ $p->active_ads === 1 ? 'listing' : 'listings' }}
                        </p>
                    </div>

                    {{-- Action buttons — right side, same row --}}
                    @unless ($p->is_self)
                    <div class="flex-shrink-0 flex items-center gap-1.5">
                        @if ($loggedIn)
                            @if ($isOwner && $tab === 'followers')
                                <button onclick="toggleFollow('{{ $p->user_id }}', this)"
                                    data-following="{{ $p->is_following ? 'true' : 'false' }}"
                                    class="follow-btn text-xs font-semibold px-2.5 py-1 rounded-full border transition-colors {{ $p->is_following ? 'bg-green-700 text-white border-green-700' : 'border-green-700 text-green-700 hover:bg-green-50' }}">
                                    {{ $p->is_following ? 'Following ✓' : '+ Follow Back' }}
                                </button>
                                <button onclick="removeFollower('{{ $p->user_id }}', this)"
                                    class="text-xs font-medium px-2.5 py-1 rounded-full border border-red-200 text-red-500 hover:bg-red-50 transition-colors">
                                    Remove
                                </button>
                            @elseif ($isOwner && $tab === 'following')
                                <button onclick="toggleFollow('{{ $p->user_id }}', this)"
                                    data-following="{{ $p->is_following ? 'true' : 'false' }}"
                                    class="follow-btn text-xs font-semibold px-2.5 py-1 rounded-full border transition-colors {{ $p->is_following ? 'bg-green-700 text-white border-green-700' : 'border-green-700 text-green-700 hover:bg-green-50' }}">
                                    {{ $p->is_following ? 'Unfollow' : '+ Follow' }}
                                </button>
                            @else
                                <button onclick="toggleFollow('{{ $p->user_id }}', this)"
                                    data-following="{{ $p->is_following ? 'true' : 'false' }}"
                                    class="follow-btn text-xs font-semibold px-2.5 py-1 rounded-full border transition-colors {{ $p->is_following ? 'bg-green-700 text-white border-green-700' : 'border-green-700 text-green-700 hover:bg-green-50' }}">
                                    {{ $p->is_following ? 'Following ✓' : '+ Follow' }}
                                </button>
                            @endif
                        @else
                            <a href="/login"
                               class="text-xs font-semibold px-2.5 py-1 rounded-full border border-green-700 text-green-700 hover:bg-green-50 transition-colors">
                                + Follow
                            </a>
                        @endif
                    </div>
                    @endunless

                </div>
            @empty
                <div class="py-16 text-center text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mx-auto mb-3 text-gray-200" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                    <p class="text-sm font-medium">No {{ $tab === 'followers' ? 'followers' : 'following' }} yet</p>
                </div>
            @endforelse
        </div>

        @if ($paginator->hasPages())
            <div class="px-4 py-6 bg-white border-t border-gray-100">
                {{ $paginator->links() }}
            </div>
        @endif

        <div class="h-8"></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const CSRF        = document.querySelector('meta[name="csrf-token"]')?.content;
const IS_OWNER    = @json($isOwner);
const CURRENT_TAB = @json($tab);

async function toggleFollow(userId, btn) {
    btn.disabled = true;
    btn.style.opacity = '0.6';
    try {
        const res  = await fetch('/api/toggle-follow', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({ followee_id: userId })
        });
        const data = await res.json();
        if (res.ok) {
            const isNow = data.isFollowing;
            btn.dataset.following = isNow ? 'true' : 'false';
            btn.textContent = (IS_OWNER && CURRENT_TAB === 'followers' && isNow)
                ? 'Following ✓'
                : (IS_OWNER && CURRENT_TAB === 'followers' && !isNow)
                    ? '+ Follow Back'
                    : (isNow ? 'Following ✓' : '+ Follow');
            btn.className = `follow-btn text-xs font-semibold px-3 py-1.5 rounded-full border transition-colors ${isNow ? 'bg-green-700 text-white border-green-700' : 'border-green-700 text-green-700 hover:bg-green-50'}`;
            if (IS_OWNER && CURRENT_TAB === 'following' && !isNow) {
                const row = document.querySelector(`[data-user-row="${userId}"]`);
                if (row) { row.style.opacity = '0'; row.style.transition = 'opacity 0.3s'; setTimeout(() => row.remove(), 300); }
            }
        }
    } catch(e) {}
    btn.disabled = false;
    btn.style.opacity = '';
}

async function removeFollower(userId, btn) {
    const result = await Swal.fire({
        title: 'Remove follower?',
        text: 'This person will no longer follow you.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, remove',
        cancelButtonText: 'Cancel',
    });
    if (!result.isConfirmed) return;
    btn.disabled = true;
    try {
        const res = await fetch(`/api/social/remove-follower/${userId}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF }
        });
        if (res.ok) {
            const row = document.querySelector(`[data-user-row="${userId}"]`);
            if (row) { row.style.opacity = '0'; row.style.transition = 'opacity 0.3s'; setTimeout(() => row.remove(), 300); }
            Swal.fire({ icon: 'success', title: 'Removed', timer: 1500, showConfirmButton: false, toast: true, position: 'top-end' });
        }
    } catch(e) {}
    btn.disabled = false;
}
</script>

@include('public.layouts.footer')
