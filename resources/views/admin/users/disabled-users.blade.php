@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">
    <div class="pagetitle">
        <h1>Disabled Users</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                <li class="breadcrumb-item active">Disabled Users</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-danger text-white">
                        <div class="row align-items-center">
                            <div class="col">
                                <h5 class="card-title mb-0 text-white">
                                    <i class="bi bi-slash-circle me-2"></i>Disabled Users
                                </h5>
                                <p class="mb-0 opacity-75">Users blocked from accessing the platform</p>
                            </div>
                            <div class="col-auto">
                                <a href="/admin/active-users" class="btn btn-light btn-sm">
                                    <i class="bi bi-people me-1"></i>Active Users
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body pt-4">

                        @if(session('status'))
                            <div class="alert alert-{{ session('status')['type'] }} alert-dismissible fade show" role="alert">
                                <i class="bi bi-{{ session('status')['type'] === 'success' ? 'check-circle' : 'exclamation-triangle' }}-fill me-2"></i>
                                {{ session('status')['text'] }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        {{-- Stats --}}
                        <div class="row mb-4 g-3">
                            <div class="col-md-3">
                                <div class="card bg-primary text-white border-0">
                                    <div class="card-body text-center py-3">
                                        <h4 class="mb-0">{{ number_format($stats['total']) }}</h4>
                                        <small>Total Users</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success text-white border-0">
                                    <div class="card-body text-center py-3">
                                        <h4 class="mb-0">{{ number_format($stats['active']) }}</h4>
                                        <small>Active Users</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-danger text-white border-0">
                                    <div class="card-body text-center py-3">
                                        <h4 class="mb-0">{{ number_format($stats['disabled']) }}</h4>
                                        <small>Disabled Users</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-secondary text-white border-0">
                                    <div class="card-body text-center py-3">
                                        <h4 class="mb-0">{{ number_format($users->total()) }}</h4>
                                        <small>This List</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Search --}}
                        <div class="row mb-4">
                            <div class="col-md-5">
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                                    <input type="text" class="form-control" id="searchInput" placeholder="Search disabled users…">
                                </div>
                            </div>
                        </div>

                        {{-- Table --}}
                        <div class="table-responsive">
                            <table class="table table-hover align-middle" id="usersTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Disabled On</th>
                                        <th>Disabled By</th>
                                        <th>Reason</th>
                                        <th class="text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($users as $row)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="avatar bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center"
                                                         style="width:38px;height:38px;flex-shrink:0;">
                                                        <i class="bi bi-person text-danger"></i>
                                                    </div>
                                                    <div>
                                                        <div class="fw-semibold">{{ $row->name }}</div>
                                                        <small class="text-muted">ID: {{ $row->user_id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="mailto:{{ $row->email }}" class="text-decoration-none text-muted">{{ $row->email }}</a>
                                            </td>
                                            <td>
                                                @if($row->disable_account_date)
                                                    <span class="text-muted">{{ date('d M Y', strtotime($row->disable_account_date)) }}</span>
                                                    <small class="d-block text-muted">{{ date('g:i A', strtotime($row->disable_account_date)) }}</small>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ $row->disabled_by ?? '—' }}</span>
                                            </td>
                                            <td>
                                                @if($row->disable_reason)
                                                    <span class="text-muted" title="{{ $row->disable_reason }}">
                                                        {{ Str::limit($row->disable_reason, 40) }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <div class="d-flex gap-1 justify-content-center">
                                                    <a href="/admin/view-user/{{ $row->user_id }}"
                                                       class="btn btn-outline-primary btn-sm" title="View">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <button type="button"
                                                            class="btn btn-outline-success btn-sm"
                                                            onclick="confirmEnable('{{ $row->user_id }}', '{{ addslashes($row->name) }}')"
                                                            title="Re-enable Account">
                                                        <i class="bi bi-unlock"></i> Enable
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">
                                                <i class="bi bi-shield-check display-4 d-block mb-2"></i>
                                                No disabled users
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($users->hasPages())
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <span class="text-muted">
                                    Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }}
                                </span>
                                {{ $users->links('pagination::bootstrap-4') }}
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

{{-- Hidden POST form for enable action --}}
<form id="enableForm" method="POST" action="" style="display:none;">
    @csrf
    <input type="hidden" name="action" value="enable">
</form>

{{-- Disable modal (for inline enable confirmations) --}}
<div class="modal fade" id="enableModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h6 class="modal-title"><i class="bi bi-unlock text-success me-2"></i>Re-enable Account</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-0">
                <p class="text-muted mb-0" id="enableMessage"></p>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success btn-sm" id="enableConfirmBtn">Enable</button>
            </div>
        </div>
    </div>
</div>

<script>
function confirmEnable(userId, userName) {
    document.getElementById('enableMessage').textContent =
        `Re-enable "${userName}"? They will be able to log in again.`;
    const btn = document.getElementById('enableConfirmBtn');
    btn.onclick = function () {
        const form = document.getElementById('enableForm');
        form.action = `/admin/disable-status/${userId}`;
        form.submit();
    };
    new bootstrap.Modal(document.getElementById('enableModal')).show();
}

// Search (scoped to disabled context)
let searchTimeout;
document.getElementById('searchInput').addEventListener('input', function () {
    clearTimeout(searchTimeout);
    const term = this.value;
    searchTimeout = setTimeout(() => {
        fetch(`/admin/users/search?search=${encodeURIComponent(term)}&context=disabled`)
            .then(r => r.json())
            .then(data => {
                if (!data.success) return;
                const tbody = document.querySelector('#usersTable tbody');
                if (!data.users.length) {
                    tbody.innerHTML = `<tr><td colspan="6" class="text-center py-4 text-muted">No results</td></tr>`;
                    return;
                }
                tbody.innerHTML = data.users.map(u => `
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="avatar bg-danger bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width:38px;height:38px;flex-shrink:0;">
                                    <i class="bi bi-person text-danger"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">${escapeHtml(u.name)}</div>
                                    <small class="text-muted">ID: ${escapeHtml(u.user_id)}</small>
                                </div>
                            </div>
                        </td>
                        <td><a href="mailto:${escapeHtml(u.email)}" class="text-decoration-none text-muted">${escapeHtml(u.email)}</a></td>
                        <td><span class="text-muted">${u.disable_account_date ? new Date(u.disable_account_date).toLocaleDateString('en-GB', {day:'2-digit',month:'short',year:'numeric'}) : '—'}</span></td>
                        <td><span class="text-muted">${escapeHtml(u.disabled_by || '—')}</span></td>
                        <td><span class="text-muted">${escapeHtml((u.disable_reason || '—').substring(0, 40))}</span></td>
                        <td class="text-center">
                            <div class="d-flex gap-1 justify-content-center">
                                <a href="/admin/view-user/${escapeHtml(u.user_id)}" class="btn btn-outline-primary btn-sm"><i class="bi bi-eye"></i></a>
                                <button class="btn btn-outline-success btn-sm" onclick="confirmEnable('${escapeHtml(u.user_id)}', '${escapeHtml(u.name).replace(/'/g,"\\'")}')">
                                    <i class="bi bi-unlock"></i> Enable
                                </button>
                            </div>
                        </td>
                    </tr>
                `).join('');
            });
    }, 400);
});

function escapeHtml(str) {
    if (typeof str !== 'string') return str ?? '';
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
}
</script>

@include('admin.layouts.footer')
