@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">
    <div class="pagetitle">
        <div class="d-flex justify-content-between align-items-center">
            <h1>User Management</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="/admin/users">Users</a></li>
                    <li class="breadcrumb-item active">Details</li>
                </ol>
            </nav>
        </div>
    </div><!-- End Page Title -->

    <section class="section">

        @if(session('status'))
            <div class="alert alert-{{ session('status')['type'] }} alert-dismissible fade show">
                <div class="d-flex align-items-center">
                    @if(session('status')['type'] === 'success')
                        <i class="bi bi-check-circle-fill me-2"></i>
                    @else
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    @endif
                    <div>{{ session('status')['text'] }}</div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @php
            $isPending = $user->acc_status == "0";
            $isDisabled = $user->disable_account === 'yes';
            $isVerified = $user->verified != "no";
            $initials = collect(explode(' ', trim($user->name ?? '?')))->map(fn($p) => mb_substr($p, 0, 1))->take(2)->implode('');

            $sourceMeta = [
                'web'      => ['label' => 'Website',       'icon' => 'bi-window'],
                'app'      => ['label' => 'Mobile app',     'icon' => 'bi-phone'],
                'google'   => ['label' => 'Google sign-in', 'icon' => 'bi-google'],
                'facebook' => ['label' => 'Facebook login', 'icon' => 'bi-facebook'],
                'apple'    => ['label' => 'Apple sign-in',  'icon' => 'bi-apple'],
            ][$user->source ?? 'unknown'] ?? ['label' => 'Unknown (before tracking)', 'icon' => 'bi-question-circle'];
        @endphp

        <!-- Profile Hero -->
        <div class="user-hero">
            <div class="user-avatar">{{ strtoupper($initials ?: '?') }}</div>
            <div class="user-hero-info">
                <h2 class="user-hero-name">{{ $user->name ?? 'Unnamed user' }}</h2>
                <div class="user-hero-email">{{ $user->email }}</div>
                <div class="user-hero-pills">
                    @if($isPending)
                        <span class="pill pill-warning"><i class="bi bi-hourglass-split"></i>Pending activation</span>
                    @elseif($isDisabled)
                        <span class="pill pill-danger"><i class="bi bi-slash-circle"></i>Disabled</span>
                    @else
                        <span class="pill pill-success"><i class="bi bi-check-circle"></i>Active</span>
                    @endif

                    @if($isVerified)
                        <span class="pill pill-success"><i class="bi bi-patch-check"></i>Verified</span>
                    @else
                        <span class="pill pill-warning"><i class="bi bi-hourglass-split"></i>Verification pending</span>
                    @endif

                    @if($user->acc_type)
                        <span class="pill pill-neutral"><i class="bi bi-person"></i>{{ $user->acc_type }}</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Advertising Stats -->
        <div class="row mb-4 gy-3">
            <div class="col-md-4">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-primary bg-opacity-10 p-3 rounded-3 me-3">
                                <i class="bi bi-megaphone text-primary fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Total adverts</h6>
                                <h4 class="mb-0 fw-bold">{{ $active_adverts ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-success bg-opacity-10 p-3 rounded-3 me-3">
                                <i class="bi bi-check-circle text-success fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Sold adverts</h6>
                                <h4 class="mb-0 fw-bold">{{ $sold_adverts ?? 0 }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card stat-card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-info bg-opacity-10 p-3 rounded-3 me-3">
                                <i class="bi bi-currency-exchange text-info fs-4"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="text-muted mb-1">Total revenue</h6>
                                <h4 class="mb-0 fw-bold">Br {{ number_format($totalRevenue ?? 0, 2) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row gy-3">
            <!-- Basic Information -->
            <div class="col-lg-6">
                <div class="section-card h-100">
                    <div class="section-title">
                        <i class="bi bi-info-circle"></i>Basic information
                    </div>
                    <div class="section-body">
                        <div class="detail-row">
                            <span class="detail-label">Full name</span>
                            <span class="detail-value">{{ $user->name ?? 'Not provided' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Email address</span>
                            <span class="detail-value">{{ $user->email }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Phone number</span>
                            <span class="detail-value">{{ $user->phone ?? 'Not provided' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Account type</span>
                            <span class="detail-value">{{ $user->acc_type ?? 'Not set' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Member since</span>
                            <span class="detail-value">{{ optional($user->created_at)->format('d M Y') ?? 'Not available' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Registered via</span>
                            <span class="detail-value">
                                <span class="pill pill-neutral"><i class="bi {{ $sourceMeta['icon'] }}"></i>{{ $sourceMeta['label'] }}</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location & Financials -->
            <div class="col-lg-6">
                <div class="section-card h-100">
                    <div class="section-title">
                        <i class="bi bi-geo-alt"></i>Location &amp; financials
                    </div>
                    <div class="section-body">
                        <div class="detail-row">
                            <span class="detail-label">Address</span>
                            <span class="detail-value">{{ $user->address ?? 'Not provided' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">City</span>
                            <span class="detail-value">{{ $user->city ?? 'Not provided' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">State</span>
                            <span class="detail-value">{{ $user->state ?? 'Not provided' }}</span>
                        </div>
                        <div class="detail-row">
                            <span class="detail-label">Pending settlement</span>
                            <span class="detail-value text-success">Br {{ number_format($pendingRevenue, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Actions -->
        <div class="section-card mt-3">
            <div class="section-title">
                <i class="bi bi-gear"></i>Account actions
            </div>
            <div class="section-body">
                <div class="action-toolbar">
                    @if($isPending)
                        <a href="/admin/user-status/{{ $user->user_id }}/1"
                           class="action-btn action-btn-success"
                           onclick="return confirm('Activate this account?');">
                            <i class="bi bi-check-circle"></i>
                            <span>Activate account</span>
                        </a>
                    @elseif($isDisabled)
                        <form method="POST" action="/admin/disable-status/{{ $user->user_id }}">
                            @csrf
                            <input type="hidden" name="action" value="enable">
                            <button type="submit" class="action-btn action-btn-success"
                                    onclick="return confirm('Re-enable this account?');">
                                <i class="bi bi-check-circle"></i>
                                <span>Enable account</span>
                            </button>
                        </form>
                    @else
                        <button type="button" class="action-btn action-btn-warning"
                                onclick="showDisableModal('{{ $user->user_id }}', '{{ addslashes($user->name) }}')">
                            <i class="bi bi-slash-circle"></i>
                            <span>Disable account</span>
                        </button>
                    @endif

                    <form action="{{ route('admin.delete.user', $user->user_id) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="action-btn action-btn-danger"
                                onclick="return confirm('WARNING: This will permanently delete the user account and all associated data. Continue?');">
                            <i class="bi bi-trash"></i>
                            <span>Delete account</span>
                        </button>
                    </form>

                    @if($isPending)
                        <a href="/admin/unverified-users" class="action-btn action-btn-neutral ms-auto">
                    @elseif($isDisabled)
                        <a href="/admin/disabled-users" class="action-btn action-btn-neutral ms-auto">
                    @else
                        <a href="/admin/active-users" class="action-btn action-btn-neutral ms-auto">
                    @endif
                        <i class="bi bi-arrow-left"></i>
                        <span>Back to list</span>
                    </a>
                </div>
            </div>
        </div>

    </section>
</main><!-- End #main -->

<style>
    /* Profile hero */
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
        margin-bottom: 0.65rem;
    }
    .user-hero-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    /* Pills — explicit tinted colors (this theme ships Bootstrap 5.1, which
       has no bg-opacity utility, so bg-success + text-success previously
       rendered as solid green text on solid green = invisible). */
    .pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.35rem 0.75rem;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 600;
        line-height: 1;
        border: 1px solid transparent;
        white-space: nowrap;
    }
    .pill i { font-size: 0.85rem; }
    .pill-success { background: #e4f7ec; color: #0f7a3d; border-color: #bfe8cf; }
    .pill-warning { background: #fff4e0; color: #9a6300; border-color: #ffe3ad; }
    .pill-danger  { background: #fdecec; color: #b42318; border-color: #f7c9c7; }
    .pill-neutral { background: #edf0f5; color: #4a5568; border-color: #dde2e9; }

    /* Section cards */
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
    .section-body { padding: 0.25rem 1.15rem; }

    .detail-row {
        display: flex;
        align-items: baseline;
        justify-content: space-between;
        gap: 1rem;
        padding: 0.7rem 0;
        border-bottom: 1px solid #f1f3f7;
    }
    .detail-row:last-child { border-bottom: none; }
    .detail-label {
        color: #8a93a2;
        font-size: 0.83rem;
        flex-shrink: 0;
    }
    .detail-value {
        color: #1f2430;
        font-weight: 600;
        font-size: 0.925rem;
        text-align: right;
    }

    /* Action toolbar */
    .action-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.65rem;
        padding: 0.85rem 0 1.1rem;
    }
    .action-toolbar form { margin: 0; }
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
    .action-btn:hover { filter: brightness(0.96); transform: translateY(-1px); }
    .action-btn-success { background: #18a44d; color: #fff; }
    .action-btn-warning { background: #ffab00; color: #1c2333; }
    .action-btn-danger  { background: #dc3545; color: #fff; }
    .action-btn-neutral { background: #fff; color: #495464; border-color: #dde2e9; }

    /* Stat cards */
    .stat-card { transition: transform 0.25s ease; border-radius: 0.75rem; }
    .stat-card:hover { transform: translateY(-4px); }

    @media (max-width: 576px) {
        .detail-row { flex-direction: column; align-items: flex-start; gap: 0.15rem; }
        .detail-value { text-align: left; }
        .action-btn.ms-auto { margin-left: 0 !important; }
    }
</style>

{{-- Disable account modal with reason --}}
<div class="modal fade" id="disableModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="disableForm" action="">
                @csrf
                <input type="hidden" name="action" value="disable">
                <div class="modal-header border-0">
                    <h6 class="modal-title text-danger"><i class="bi bi-slash-circle me-2"></i>Disable Account</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-0">
                    <p class="text-muted mb-3" id="disableModalMessage"></p>
                    <label class="form-label fw-semibold">Reason <span class="text-muted fw-normal">(optional)</span></label>
                    <textarea name="reason" class="form-control" rows="3"
                              placeholder="e.g. Suspicious activity, violated terms…" maxlength="500"></textarea>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-slash-circle me-1"></i>Disable Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showDisableModal(userId, userName) {
    document.getElementById('disableModalMessage').textContent =
        `This will block "${userName}" from logging in. You can re-enable at any time.`;
    document.getElementById('disableForm').action = `/admin/disable-status/${userId}`;
    new bootstrap.Modal(document.getElementById('disableModal')).show();
}
</script>

@include('admin.layouts.footer')
