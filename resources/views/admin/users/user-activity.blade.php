@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">
    <div class="pagetitle">
        <div class="d-flex justify-content-between align-items-center">
            <h1>User Activity</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/active-users">Users</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.view.user', $user->user_id) }}">{{ $user->name }}</a></li>
                    <li class="breadcrumb-item active">Activity</li>
                </ol>
            </nav>
        </div>
    </div><!-- End Page Title -->

    <section class="section">

        @php
            $initials = collect(explode(' ', trim($user->name ?? '?')))->map(fn($p) => mb_substr($p, 0, 1))->take(2)->implode('');
        @endphp

        <div class="user-hero">
            <div class="user-avatar">{{ strtoupper($initials ?: '?') }}</div>
            <div class="user-hero-info">
                <h2 class="user-hero-name">{{ $user->name ?? 'Unnamed user' }}</h2>
                <div class="user-hero-email">{{ $user->email }} · ID {{ $user->user_id }}</div>
            </div>
            <a href="{{ route('admin.view.user', $user->user_id) }}" class="action-btn action-btn-neutral ms-auto">
                <i class="bi bi-arrow-left"></i>
                <span>Back to profile</span>
            </a>
        </div>

        <div class="section-card">
            <div class="section-title">
                <i class="bi bi-clock-history"></i>Activity timeline
            </div>
            <div class="section-body">
                <form method="GET" class="filter-bar">
                    <select name="log_name" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All activity types</option>
                        @foreach($logNames as $logName)
                            <option value="{{ $logName }}" {{ request('log_name') === $logName ? 'selected' : '' }}>
                                {{ ucfirst($logName) }}
                            </option>
                        @endforeach
                    </select>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}" placeholder="From" onchange="this.form.submit()">
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}" placeholder="To" onchange="this.form.submit()">
                    @if(request()->hasAny(['log_name', 'from', 'to']))
                        <a href="{{ route('admin.user.activity', $user->user_id) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-x-circle"></i> Clear
                        </a>
                    @endif
                </form>

                @forelse($activities as $activity)
                    @php
                        $isSelf = $activity->causer instanceof \App\Models\User && $activity->causer->user_id === $user->user_id;
                        $causerLabel = match(true) {
                            $activity->causer === null => 'System',
                            $isSelf => 'This user',
                            $activity->causer instanceof \App\Models\Admin => ($activity->causer->username ?? 'Admin') . ' (admin)',
                            $activity->causer instanceof \App\Models\User => $activity->causer->name . ' (user)',
                            default => 'Unknown',
                        };
                        $icon = match($activity->log_name) {
                            'auth' => 'bi-box-arrow-in-right',
                            'advert' => 'bi-megaphone',
                            'profile' => 'bi-person-gear',
                            'message' => 'bi-chat-dots',
                            'account' => 'bi-shield-exclamation',
                            default => 'bi-dot',
                        };
                    @endphp
                    <div class="activity-row">
                        <div class="activity-icon"><i class="bi {{ $icon }}"></i></div>
                        <div class="activity-body">
                            <div class="activity-desc">{{ $activity->description }}</div>
                            <div class="activity-meta">
                                <span>{{ $causerLabel }}</span>
                                <span>·</span>
                                <span>{{ $activity->created_at->format('d M Y, g:i A') }}</span>
                                <span>·</span>
                                <span class="text-muted">{{ $activity->created_at->diffForHumans() }}</span>
                            </div>
                            @if($activity->properties && $activity->properties->isNotEmpty())
                                <details class="activity-details">
                                    <summary>Details</summary>
                                    <pre>{{ json_encode($activity->properties, JSON_PRETTY_PRINT) }}</pre>
                                </details>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5">
                        <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                        <p class="mt-2 text-muted">No activity recorded for this account yet.</p>
                    </div>
                @endforelse

                @if($activities->total() > 0)
                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $activities->firstItem() }} to {{ $activities->lastItem() }} of {{ $activities->total() }} entries
                        </div>
                        {{ $activities->links('pagination::bootstrap-4') }}
                    </div>
                @endif
            </div>
        </div>

    </section>
</main><!-- End #main -->

<style>
    .user-hero {
        display: flex;
        align-items: center;
        gap: 1.25rem;
        flex-wrap: wrap;
        background: linear-gradient(135deg, #eef3ff 0%, #f6f9ff 100%);
        border: 1px solid #e6ecfb;
        border-radius: 0.9rem;
        padding: 1.5rem 1.75rem;
        margin-bottom: 1.25rem;
    }
    .user-avatar {
        width: 60px;
        height: 60px;
        flex-shrink: 0;
        border-radius: 50%;
        background: #0d6efd;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: "Nunito", sans-serif;
        font-weight: 700;
        font-size: 1.35rem;
    }
    .user-hero-name {
        font-family: "Nunito", sans-serif;
        font-weight: 700;
        font-size: 1.3rem;
        color: #1c2333;
        margin: 0 0 0.15rem;
    }
    .user-hero-email {
        color: #6c757d;
        font-size: 0.925rem;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.55rem 1.1rem;
        border-radius: 0.5rem;
        border: 1px solid transparent;
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        text-decoration: none;
        transition: filter 0.15s ease, transform 0.1s ease;
    }
    .action-btn:hover { filter: brightness(0.96); transform: translateY(-1px); color: inherit; }
    .action-btn-neutral { background: #fff; color: #495464; border-color: #dde2e9; }

    .section-card {
        background: #fff;
        border: 1px solid #eef1f6;
        border-radius: 0.75rem;
        overflow: hidden;
    }
    .section-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.85rem 1.15rem;
        font-weight: 700;
        font-size: 0.92rem;
        color: #33394b;
        background: #f8f9fc;
        border-bottom: 1px solid #eef1f6;
    }
    .section-title i { color: #0d6efd; }
    .section-body { padding: 1rem 1.15rem; }

    .filter-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1.1rem;
    }
    .filter-bar select, .filter-bar input { max-width: 200px; }

    .activity-row {
        display: flex;
        gap: 0.85rem;
        padding: 0.85rem 0;
        border-bottom: 1px solid #f1f3f7;
    }
    .activity-row:last-child { border-bottom: none; }
    .activity-icon {
        width: 34px;
        height: 34px;
        flex-shrink: 0;
        border-radius: 50%;
        background: #eef3ff;
        color: #0d6efd;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.95rem;
    }
    .activity-desc { font-weight: 600; color: #1f2430; font-size: 0.925rem; }
    .activity-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        font-size: 0.8rem;
        color: #8a93a2;
        margin-top: 0.15rem;
    }
    .activity-details { margin-top: 0.4rem; }
    .activity-details summary {
        cursor: pointer;
        font-size: 0.78rem;
        color: #0d6efd;
        font-weight: 600;
    }
    .activity-details pre {
        background: #f8f9fc;
        border: 1px solid #eef1f6;
        border-radius: 0.5rem;
        padding: 0.65rem;
        font-size: 0.78rem;
        margin-top: 0.4rem;
        white-space: pre-wrap;
        word-break: break-word;
    }

    @media (max-width: 576px) {
        .filter-bar select, .filter-bar input { max-width: 100%; flex: 1 1 100%; }
    }
</style>

@include('admin.layouts.footer')
