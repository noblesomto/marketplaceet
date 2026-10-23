@include('admin.layouts.header')
@include('admin.layouts.nav')

<style>
/* ═══════════════════════════════════════════════
   ADMIN DASHBOARD – clean, card-based redesign
   ═══════════════════════════════════════════════ */

/* ── page wrapper ─────────────────────────────── */
.dash-page { padding: 0 4px; }

/* ── welcome banner ───────────────────────────── */
.dash-welcome {
    background: linear-gradient(135deg, #1a2d5a 0%, #1d4ed8 100%);
    border-radius: 14px;
    padding: 26px 32px;
    margin-bottom: 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    position: relative;
    overflow: hidden;
}
.dash-welcome::before {
    content: '';
    position: absolute; right: -60px; top: -60px;
    width: 260px; height: 260px;
    border-radius: 50%;
    background: rgba(255,255,255,0.04);
    pointer-events: none;
}
.dash-welcome-text h1 { font-size: 1.55rem; font-weight: 700; color: #fff; margin: 0 0 4px; }
.dash-welcome-text p  { color: rgba(255,255,255,0.72); font-size: 0.9rem; margin: 0; }
.dash-welcome-badge {
    background: rgba(255,255,255,0.12);
    border: 1px solid rgba(255,255,255,0.2);
    color: rgba(255,255,255,0.9);
    border-radius: 20px;
    padding: 5px 16px;
    font-size: 0.78rem;
    font-weight: 600;
    letter-spacing: 0.3px;
    white-space: nowrap;
    flex-shrink: 0;
}
.dash-welcome-date {
    color: rgba(255,255,255,0.55);
    font-size: 0.8rem;
    margin-top: 3px;
}

/* ── quick actions ────────────────────────────── */
.dash-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-bottom: 28px;
}
.dash-action-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 18px;
    border-radius: 8px;
    font-size: 0.84rem;
    font-weight: 600;
    text-decoration: none;
    transition: transform 0.15s, box-shadow 0.15s;
    flex-shrink: 0;
}
.dash-action-btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
.dash-action-btn i { font-size: 1rem; }

/* ── section heading ──────────────────────────── */
.dash-section-heading {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 14px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e9ecef;
}
.dash-section-heading h2 {
    font-size: 1rem;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}
.dash-section-heading p { margin: 0; font-size: 0.8rem; color: #94a3b8; }

/* ── main KPI card ────────────────────────────── */
.kpi-card {
    background: #fff;
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    margin-bottom: 24px;
    overflow: hidden;
}

/* card header strip */
.kpi-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 22px;
    border-bottom: 1px solid #f1f5f9;
    gap: 12px;
}
.kpi-card-header-left { display: flex; align-items: center; gap: 14px; }
.kpi-card-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}
.kpi-card-icon.users    { background: #eef0fd; color: #4361ee; }
.kpi-card-icon.adverts  { background: #fdeaeb; color: #e63946; }
.kpi-card-icon.boosts   { background: #e0f4fb; color: #0096c7; }
.kpi-card-icon.payments { background: #e8f5ec; color: #2d9a4e; }

.kpi-card-title { font-size: 0.97rem; font-weight: 700; color: #1e293b; margin: 0 0 2px; }
.kpi-card-sub   { font-size: 0.75rem; color: #94a3b8; margin: 0; }
.kpi-card-link  {
    font-size: 0.78rem; font-weight: 600; color: #64748b;
    text-decoration: none; white-space: nowrap; flex-shrink: 0;
    display: inline-flex; align-items: center; gap: 4px;
}
.kpi-card-link:hover { color: #1e293b; }

/* ── stat grid (primary + secondary) ─────────── */
.kpi-stats-grid {
    display: grid;
    grid-template-columns: minmax(160px, 1.8fr) repeat(3, 1fr);
    border-bottom: 1px solid #f1f5f9;
}
.kpi-stats-grid.cols-4 { grid-template-columns: minmax(160px, 1.8fr) repeat(3, 1fr); }
.kpi-stats-grid.cols-5 { grid-template-columns: minmax(160px, 1.8fr) repeat(4, 1fr); }
.kpi-stats-grid.cols-6 { grid-template-columns: minmax(160px, 1.8fr) repeat(5, 1fr); }

.kpi-stat {
    padding: 22px 24px;
    border-right: 1px solid #f1f5f9;
    position: relative;
}
.kpi-stat:last-child { border-right: none; }

/* primary (first) stat */
.kpi-stat.primary { background: #fafbff; }
.kpi-stat.primary .stat-num { font-size: 2.6rem; font-weight: 800; color: #0f172a; line-height: 1; }
.kpi-stat.primary .stat-label { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #64748b; margin-top: 6px; }
.kpi-stat.primary .stat-sub { font-size: 0.76rem; color: #94a3b8; margin-top: 4px; line-height: 1.5; }

/* secondary stats */
.kpi-stat .stat-num { font-size: 1.7rem; font-weight: 700; color: #1e293b; line-height: 1; }
.kpi-stat .stat-label { font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.7px; color: #94a3b8; margin-top: 5px; }
.kpi-stat .stat-badge {
    display: inline-block;
    margin-top: 6px;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 0.68rem;
    font-weight: 600;
}
.stat-badge.green  { background: #dcfce7; color: #166534; }
.stat-badge.red    { background: #fee2e2; color: #991b1b; }
.stat-badge.amber  { background: #fef3c7; color: #92400e; }
.stat-badge.blue   { background: #dbeafe; color: #1e40af; }
.stat-badge.gray   { background: #f1f5f9; color: #475569; }

/* source pair (App / Web) */
.stat-source-pair { display: flex; gap: 20px; margin-top: 4px; }
.stat-source-item .stat-num { font-size: 1.35rem; }
.stat-source-label { font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #94a3b8; margin-top: 3px; }

/* colored stat num variants */
.stat-num.danger  { color: #dc2626; }
.stat-num.warning { color: #d97706; }
.stat-num.success { color: #16a34a; }

/* ── trend strip ──────────────────────────────── */
.kpi-trend-strip {
    background: #f8fafc;
    display: flex;
    align-items: stretch;
    border-top: 1px solid #f1f5f9;
}
.kpi-trend-strip-label {
    padding: 0 18px;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.7px;
    color: #94a3b8;
    white-space: nowrap;
    border-right: 1px solid #e9ecef;
    min-width: 110px;
    background: #f1f5f9;
}
.kpi-trend-periods { display: flex; flex: 1; }
.kpi-trend-period {
    flex: 1;
    padding: 12px 16px;
    border-right: 1px solid #e9ecef;
    text-align: center;
}
.kpi-trend-period:last-child { border-right: none; }
.kpi-trend-value { font-size: 1.1rem; font-weight: 700; color: #1e293b; line-height: 1; }
.kpi-trend-value.currency { font-size: 0.95rem; }
.kpi-trend-plabel {
    font-size: 0.62rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #cbd5e1;
    margin-top: 4px;
}

/* ── mini stat cards (shipping, reports, etc.) ── */
.mini-kpi-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    padding: 22px 24px;
    margin-bottom: 24px;
    border-top: 4px solid #dee2e6;
    position: relative;
    overflow: hidden;
}
.mini-kpi-card.shipping   { border-top-color: #e76f51; }
.mini-kpi-card.reports    { border-top-color: #6c757d; }
.mini-kpi-card.categories { border-top-color: #7209b7; }
.mini-kpi-card.blog       { border-top-color: #f4a261; }

.mini-kpi-icon {
    position: absolute; top: 20px; right: 20px;
    width: 44px; height: 44px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem;
}
.mini-kpi-icon.shipping   { background: #fdf0ec; color: #e76f51; }
.mini-kpi-icon.reports    { background: #f1f5f9; color: #6c757d; }
.mini-kpi-icon.categories { background: #f3e8fd; color: #7209b7; }
.mini-kpi-icon.blog       { background: #fef7ec; color: #f4a261; }

.mini-kpi-primary { font-size: 2.2rem; font-weight: 800; color: #0f172a; line-height: 1; padding-right: 56px; }
.mini-kpi-label   { font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.7px; color: #64748b; margin-top: 5px; }
.mini-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(70px, 1fr));
    gap: 8px;
    margin-top: 16px;
}
.mini-kpi-item {
    background: #f8fafc;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 10px 8px;
    text-align: center;
}
.mini-kpi-item-value { font-size: 1.1rem; font-weight: 700; color: #1e293b; }
.mini-kpi-item-label { font-size: 0.62rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; color: #94a3b8; margin-top: 2px; }

/* ── activity feed ────────────────────────────── */
.activity-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    margin-bottom: 24px;
    overflow: hidden;
}
.activity-card-header {
    padding: 14px 18px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    gap: 10px;
}
.activity-card-header h6 { font-size: 0.9rem; font-weight: 700; color: #1e293b; margin: 0; }
.activity-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 18px;
    border-bottom: 1px solid #f8fafc;
    transition: background 0.12s;
}
.activity-row:hover { background: #f8fafc; }
.activity-row:last-child { border-bottom: none; }
.activity-avatar {
    width: 36px; height: 36px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.9rem;
    flex-shrink: 0;
}
.activity-body { flex: 1; min-width: 0; }
.activity-body-title { font-size: 0.84rem; font-weight: 600; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.activity-body-meta  { font-size: 0.74rem; color: #94a3b8; margin-top: 1px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.activity-pill {
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.68rem;
    font-weight: 600;
    white-space: nowrap;
    flex-shrink: 0;
}
.activity-pill.green  { background: #dcfce7; color: #166534; }
.activity-pill.red    { background: #fee2e2; color: #991b1b; }
.activity-pill.amber  { background: #fef3c7; color: #92400e; }
.activity-pill.gray   { background: #f1f5f9; color: #475569; }

/* ── divider label ────────────────────────────── */
.dash-divider {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 4px 0 20px;
    color: #94a3b8;
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
}
.dash-divider::before, .dash-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e9ecef;
}

/* ─── Responsive ──────────────────────────────── */
@media (max-width: 767px) {
    .kpi-stats-grid,
    .kpi-stats-grid.cols-4,
    .kpi-stats-grid.cols-5,
    .kpi-stats-grid.cols-6 {
        grid-template-columns: 1fr 1fr;
    }
    .kpi-stat.primary { grid-column: 1 / -1; }
    .kpi-stat { border-right: none; border-bottom: 1px solid #f1f5f9; }
    .kpi-trend-strip { flex-direction: column; }
    .kpi-trend-strip-label { border-right: none; border-bottom: 1px solid #e9ecef; min-width: unset; padding: 10px 18px; }
    .kpi-trend-period { padding: 10px 12px; }
    .kpi-trend-value { font-size: 0.95rem; }
    .dash-welcome { flex-direction: column; align-items: flex-start; }
    .kpi-stat.primary .stat-num { font-size: 2rem; }
}
@media (max-width: 480px) {
    .kpi-stats-grid,
    .kpi-stats-grid.cols-4,
    .kpi-stats-grid.cols-5,
    .kpi-stats-grid.cols-6 { grid-template-columns: 1fr 1fr; }
    .kpi-trend-periods { flex-wrap: wrap; }
    .kpi-trend-period { flex: 1 1 30%; }
}
</style>

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Dashboard</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/index">Home</a></li>
                <li class="breadcrumb-item active">Dashboard</li>
            </ol>
        </nav>
    </div>

    <section class="section dashboard">
    <div class="dash-page">

        {{-- ── Welcome ──────────────────────────────────────────── --}}
        <div class="dash-welcome">
            <div class="dash-welcome-text">
                <h1>Good {{ date('H') < 12 ? 'morning' : (date('H') < 17 ? 'afternoon' : 'evening') }}, {{ $admin->username }}!</h1>
                <p>Here's your marketplace snapshot for today.</p>
                <div class="dash-welcome-date">{{ now()->format('l, F j, Y') }}</div>
            </div>
            <div class="dash-welcome-badge">
                <i class="bi bi-shield-check me-1"></i>
                {{ $admin->getRoleNames()->implode(', ') ?: 'No Role Assigned' }}
            </div>
        </div>

        {{-- ── Quick Actions ────────────────────────────────────── --}}
        @if(count($quickActions) > 0)
        <div class="dash-actions mb-4">
            @foreach($quickActions as $action)
                <a href="{{ $action['url'] }}" class="dash-action-btn btn btn-{{ $action['color'] }}">
                    <i class="bi {{ $action['icon'] }}"></i>
                    {{ $action['title'] }}
                </a>
            @endforeach
        </div>
        @endif

        {{-- ═══════════════════════════════════════════════════════
             USER MANAGEMENT
        ═══════════════════════════════════════════════════════════ --}}
        @if(isset($stats['users']))
        @php
            $uMonthChange = $stats['users']['new_last_month'] > 0
                ? round((($stats['users']['new_this_month'] - $stats['users']['new_last_month']) / $stats['users']['new_last_month']) * 100)
                : null;
        @endphp
        <div class="kpi-card">
            <div class="kpi-card-header">
                <div class="kpi-card-header-left">
                    <div class="kpi-card-icon users"><i class="bi bi-people"></i></div>
                    <div>
                        <div class="kpi-card-title">User Management</div>
                        <div class="kpi-card-sub">Registered users overview</div>
                    </div>
                </div>
                <a href="/admin/users/active" class="kpi-card-link">All Users <i class="bi bi-arrow-right"></i></a>
            </div>

            <div class="kpi-stats-grid cols-4">
                {{-- Primary --}}
                <div class="kpi-stat primary">
                    <div class="stat-num">{{ number_format($stats['users']['total']) }}</div>
                    <div class="stat-label">Total Users</div>
                    <div class="stat-sub">{{ number_format($stats['users']['active']) }} active · {{ number_format($stats['users']['verified']) }} verified</div>
                </div>
                {{-- Secondary --}}
                <div class="kpi-stat">
                    <div class="stat-num success">{{ number_format($stats['users']['active']) }}</div>
                    <div class="stat-label">Active</div>
                    <span class="stat-badge green">Enabled accounts</span>
                </div>
                <div class="kpi-stat">
                    <div class="stat-num">{{ number_format($stats['users']['verified']) }}</div>
                    <div class="stat-label">Verified</div>
                    <span class="stat-badge blue">ID confirmed</span>
                </div>
                <div class="kpi-stat">
                    <div class="stat-num">{{ number_format($stats['users']['inactive'] ?? ($stats['users']['total'] - $stats['users']['active'])) }}</div>
                    <div class="stat-label">Inactive</div>
                    <span class="stat-badge gray">Disabled</span>
                </div>
            </div>

            {{-- Trend Strip --}}
            <div class="kpi-trend-strip">
                <div class="kpi-trend-strip-label"><i class="bi bi-person-plus"></i> New Sign-ups</div>
                <div class="kpi-trend-periods">
                    <div class="kpi-trend-period">
                        <div class="kpi-trend-value">{{ number_format($stats['users']['new_today']) }}</div>
                        <div class="kpi-trend-plabel">Today</div>
                    </div>
                    <div class="kpi-trend-period">
                        <div class="kpi-trend-value">{{ number_format($stats['users']['new_this_week']) }}</div>
                        <div class="kpi-trend-plabel">This Week</div>
                    </div>
                    <div class="kpi-trend-period">
                        <div class="kpi-trend-value">{{ number_format($stats['users']['new_last_week']) }}</div>
                        <div class="kpi-trend-plabel">Last Week</div>
                    </div>
                    <div class="kpi-trend-period">
                        <div class="kpi-trend-value">{{ number_format($stats['users']['new_this_month']) }}</div>
                        <div class="kpi-trend-plabel">This Month</div>
                    </div>
                    <div class="kpi-trend-period">
                        <div class="kpi-trend-value">{{ number_format($stats['users']['new_last_month']) }}</div>
                        <div class="kpi-trend-plabel">Last Month</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ═══════════════════════════════════════════════════════
             ADVERTISEMENT MANAGEMENT
        ═══════════════════════════════════════════════════════════ --}}
        @if(isset($stats['adverts']))
        <div class="kpi-card">
            <div class="kpi-card-header">
                <div class="kpi-card-header-left">
                    <div class="kpi-card-icon adverts"><i class="bi bi-badge-ad"></i></div>
                    <div>
                        <div class="kpi-card-title">Advertisement Management</div>
                        <div class="kpi-card-sub">Active listings and performance</div>
                    </div>
                </div>
                <a href="/admin/active-adverts" class="kpi-card-link">Manage Adverts <i class="bi bi-arrow-right"></i></a>
            </div>

            <div class="kpi-stats-grid cols-5">
                {{-- Primary --}}
                <div class="kpi-stat primary">
                    <div class="stat-num">{{ number_format($stats['adverts']['total']) }}</div>
                    <div class="stat-label">Total Adverts</div>
                    <div class="stat-sub">{{ number_format($stats['adverts']['active']) }} active · {{ number_format($stats['adverts']['sold']) }} sold</div>
                </div>
                <div class="kpi-stat">
                    <div class="stat-num success">{{ number_format($stats['adverts']['active']) }}</div>
                    <div class="stat-label">Active</div>
                    <span class="stat-badge green">Live listings</span>
                </div>
                <div class="kpi-stat">
                    <div class="stat-num {{ $stats['adverts']['pending'] > 0 ? 'warning' : '' }}">{{ number_format($stats['adverts']['pending']) }}</div>
                    <div class="stat-label">Pending</div>
                    <span class="stat-badge {{ $stats['adverts']['pending'] > 0 ? 'amber' : 'gray' }}">Awaiting approval</span>
                </div>
                <div class="kpi-stat">
                    <div class="stat-num">{{ number_format($stats['adverts']['sold']) }}</div>
                    <div class="stat-label">Sold</div>
                    <span class="stat-badge gray">Completed</span>
                </div>
                {{-- Source split --}}
                <div class="kpi-stat">
                    <div class="stat-label" style="margin-bottom:10px;">Posted Via</div>
                    <div class="stat-source-pair">
                        <div class="stat-source-item">
                            <div class="stat-num" style="font-size:1.35rem;">{{ number_format($stats['adverts']['source_api']) }}</div>
                            <div class="stat-source-label">App</div>
                        </div>
                        <div class="stat-source-item">
                            <div class="stat-num" style="font-size:1.35rem;">{{ number_format($stats['adverts']['source_web']) }}</div>
                            <div class="stat-source-label">Web</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Trend Strip --}}
            <div class="kpi-trend-strip">
                <div class="kpi-trend-strip-label"><i class="bi bi-graph-up"></i> New Listings</div>
                <div class="kpi-trend-periods">
                    <div class="kpi-trend-period">
                        <div class="kpi-trend-value">{{ number_format($stats['adverts']['new_today']) }}</div>
                        <div class="kpi-trend-plabel">Today</div>
                    </div>
                    <div class="kpi-trend-period">
                        <div class="kpi-trend-value">{{ number_format($stats['adverts']['new_this_week']) }}</div>
                        <div class="kpi-trend-plabel">This Week</div>
                    </div>
                    <div class="kpi-trend-period">
                        <div class="kpi-trend-value">{{ number_format($stats['adverts']['new_last_week']) }}</div>
                        <div class="kpi-trend-plabel">Last Week</div>
                    </div>
                    <div class="kpi-trend-period">
                        <div class="kpi-trend-value">{{ number_format($stats['adverts']['new_this_month']) }}</div>
                        <div class="kpi-trend-plabel">This Month</div>
                    </div>
                    <div class="kpi-trend-period">
                        <div class="kpi-trend-value">{{ number_format($stats['adverts']['new_last_month']) }}</div>
                        <div class="kpi-trend-plabel">Last Month</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ═══════════════════════════════════════════════════════
             BOOST MANAGEMENT
        ═══════════════════════════════════════════════════════════ --}}
        @if(isset($stats['boosts']))
        <div class="kpi-card">
            <div class="kpi-card-header">
                <div class="kpi-card-header-left">
                    <div class="kpi-card-icon boosts"><i class="bi bi-rocket-takeoff"></i></div>
                    <div>
                        <div class="kpi-card-title">Ad Boost Management</div>
                        <div class="kpi-card-sub">Promoted advertisements performance</div>
                    </div>
                </div>
                <a href="/boost/active" class="kpi-card-link">Manage Boosts <i class="bi bi-arrow-right"></i></a>
            </div>

            <div class="kpi-stats-grid cols-5">
                {{-- Primary: this month revenue --}}
                <div class="kpi-stat primary">
                    <div class="stat-num" style="font-size:2rem;">Br {{ number_format($stats['boosts']['revenue_this_month'], 0) }}</div>
                    <div class="stat-label">Revenue This Month</div>
                    <div class="stat-sub">Br {{ number_format($stats['boosts']['revenue_last_month'], 0) }} last month</div>
                </div>
                <div class="kpi-stat">
                    <div class="stat-num success">{{ number_format($stats['boosts']['active']) }}</div>
                    <div class="stat-label">Active Boosts</div>
                    <span class="stat-badge green">Running now</span>
                </div>
                <div class="kpi-stat">
                    <div class="stat-num {{ $stats['boosts']['unpaid'] > 0 ? 'danger' : '' }}">{{ number_format($stats['boosts']['unpaid']) }}</div>
                    <div class="stat-label">Unpaid / Pending</div>
                    <span class="stat-badge {{ $stats['boosts']['unpaid'] > 0 ? 'amber' : 'gray' }}">Needs action</span>
                </div>
                <div class="kpi-stat">
                    <div class="stat-num">{{ number_format($stats['boosts']['completed']) }}</div>
                    <div class="stat-label">Completed</div>
                    <span class="stat-badge gray">Finished</span>
                </div>
                <div class="kpi-stat">
                    <div class="stat-num">{{ number_format($stats['boosts']['total']) }}</div>
                    <div class="stat-label">Total Boosts</div>
                    <span class="stat-badge blue">All time</span>
                </div>
            </div>

            {{-- Revenue Trend Strip --}}
            <div class="kpi-trend-strip">
                <div class="kpi-trend-strip-label"><i class="bi bi-cash-stack"></i> Revenue</div>
                <div class="kpi-trend-periods">
                    <div class="kpi-trend-period">
                        <div class="kpi-trend-value currency">Br {{ number_format($stats['boosts']['revenue_today'], 0) }}</div>
                        <div class="kpi-trend-plabel">Today</div>
                    </div>
                    <div class="kpi-trend-period">
                        <div class="kpi-trend-value currency">Br {{ number_format($stats['boosts']['revenue_this_week'], 0) }}</div>
                        <div class="kpi-trend-plabel">This Week</div>
                    </div>
                    <div class="kpi-trend-period">
                        <div class="kpi-trend-value currency">Br {{ number_format($stats['boosts']['revenue_last_week'], 0) }}</div>
                        <div class="kpi-trend-plabel">Last Week</div>
                    </div>
                    <div class="kpi-trend-period">
                        <div class="kpi-trend-value currency">Br {{ number_format($stats['boosts']['revenue_this_month'], 0) }}</div>
                        <div class="kpi-trend-plabel">This Month</div>
                    </div>
                    <div class="kpi-trend-period">
                        <div class="kpi-trend-value currency">Br {{ number_format($stats['boosts']['revenue_last_month'], 0) }}</div>
                        <div class="kpi-trend-plabel">Last Month</div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ═══════════════════════════════════════════════════════
             PAYMENTS & SETTLEMENTS
        ═══════════════════════════════════════════════════════════ --}}
        @if(isset($stats['payments']) || isset($stats['settlements']))
        <div class="kpi-card">
            <div class="kpi-card-header">
                <div class="kpi-card-header-left">
                    <div class="kpi-card-icon payments"><i class="bi bi-credit-card-2-back"></i></div>
                    <div>
                        <div class="kpi-card-title">Payments &amp; Settlements</div>
                        <div class="kpi-card-sub">Buy Direct financial overview</div>
                    </div>
                </div>
                <a href="/admin/completed-payments" class="kpi-card-link">View Payments <i class="bi bi-arrow-right"></i></a>
            </div>

            <div class="kpi-stats-grid cols-6">
                @if(isset($stats['payments']))
                {{-- Primary: total revenue --}}
                <div class="kpi-stat primary">
                    <div class="stat-num" style="font-size:1.9rem;">Br {{ number_format($stats['payments']['total_revenue'], 0) }}</div>
                    <div class="stat-label">Total Revenue</div>
                    <div class="stat-sub">{{ number_format($stats['payments']['paid']) }} paid transactions</div>
                </div>
                <div class="kpi-stat">
                    <div class="stat-num" style="font-size:1.45rem;">Br {{ number_format($stats['payments']['revenue_this_month'], 0) }}</div>
                    <div class="stat-label">This Month</div>
                    <span class="stat-badge green">Current month</span>
                </div>
                <div class="kpi-stat">
                    <div class="stat-num {{ $stats['payments']['pending'] > 0 ? 'warning' : '' }}">{{ number_format($stats['payments']['pending']) }}</div>
                    <div class="stat-label">Pending Payments</div>
                    <span class="stat-badge {{ $stats['payments']['pending'] > 0 ? 'amber' : 'gray' }}">Unconfirmed</span>
                </div>
                {{-- Source split --}}
                <div class="kpi-stat">
                    <div class="stat-label" style="margin-bottom:10px;">Purchases Via</div>
                    <div class="stat-source-pair">
                        <div class="stat-source-item">
                            <div class="stat-num" style="font-size:1.35rem;">{{ number_format($stats['payments']['source_api']) }}</div>
                            <div class="stat-source-label">App</div>
                        </div>
                        <div class="stat-source-item">
                            <div class="stat-num" style="font-size:1.35rem;">{{ number_format($stats['payments']['source_web']) }}</div>
                            <div class="stat-source-label">Web</div>
                        </div>
                    </div>
                </div>
                @endif
                @if(isset($stats['settlements']))
                <div class="kpi-stat">
                    <div class="stat-num {{ $stats['settlements']['pending'] > 0 ? 'danger' : 'success' }}">{{ number_format($stats['settlements']['pending']) }}</div>
                    <div class="stat-label">Pending Settlements</div>
                    <span class="stat-badge {{ $stats['settlements']['pending'] > 0 ? 'red' : 'green' }}">Seller payouts</span>
                </div>
                <div class="kpi-stat">
                    <div class="stat-num" style="font-size:1.35rem;">Br {{ number_format($stats['settlements']['pending_amount'], 0) }}</div>
                    <div class="stat-label">Pending Amount</div>
                    <span class="stat-badge amber">Owed to sellers</span>
                </div>
                @endif
            </div>
        </div>
        @endif

        {{-- ═══════════════════════════════════════════════════════
             SECONDARY STATS — Shipping / Reports / Categories / Blog
        ═══════════════════════════════════════════════════════════ --}}
        @if(isset($stats['shipping']) || isset($stats['reports']) || isset($stats['categories']) || isset($stats['blog']))
        <div class="dash-divider">Other Sections</div>
        <div class="row">

            {{-- Shipping --}}
            @if(isset($stats['shipping']))
            <div class="col-xl-4 col-md-6">
                <div class="mini-kpi-card shipping">
                    <div class="mini-kpi-icon shipping"><i class="bi bi-truck"></i></div>
                    <div class="mini-kpi-primary">{{ number_format($stats['shipping']['pending']) }}</div>
                    <div class="mini-kpi-label">Shipping Management</div>
                    <div class="mini-kpi-grid">
                        <div class="mini-kpi-item">
                            <div class="mini-kpi-item-value">{{ number_format($stats['shipping']['pending']) }}</div>
                            <div class="mini-kpi-item-label">Pending</div>
                        </div>
                        <div class="mini-kpi-item">
                            <div class="mini-kpi-item-value">{{ number_format($stats['shipping']['shipped']) }}</div>
                            <div class="mini-kpi-item-label">In Transit</div>
                        </div>
                        <div class="mini-kpi-item">
                            <div class="mini-kpi-item-value">{{ number_format($stats['shipping']['delivered']) }}</div>
                            <div class="mini-kpi-item-label">Delivered</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Reports --}}
            @if(isset($stats['reports']))
            <div class="col-xl-4 col-md-6">
                <div class="mini-kpi-card reports">
                    <div class="mini-kpi-icon reports"><i class="bi bi-flag"></i></div>
                    <div class="mini-kpi-primary {{ $stats['reports']['pending'] > 0 ? 'text-danger' : '' }}">{{ number_format($stats['reports']['pending']) }}</div>
                    <div class="mini-kpi-label">Pending Reports</div>
                    <div class="mini-kpi-grid">
                        <div class="mini-kpi-item">
                            <div class="mini-kpi-item-value">{{ number_format($stats['reports']['total']) }}</div>
                            <div class="mini-kpi-item-label">Total</div>
                        </div>
                        <div class="mini-kpi-item">
                            <div class="mini-kpi-item-value">{{ number_format($stats['reports']['new_today']) }}</div>
                            <div class="mini-kpi-item-label">New Today</div>
                        </div>
                        <div class="mini-kpi-item">
                            <div class="mini-kpi-item-value">{{ number_format($stats['reports']['resolved']) }}</div>
                            <div class="mini-kpi-item-label">Resolved</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Categories --}}
            @if(isset($stats['categories']))
            <div class="col-xl-4 col-md-6">
                <div class="mini-kpi-card categories">
                    <div class="mini-kpi-icon categories"><i class="bi bi-grid-3x3-gap"></i></div>
                    <div class="mini-kpi-primary">{{ number_format($stats['categories']['total_categories']) }}</div>
                    <div class="mini-kpi-label">Categories</div>
                    <div class="mini-kpi-grid">
                        <div class="mini-kpi-item">
                            <div class="mini-kpi-item-value">{{ number_format($stats['categories']['total_subcategories']) }}</div>
                            <div class="mini-kpi-item-label">Subcategories</div>
                        </div>
                        <div class="mini-kpi-item">
                            <div class="mini-kpi-item-value">{{ number_format($stats['categories']['total_brands']) }}</div>
                            <div class="mini-kpi-item-label">Brands</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Blog --}}
            @if(isset($stats['blog']))
            <div class="col-xl-4 col-md-6">
                <div class="mini-kpi-card blog">
                    <div class="mini-kpi-icon blog"><i class="bi bi-newspaper"></i></div>
                    <div class="mini-kpi-primary">{{ number_format($stats['blog']['total_posts']) }}</div>
                    <div class="mini-kpi-label">Blog Posts</div>
                    <div class="mini-kpi-grid">
                        <div class="mini-kpi-item">
                            <div class="mini-kpi-item-value">{{ number_format($stats['blog']['published']) }}</div>
                            <div class="mini-kpi-item-label">Published</div>
                        </div>
                        <div class="mini-kpi-item">
                            <div class="mini-kpi-item-value">{{ number_format($stats['blog']['draft']) }}</div>
                            <div class="mini-kpi-item-label">Drafts</div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
        @endif

        {{-- ═══════════════════════════════════════════════════════
             RECENT ACTIVITY
        ═══════════════════════════════════════════════════════════ --}}
        @if(count($recentActivities) > 0)
        <div class="dash-divider">Recent Activity</div>
        <div class="row">

            {{-- Recent Adverts --}}
            @if(isset($recentActivities['adverts']) && count($recentActivities['adverts']) > 0)
            <div class="col-lg-6">
                <div class="activity-card">
                    <div class="activity-card-header">
                        <div class="activity-avatar bg-danger bg-opacity-10" style="color:#e63946;width:32px;height:32px;font-size:.85rem;">
                            <i class="bi bi-badge-ad"></i>
                        </div>
                        <h6>Recent Adverts</h6>
                        <a href="/admin/active-adverts" class="ms-auto kpi-card-link" style="font-size:0.75rem;">See all <i class="bi bi-arrow-right"></i></a>
                    </div>
                    @foreach($recentActivities['adverts'] as $advert)
                    @php
                        $sPill = match($advert->ad_status) {
                            'active'   => 'green',
                            'pending'  => 'amber',
                            'disabled' => 'red',
                            default    => 'gray',
                        };
                    @endphp
                    <div class="activity-row">
                        <div class="activity-avatar bg-danger bg-opacity-10" style="color:#e63946;">
                            <i class="bi bi-badge-ad"></i>
                        </div>
                        <div class="activity-body">
                            <div class="activity-body-title">{{ Str::limit($advert->ad_title, 48) }}</div>
                            <div class="activity-body-meta">{{ $advert->user->name ?? 'Unknown' }} · {{ $advert->created_at->diffForHumans() }}</div>
                        </div>
                        <span class="activity-pill {{ $sPill }}">{{ ucfirst($advert->ad_status) }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Recent Users --}}
            @if(isset($recentActivities['users']) && count($recentActivities['users']) > 0)
            <div class="col-lg-6">
                <div class="activity-card">
                    <div class="activity-card-header">
                        <div class="activity-avatar bg-primary bg-opacity-10" style="color:#4361ee;width:32px;height:32px;font-size:.85rem;">
                            <i class="bi bi-people"></i>
                        </div>
                        <h6>Recent Sign-ups</h6>
                        <a href="/admin/users/active" class="ms-auto kpi-card-link" style="font-size:0.75rem;">See all <i class="bi bi-arrow-right"></i></a>
                    </div>
                    @foreach($recentActivities['users'] as $user)
                    <div class="activity-row">
                        <div class="activity-avatar bg-primary bg-opacity-10" style="color:#4361ee;">
                            <i class="bi bi-person"></i>
                        </div>
                        <div class="activity-body">
                            <div class="activity-body-title">{{ $user->name }}</div>
                            <div class="activity-body-meta">{{ $user->email }} · {{ $user->created_at->diffForHumans() }}</div>
                        </div>
                        <span class="activity-pill {{ $user->verified ? 'green' : 'gray' }}">
                            {{ $user->verified ? 'Verified' : 'Unverified' }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Recent Reports --}}
            @if(isset($recentActivities['reports']) && count($recentActivities['reports']) > 0)
            <div class="col-12">
                <div class="activity-card">
                    <div class="activity-card-header">
                        <div class="activity-avatar bg-warning bg-opacity-10" style="color:#d97706;width:32px;height:32px;font-size:.85rem;">
                            <i class="bi bi-flag"></i>
                        </div>
                        <h6>Recent Reports</h6>
                    </div>
                    @foreach($recentActivities['reports'] as $report)
                    <div class="activity-row">
                        <div class="activity-avatar bg-warning bg-opacity-10" style="color:#d97706;">
                            <i class="bi bi-flag"></i>
                        </div>
                        <div class="activity-body">
                            <div class="activity-body-title">{{ Str::limit($report->subject ?? 'Report', 60) }}</div>
                            <div class="activity-body-meta">
                                by {{ $report->user->name ?? 'Unknown' }} ·
                                Ad: {{ Str::limit($report->display_ad_title, 30) }} ·
                                {{ $report->created_at->diffForHumans() }}
                            </div>
                        </div>
                        <span class="activity-pill {{ $report->status === 'resolved' ? 'green' : 'red' }}">
                            {{ ucfirst($report->status) }}
                        </span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
        @endif

        {{-- No permissions --}}
        @if(count($stats) === 0)
        <div class="activity-card text-center py-5">
            <i class="bi bi-shield-exclamation text-muted" style="font-size:3.5rem;"></i>
            <h5 class="mt-3 fw-bold text-muted">Limited Access</h5>
            <p class="text-muted mb-0">You don't have permission to view dashboard statistics.<br>Contact your administrator to request access.</p>
        </div>
        @endif

    </div>
    </section>
</main>

@include('admin.layouts.footer')
