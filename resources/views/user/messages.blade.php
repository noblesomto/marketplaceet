@include('user.layouts.header')
@include('user.layouts.nav')
@include('user.layouts.back-nav')
@include('user.layouts.search')

<style>
.msg-wrap {
    width: 100%;
    max-width: 1100px;
    margin: 24px auto 0;
    padding: 0 12px;
}

/* ── Two-column layout on desktop ── */
.msg-layout {
    display: flex;
    gap: 20px;
    align-items: flex-start;
    padding-bottom: 80px;
}
.msg-main {
    flex: 1 1 0;
    min-width: 0;
}
.msg-sidebar-col {
    width: 290px;
    flex-shrink: 0;
    display: none;
}
@media (min-width: 1024px) {
    .msg-sidebar-col { display: block; }
}

/* ── Panel shell ── */
.msg-panel {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
}

/* ── Header ── */
.msg-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 20px;
    border-bottom: 1px solid #f0f0f0;
}
.msg-header-title {
    font-size: 1rem;
    font-weight: 700;
    color: #1a1a2e;
    display: flex;
    align-items: center;
    gap: 8px;
}
.msg-header-title i { color: #326916; font-size: 1.1rem; }

/* ── Conversation item ── */
.conv-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 20px;
    border-bottom: 1px solid #f5f5f5;
    text-decoration: none;
    color: inherit;
    transition: background 0.15s;
    position: relative;
}
.conv-item:hover { background: #f9fafb; text-decoration: none; color: inherit; }
.conv-item.unread { background: #f0faf0; }
.conv-item.unread:hover { background: #e8f5e2; }

/* Ad thumbnail */
.conv-ad-thumb {
    width: 54px;
    height: 54px;
    border-radius: 8px;
    object-fit: cover;
    flex-shrink: 0;
    border: 1px solid #e5e7eb;
    background: #f3f4f6;
}

/* Text block */
.conv-body { flex: 1; min-width: 0; }
.conv-name {
    font-size: 0.875rem;
    font-weight: 600;
    color: #111827;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.conv-ad-title {
    font-size: 0.75rem;
    color: #326916;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-top: 1px;
}
.conv-preview {
    font-size: 0.72rem;
    color: #9ca3af;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-top: 2px;
}
.conv-item.unread .conv-name { color: #111827; }
.conv-item.unread .conv-preview { color: #6b7280; font-weight: 500; }

/* Right: time + badge */
.conv-meta {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 6px;
    flex-shrink: 0;
}
.conv-time {
    font-size: 0.68rem;
    color: #9ca3af;
    white-space: nowrap;
}
.conv-badge {
    background: #326916;
    color: #fff;
    font-size: 0.65rem;
    font-weight: 700;
    min-width: 18px;
    height: 18px;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0 5px;
}

/* ── Empty state ── */
.msg-empty {
    padding: 60px 20px;
    text-align: center;
    color: #9ca3af;
}
.msg-empty i { font-size: 3rem; color: #d1d5db; margin-bottom: 12px; }
.msg-empty p { font-size: 0.875rem; margin-top: 6px; }

/* ── Sidebar card ── */
.sidebar-card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    overflow: hidden;
}
.sidebar-header {
    background: linear-gradient(135deg, #326916 0%, #4a9c24 100%);
    padding: 20px;
    text-align: center;
}
.sidebar-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(255,255,255,0.25);
    border: 3px solid rgba(255,255,255,0.6);
    margin: 0 auto 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
    color: #fff;
    overflow: hidden;
}
.sidebar-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
.sidebar-name { color: #fff; font-weight: 700; font-size: 0.95rem; }
.sidebar-since { color: rgba(255,255,255,0.8); font-size: 0.72rem; margin-top: 2px; }

.sidebar-body { padding: 16px; }

.sidebar-stat {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 10px;
    border-radius: 8px;
    font-size: 0.78rem;
    color: #4b5563;
    margin-bottom: 4px;
    background: #f9fafb;
}
.sidebar-stat i { color: #326916; font-size: 0.9rem; width: 16px; text-align: center; }

.sidebar-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 10px;
    border-radius: 20px;
    background: #f0faf0;
    border: 1px solid #c6e6b0;
    font-size: 0.72rem;
    color: #326916;
    font-weight: 500;
    margin-bottom: 6px;
}
.sidebar-badge i { font-size: 0.8rem; }

.sidebar-divider { border: none; border-top: 1px solid #f0f0f0; margin: 12px 0; }

.sidebar-link {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    padding: 9px 12px;
    border-radius: 8px;
    font-size: 0.8rem;
    color: #6b7280;
    border: 1px solid #e5e7eb;
    background: #fff;
    text-decoration: none;
    transition: all 0.15s;
}
.sidebar-link:hover { background: #f9fafb; color: #326916; border-color: #c6e6b0; text-decoration: none; }
.sidebar-link i { font-size: 0.9rem; }

.sidebar-ads-count {
    font-size: 1.4rem;
    font-weight: 700;
    color: #326916;
}
.sidebar-ads-label {
    font-size: 0.72rem;
    color: #9ca3af;
}

</style>

<div class="msg-wrap">

    @include('public.components.flash-message')

    <div class="msg-layout">

        {{-- ── LEFT: Conversations ── --}}
        <div class="msg-main">
            <div class="msg-panel">

                {{-- Header --}}
                <div class="msg-header">
                    <div class="msg-header-title">
                        <i class="bi bi-chat-dots-fill"></i>
                        Messages
                        @php $totalUnread = $conversations->sum('unread_count'); @endphp
                        @if($totalUnread > 0)
                            <span style="background:#326916;color:#fff;font-size:0.65rem;font-weight:700;padding:2px 7px;border-radius:10px;">
                                {{ $totalUnread }}
                            </span>
                        @endif
                    </div>

                    <a href="/user/archived-messages"
                       style="display:inline-flex;align-items:center;gap:6px;font-size:0.78rem;color:#6b7280;border:1px solid #e5e7eb;padding:6px 12px;border-radius:8px;text-decoration:none;transition:all 0.15s;"
                       onmouseover="this.style.color='#326916';this.style.borderColor='#c6e6b0';this.style.background='#f0faf0';"
                       onmouseout="this.style.color='#6b7280';this.style.borderColor='#e5e7eb';this.style.background='';">
                        <i class="bi bi-archive"></i> Archived
                    </a>
                </div>

                {{-- List --}}
                @if (!$conversations->isEmpty())
                    @foreach($conversations as $conversation)
                        @php
                            $isUnread = $conversation['unread_count'] > 0;
                            $otherUser = $conversation['other_user'];
                            $advert    = $conversation['advert'];
                            $lastMsg   = $conversation['last_message'];
                            $lastAt    = $conversation['last_message_at']
                                ? \Carbon\Carbon::parse($conversation['last_message_at'])->diffForHumans()
                                : '';
                            $advertImg = $advert ? $advert->getFirstImageUrl('thumbnail') : null;
                            $userName  = $otherUser ? $otherUser->name : 'Unknown';
                            $initial   = strtoupper(substr($userName, 0, 1));
                        @endphp
                        <a href="{{ route('chat.show', ['advertId' => $advert->id, 'receiverId' => $otherUser->user_id]) }}"
                           class="conv-item {{ $isUnread ? 'unread' : '' }}">

                            {{-- Ad thumbnail --}}
                            <img class="conv-ad-thumb"
                                 src="{{ $advertImg ?: asset('frontend/images/default.png') }}"
                                 alt="{{ $advert->ad_title ?? '' }}"
                                 onerror="this.src='{{ asset('frontend/images/default.png') }}'">

                            {{-- Text --}}
                            <div class="conv-body">
                                <div class="conv-name">{{ $userName }}</div>
                                <div class="conv-ad-title">{{ $advert->ad_title ?? '' }}</div>
                                @if($lastMsg)
                                    <div class="conv-preview">
                                        @if($lastMsg->sender_id === session('user_id'))
                                            <span style="color:#326916;font-weight:600;">You:</span>
                                        @endif
                                        {{ Str::limit(strip_tags($lastMsg->message ?? ''), 55) }}
                                    </div>
                                @endif
                            </div>

                            {{-- Time + badge --}}
                            <div class="conv-meta">
                                <span class="conv-time">{{ $lastAt }}</span>
                                @if($isUnread)
                                    <span class="conv-badge">{{ $conversation['unread_count'] }}</span>
                                @endif
                            </div>
                        </a>
                    @endforeach
                @else
                    <div class="msg-empty">
                        <div><i class="bi bi-chat-square-text"></i></div>
                        <div style="font-size:1rem;font-weight:600;color:#374151;">No messages yet</div>
                        <p>Your conversations will appear here when you start messaging sellers.</p>
                    </div>
                @endif

            </div>
        </div>

        {{-- ── RIGHT: Sidebar ── --}}
        <div class="msg-sidebar-col">
            <div class="sidebar-card">

                {{-- Green header with avatar --}}
                <div class="sidebar-header">
                    <div class="sidebar-avatar">
                        @if($user->profile_thumbnail_url)
                            <img src="{{ $user->profile_thumbnail_url }}" alt="{{ $user->name }}"
                                 onerror="this.parentElement.innerHTML='{{ strtoupper(substr($user->name,0,1)) }}'">
                        @else
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        @endif
                    </div>
                    <div class="sidebar-name">{{ $user->name }}</div>
                    <div class="sidebar-since">Member since {{ $user->created_at->format('M Y') }}</div>
                </div>

                <div class="sidebar-body">

                    {{-- Ads stat --}}
                    <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0 12px;">
                        <div>
                            <div class="sidebar-ads-count">{{ $count_ads }}</div>
                            <div class="sidebar-ads-label">Ads Online</div>
                        </div>
                        <a href="/user/my-ads"
                           style="font-size:0.75rem;color:#326916;font-weight:600;text-decoration:none;border:1px solid #c6e6b0;padding:5px 12px;border-radius:20px;background:#f0faf0;">
                            View all
                        </a>
                    </div>

                    <hr class="sidebar-divider">

                    {{-- Reputation badges --}}
                    <div style="font-size:0.72rem;font-weight:600;color:#9ca3af;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Reputation</div>

                    <div class="sidebar-badge">
                        <i class="bi bi-emoji-smile"></i> Top Satisfaction
                    </div>
                    <div class="sidebar-badge">
                        <i class="bi bi-people"></i> Particularly Friendly
                    </div>
                    <div class="sidebar-badge">
                        <i class="bi bi-hand-thumbs-up"></i> Particularly Reliable
                    </div>

                    <hr class="sidebar-divider">

                    {{-- Info rows --}}
                    <div class="sidebar-stat">
                        <i class="bi bi-person"></i>
                        <span>{{ $user->acc_type ?? 'Private' }} User</span>
                    </div>
                    <div class="sidebar-stat">
                        <i class="bi bi-calendar3"></i>
                        <span>Active since {{ $user->created_at->format('j M Y') }}</span>
                    </div>

                    <hr class="sidebar-divider">

                    {{-- Quick links --}}
                    <a href="/user/archived-messages" class="sidebar-link">
                        <i class="bi bi-archive"></i> Archived Messages
                    </a>

                </div>
            </div>
        </div>

    </div>
</div>

@include('user.layouts.footer')
