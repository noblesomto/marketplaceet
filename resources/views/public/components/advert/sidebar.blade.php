@if ($ad->sold != 'Yes')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

    <!-- User Profile Info -->
    <div class="p-5 border-b border-gray-100">
        <div class="flex items-start gap-3">
            <a href="/seller/{{ \Illuminate\Support\Str::slug($ad->owner->name) }}/{{ $ad->owner->user_id }}" class="shrink-0 relative">
                <img class="w-12 h-12 rounded-full object-cover border border-gray-200" src="{{ $ad->owner->profile_thumbnail_url }}" alt="{{ $ad->owner->name }}">
                @if($ad->owner->verified=='yes')
                    <span class="absolute -bottom-1 -right-1 bg-green-500 text-white p-0.5 rounded-full border-2 border-white" title="Verified Seller">
                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>
                    </span>
                @endif
            </a>
            <div class="min-w-0 flex-1">
                <a href="/seller/{{ \Illuminate\Support\Str::slug($ad->owner->name) }}/{{ $ad->owner->user_id }}" class="block font-bold text-gray-900 truncate hover:text-dark_green">{{ $ad->owner->name }}</a>
                <div class="text-xs text-gray-500 mt-0.5" data-nosnippet>
                    Active since {{ date('j F Y', strtotime($ad->owner->created_at)) }}
                </div>
                <div class="text-xs text-gray-500 mt-0.5" data-nosnippet>
                    Last Seen:
                    @php
                        // last_seen_at reflects real activity (App\Http\Middleware\UpdateLastSeen),
                        // last_login_at only moves on a fresh login/token event — fall back to it
                        // for users seen before last_seen_at existed.
                        $lastSeen = \Carbon\Carbon::parse($ad->owner->last_seen_at ?? $ad->owner->last_login_at);
                        if ($lastSeen->isToday()) {
                            echo 'Today at ' . $lastSeen->format('g:i A');
                        } elseif ($lastSeen->isYesterday()) {
                            echo 'Yesterday at ' . $lastSeen->format('g:i A');
                        } else {
                            echo $lastSeen->diffForHumans();
                        }
                    @endphp
                </div>
            </div>
        </div>

        @if(session()->get('user_id') != '' && $ad->owner->user_id != $user->user_id)
        <button id="followButton" data-user-id="{{ $ad->owner->user_id }}" class="mt-4 w-full py-2 text-sm font-medium border border-dark_green text-dark_green rounded-lg hover:bg-dark_green hover:text-white transition active:scale-95">
            <span class="flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4 mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
                </svg>
                <span>Follow Seller</span>
            </span>
        </button>
        @endif
    </div>

    <!-- Seller Rankings / Feedback -->
    <div class="p-5 border-b border-gray-100 bg-gray-50/50">
        @php $labels = feedback_rating_labels($ad->owner->user_id); @endphp
        <div class="grid grid-cols-2 gap-2 text-xs text-gray-700">
            <div class="flex items-center gap-1 p-2 rounded-md {{ $labels['satisfaction']['color'] }}">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.182 15.182a4.5 4.5 0 01-6.364 0M21 12a9 9 0 11-18 0 9 9 0 0118 0ZM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75Zm-.375 0h.008v.015h-.008V9.75Z"></path></svg>
                <span>{{ $labels['satisfaction']['label'] }} Satisfied</span>
            </div>
            <div class="flex items-center gap-1 p-2 rounded-md {{ $labels['friendly']['color'] }}">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0ZM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766Z"></path></svg>
                <span>{{ $labels['friendly']['label'] }} Friendly</span>
            </div>
            <div class="flex items-center gap-1 p-2 rounded-md {{ $labels['reliable']['color'] }}">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6.633 10.25c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 012.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 00.322-1.672V2.75a.75.75 0 01.75-.75 2.25 2.25 0 012.25 2.25c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282m0 0h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 01-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 00-1.423-.23H5.904m10.598-9.75H14.25M5.904 18.5c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 01-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 9.953 4.167 9.5 5 9.5h1.053c.472 0 .745.556.5.96a8.958 8.958 0 00-1.302 4.665c0 1.194.232 2.333.654 3.375Z"></path></svg>
                <span>{{ $labels['reliable']['label'] }} Reliable</span>
            </div>
            <div class="flex items-center gap-1 p-2 rounded-md bg-gray-100 text-gray-700">
                @if($ad->owner->acc_type=="Private")
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0ZM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632Z"></path></svg>
                <span>Private User</span>
                @else
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72l1.189-1.19A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72M6.75 18h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .414.336.75.75.75Z"></path></svg>
                <span>Business User</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Stats and Seller's Ads -->
    <div class="grid grid-cols-2 divide-x divide-gray-100 bg-gray-50 border-b border-gray-100">
        <a class="p-3 text-center" href="/reviews/seller/{{ $ad->owner->user_id }}">
            <div class="">
                <span class="block font-bold text-gray-800 text-lg hover:text-dark_green">{{ get_user_feedback_averages($ad->owner->user_id)['count'] }}</span>
                <span class="text-xs text-gray-500">Reviews</span>
            </div>
        </a>
        <a class="p-3 text-center" href="/seller/{{ \Illuminate\Support\Str::slug($ad->owner->name) }}/{{ $ad->owner->user_id }}">
            <div class="">
                <span class="block font-bold text-gray-800 text-lg hover:text-dark_green">{{ $count_ads ?? 0 }}</span>
                <span class="text-xs text-gray-500">Ads Online</span>
            </div>
        </a>


    </div>

    <!-- Desktop CTAs (Hidden on Mobile, handled by fixed bottom bar) -->
    <div class="p-5 space-y-3 hidden lg:block">
        @if($cat->category !="Jobs")
            @if ($ad->buy_direct == 'Yes' && $ad->sold !== 'Yes' && session('user_id') !== $ad->user_id)
            <a href="/buy-direct/{{ $ad->ad_id }}" class="flex justify-center items-center w-full py-3 bg-secondary_dark text-white font-bold rounded-lg shadow hover:opacity-90 transition active:scale-95">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="size-5 accent-bg_primary mr-2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                </svg>
                Buy Direct (Secure)
            </a>
            @endif

            <div class="grid grid-cols-2 gap-3">
                 <a href="/chat/{{ $ad->id }}/{{ $ad->user_id }}" class="flex justify-center items-center py-2 border-2 border-green-600 text-green-700 font-bold rounded-lg hover:bg-green-50 transition active:scale-95">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    Chat
                </a>
                @if($ad->show_contact=="Yes")
                <button id="showContact" class="flex justify-center items-center py-2 border-2 border-blue-600 text-blue-700 font-bold rounded-lg hover:bg-blue-50 transition active:scale-95">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                    Call
                </button>
                @endif
            </div>

            <div id="contactPhone" class="hidden mt-2 p-3 bg-blue-50 text-blue-900 rounded-lg text-center border border-blue-100">
                @if(session()->get('user_id') =='')
                    <a href="/login" class="font-bold underline">Login to see number</a>
                @else
                    <a href="tel:{{ $ad->owner->phone }}" class="text-xl font-bold">{{ $ad->owner->phone }}</a>
                @endif
            </div>
        @endif
    </div>

    <!-- Safety Tips -->
    <div class="bg-yellow-50 p-4">
        <h4 class="font-bold text-yellow-800 text-sm mb-2">Safety Tips</h4>
        @if($ad->category==3)
            <ul class="text-xs text-yellow-800 space-y-1 list-disc list-inside">
                <li class="">Never pay to apply or attend an interview</li>
                <li class="">Research the company and verify their details beforehand</li>
                <li class="">Only attend interviews at official company addresses</li>
                <li class="">Share personal information only after receiving a confirmed job offer</li>
            </ul>
            @elseif($ad->category==11)
            <ul class="text-xs text-yellow-800 space-y-1 list-disc list-inside">
                <li class="">Clearly define the work and payment terms in advance</li>
                <li class="">Review ratings and feedback to confirm reliability</li>
                <li class="">Arrange meetings only in safe, public locations</li>
                <li class="">Keep a record of all agreements and communications for future reference</li>
            </ul>
            @elseif($ad->category==18)
            <ul class="text-xs text-yellow-800 space-y-1 list-disc list-inside">
                <li class="">Check the candidate’s background before arranging interviews</li>
                <li class="">Keep sensitive personal details private</li>
                <li class="">Avoid clicking on suspicious links outside the provided CV</li>
                <li class="">Hold interviews only in a secure, professional setting</li>
            </ul>
            @else
            <ul class="text-xs text-yellow-800 space-y-1 list-disc list-inside">
                <li class="">Never pay upfront before inspecting the item.</li>
                <li class="">Meet in a safe, public location.</li>
                <li class="">Check the item carefully to ensure it matches the listing.</li>
                <li class="">Verify all documents and pay only when you're fully satisfied.</li>
                <li class="">Always use the <strong>"Buy Direct"</strong> option (if available) to enjoy <strong>100% Buyer Protection</strong>.</li>
            </ul>
        @endif
        <div class="mt-3 flex justify-between pt-3 border-t border-yellow-200">
            <a href="/report-ad/{{ $ad->id }}" class="text-xs text-red-600 font-semibold hover:underline p-1 rounded border border-red-600">Report this Ad</a>
            <button id="openModalShare" class="text-xs text-dark_green font-semibold hover:underline p-1 rounded border border-dark_green">Share Ad</button>
        </div>
    </div>

    <div class="mt-4 p-4 text-center">
        <a href="/user/post-ad" class="flex justify-center items-center py-2 border-2 border-green-600 text-green-700 font-bold rounded-lg hover:bg-green-50 transition active:scale-95">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Post your own Ad for Free
        </a>

    </div>
</div>
@endif
