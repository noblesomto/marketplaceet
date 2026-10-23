# Unmatched Payments Admin Tool Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Give admin a page to view Paystack payments that succeeded but matched no `AdvertBoost`/`Payment` record, and to manually complete them (link to the right advert, activate the boost) without touching the database directly.

**Architecture:** A new `unmatched_payments` table is populated by `PaystackWebhookController::reportUnmatchedPayment()` (already exists, currently only logs+emails). A new pair of methods on the existing `App\Http\Controllers\Admin\ManageBoost` controller lists unresolved rows and completes them by creating the missing `AdvertBoost` row, following the exact conventions already used by that controller's `verifyAndActivate`/`payment` methods.

**Tech Stack:** Laravel 12, Blade, MySQL, Spatie permissions (admin guard).

## Global Constraints

- Reuse the existing `manage_boost_payment` permission slug for the new routes — do not add a new permission or touch the Spatie seeder.
- Match the existing `admin.adboost.*` Blade skeleton exactly: header/nav includes, `card` + `card-header bg-primary`, the `session('status')` alert block, `table-responsive` + `table-hover align-middle`, `$collection->links('pagination::bootstrap-4')`.
- No new PHPUnit tests: zero admin controllers in this codebase have test coverage today (confirmed during design), and the approved spec scoped this as manual verification only. Follow that — do not introduce a new testing pattern unilaterally.
- Buy-Direct order payments are out of scope for this tool (spec decision) — only boost completion.
- No forced confirmation/note field when a customer has multiple candidate adverts (spec decision) — plain dropdown, most-recent pre-selected by sort order.
- `adverts.user_id` / `advert_boosts.user_id` reference `users.user_id` (a `varchar(5)` column), **not** `users.id` — this bit the investigation earlier this session; get it wrong and every lookup silently returns nothing.

---

### Task 1: `unmatched_payments` table + model

**Files:**
- Create: `database/migrations/2026_08_10_120000_create_unmatched_payments_table.php`
- Create: `app/Models/UnmatchedPayment.php`

**Interfaces:**
- Produces: `UnmatchedPayment` Eloquent model, fillable `reference, transaction_id, amount, customer_email, paid_at, status, resolved_boost_id, resolved_by, resolved_at`; `status` is `unresolved`|`resolved`; relation `boost()` → `belongsTo(AdvertBoost::class, 'resolved_boost_id')`.

- [ ] **Step 1: Write the migration**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unmatched_payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('transaction_id')->nullable();
            $table->unsignedBigInteger('amount');
            $table->string('customer_email');
            $table->timestamp('paid_at')->nullable();
            $table->enum('status', ['unresolved', 'resolved'])->default('unresolved');
            $table->unsignedBigInteger('resolved_boost_id')->nullable();
            $table->string('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->foreign('resolved_boost_id')->references('id')->on('advert_boosts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unmatched_payments');
    }
};
```

- [ ] **Step 2: Run the migration locally and verify the table**

Run: `php artisan migrate`
Expected: `2026_08_10_120000_create_unmatched_payments_table ... DONE` in the output.

Run: `php artisan tinker --execute="dd(Schema::getColumnListing('unmatched_payments'));"`
Expected: array containing `id, reference, transaction_id, amount, customer_email, paid_at, status, resolved_boost_id, resolved_by, resolved_at, created_at, updated_at`.

- [ ] **Step 3: Write the model**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnmatchedPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference',
        'transaction_id',
        'amount',
        'customer_email',
        'paid_at',
        'status',
        'resolved_boost_id',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function boost()
    {
        return $this->belongsTo(AdvertBoost::class, 'resolved_boost_id');
    }
}
```

- [ ] **Step 4: Verify the model works end-to-end**

Run:
```bash
php artisan tinker --execute="
\$p = App\Models\UnmatchedPayment::create([
    'reference' => 'test-plan-verify-1',
    'transaction_id' => '123456',
    'amount' => 150000,
    'customer_email' => 'planverify@example.com',
    'paid_at' => now(),
]);
echo \$p->status . PHP_EOL;
\$p->delete();
"
```
Expected: prints `unresolved` (the default), then deletes cleanly with no error.

- [ ] **Step 5: Commit**

```bash
git add database/migrations/2026_08_10_120000_create_unmatched_payments_table.php app/Models/UnmatchedPayment.php
git commit -m "Add unmatched_payments table and model"
```

---

### Task 2: Persist unmatched payments from the webhook

**Files:**
- Modify: `app/Http/Controllers/Api/PaystackWebhookController.php`

**Interfaces:**
- Consumes: `App\Models\UnmatchedPayment` (Task 1) — `UnmatchedPayment::firstOrCreate(array $attributes, array $values)`, returns a model with `->wasRecentlyCreated` (Eloquent built-in).
- Produces: no change to the controller's public interface (`handle(Request $request)` unchanged); `reportUnmatchedPayment()` stays `private`.

- [ ] **Step 1: Confirm current behavior (no persistence) before changing it**

Run: `grep -n "reportUnmatchedPayment" -A25 app/Http/Controllers/Api/PaystackWebhookController.php`
Expected: shows the current method using `Cache::has`/`Cache::put` for dedup and no database write — confirms the gap this task closes.

- [ ] **Step 2: Replace the cache-based dedup with DB persistence**

In `app/Http/Controllers/Api/PaystackWebhookController.php`, replace the `use` block:

```php
use App\Http\Controllers\Controller;
use App\Mail\UnmatchedPaymentMail;
use App\Models\Advert;
use App\Models\AdvertBoost;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
```

with:

```php
use App\Http\Controllers\Controller;
use App\Mail\UnmatchedPaymentMail;
use App\Models\Advert;
use App\Models\AdvertBoost;
use App\Models\Payment;
use App\Models\UnmatchedPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
```

(`Cache` import dropped — no longer used once dedup moves to the unique `reference` column.)

Replace the `reportUnmatchedPayment` method body:

```php
    private function reportUnmatchedPayment(string $reference, array $data): void
    {
        Log::critical('Paystack webhook: paid transaction has no matching AdvertBoost or Payment record', [
            'ref'    => $reference,
            'amount' => $data['amount'] ?? null,
            'email'  => $data['customer']['email'] ?? null,
        ]);

        $unmatched = UnmatchedPayment::firstOrCreate(
            ['reference' => $reference],
            [
                'transaction_id' => $data['id'] ?? null,
                'amount'         => $data['amount'] ?? 0,
                'customer_email' => $data['customer']['email'] ?? 'unknown',
                'paid_at'        => $data['paid_at'] ?? null,
                'status'         => 'unresolved',
            ]
        );

        if (!$unmatched->wasRecentlyCreated) {
            // Already recorded (and already alerted) on a previous delivery of this event.
            return;
        }

        Mail::to(config('global.admin_email'))->queue(new UnmatchedPaymentMail([
            'reference'       => $reference,
            'transaction_id'  => $data['id'] ?? null,
            'amount'          => $data['amount'] ?? 0,
            'customer_email'  => $data['customer']['email'] ?? 'unknown',
            'paid_at'         => $data['paid_at'] ?? 'unknown',
        ]));
    }
```

- [ ] **Step 3: Syntax-check the file**

Run: `php -l app/Http/Controllers/Api/PaystackWebhookController.php`
Expected: `No syntax errors detected`

- [ ] **Step 4: Verify the new persistence path manually**

Run:
```bash
php artisan tinker --execute="
\$controller = new App\Http\Controllers\Api\PaystackWebhookController();
\$method = new ReflectionMethod(\$controller, 'reportUnmatchedPayment');
\$method->setAccessible(true);
\$method->invoke(\$controller, 'test-plan-verify-2', [
    'id' => 999999,
    'amount' => 150000,
    'customer' => ['email' => 'planverify@example.com'],
    'paid_at' => now()->toIso8601String(),
]);
echo App\Models\UnmatchedPayment::where('reference', 'test-plan-verify-2')->exists() ? 'ROW CREATED' : 'MISSING';
"
```
Expected: prints `ROW CREATED`. Then clean up:
```bash
php artisan tinker --execute="App\Models\UnmatchedPayment::where('reference', 'test-plan-verify-2')->delete();"
```

- [ ] **Step 5: Verify redelivery doesn't duplicate or re-alert**

Run the same tinker snippet from Step 4 twice in a row without deleting in between, then check:
```bash
php artisan tinker --execute="echo App\Models\UnmatchedPayment::where('reference', 'test-plan-verify-2')->count();"
```
Expected: `1` (not `2`) — confirms `firstOrCreate` dedups correctly. Clean up the row afterward.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Api/PaystackWebhookController.php
git commit -m "Persist unmatched Paystack payments instead of relying on cache-only dedup"
```

---

### Task 3: Admin controller methods + routes

**Files:**
- Modify: `app/Http/Controllers/Admin/ManageBoost.php`
- Modify: `routes/admin.php`

**Interfaces:**
- Consumes: `App\Models\UnmatchedPayment` (Task 1); existing `App\Models\Advert`, `App\Models\AdvertBoost`, `App\Models\User`, `App\Models\BoostType` (has `calculatePrice($days, $discountPercentage = 0)`, scopes `active()`/`ordered()`), `App\Models\BoostDuration` (same scopes, has `days`, `discount_percentage`, `label`); `App\Helpers\AdminHelper::currentAdmin()` (returns `?Admin`, `Admin` has `username`).
- Produces: routes `admin.boost.unmatched-payments` (GET `/boost/unmatched-payments`) and `admin.boost.unmatched-payments.complete` (POST `/boost/unmatched-payments/{id}/complete`); view expects `$title, $page_title, $payments (paginated UnmatchedPayment), $boostTypes (Collection<BoostType>), $boostDurations (Collection<BoostDuration>), $advertsByPayment (array<int, Collection<Advert>> keyed by UnmatchedPayment id)`.

- [ ] **Step 1: Add imports to `ManageBoost.php`**

The file currently starts:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Advert;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\AdvertBoost;
use Carbon\Carbon;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
```

Replace with:

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\AdminHelper;
use App\Http\Controllers\Controller;
use App\Models\Advert;
use App\Models\BoostDuration;
use App\Models\BoostType;
use App\Models\UnmatchedPayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\AdvertBoost;
use Carbon\Carbon;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
```

- [ ] **Step 2: Add the two new methods**

Add at the end of the `ManageBoost` class, just before its closing `}`:

```php
    public function unmatchedPayments(Request $request)
    {
        $title = "Unmatched Payments | " . config('global.site_name');
        $page_title = "Unmatched Payments";

        $payments = UnmatchedPayment::where('status', 'unresolved')
            ->orderBy('paid_at', 'desc')
            ->paginate(20);

        $boostTypes = BoostType::active()->ordered()->get();
        $boostDurations = BoostDuration::active()->ordered()->get();

        $advertsByPayment = [];
        foreach ($payments as $payment) {
            $user = User::where('email', $payment->customer_email)->first();
            $advertsByPayment[$payment->id] = $user
                ? Advert::where('user_id', $user->user_id)
                    ->where('ad_status', 'active')
                    ->orderBy('created_at', 'desc')
                    ->get()
                : collect();
        }

        return view('admin.adboost.unmatched-payments', compact(
            'title', 'page_title', 'payments', 'boostTypes', 'boostDurations', 'advertsByPayment'
        ));
    }

    public function completeUnmatchedPayment(Request $request, $id)
    {
        $payment = UnmatchedPayment::where('status', 'unresolved')->findOrFail($id);

        $request->validate([
            'advert_id'      => 'required|integer|exists:adverts,id',
            'boost_type_id'  => 'required|integer|exists:boost_types,id',
            'duration_id'    => 'required|integer|exists:boost_durations,id',
        ]);

        $user = User::where('email', $payment->customer_email)->first();

        if (!$user) {
            return redirect()->back()->with('status', [
                'text' => 'No account found for ' . $payment->customer_email . ' — cannot complete.',
                'type' => 'danger',
            ]);
        }

        $advert = Advert::where('id', $request->advert_id)
            ->where('user_id', $user->user_id)
            ->first();

        if (!$advert) {
            return redirect()->back()->with('status', [
                'text' => 'That advert does not belong to ' . $payment->customer_email . ' — cannot complete.',
                'type' => 'danger',
            ]);
        }

        $boostType = BoostType::findOrFail($request->boost_type_id);
        $duration  = BoostDuration::findOrFail($request->duration_id);
        $amount    = $boostType->calculatePrice($duration->days, $duration->discount_percentage);

        DB::transaction(function () use ($payment, $advert, $boostType, $duration, $amount) {
            $boost = AdvertBoost::create([
                'advert_id'         => $advert->id,
                'user_id'           => $advert->user_id,
                'trans_id'          => $payment->transaction_id,
                'payment_reference' => $payment->reference,
                'amount'            => $amount,
                'boost_type'        => strtolower($boostType->name),
                'duration'          => $duration->days,
                'boost_type_id'     => $boostType->id,
                'duration_id'       => $duration->id,
                'start_date'        => Carbon::now(),
                'payment_status'    => 'paid',
                'boost_status'      => 'active',
                'upload_proof'      => 'no',
            ]);

            Advert::where('id', $advert->id)->update(['featured' => 'Yes']);

            $payment->update([
                'status'            => 'resolved',
                'resolved_boost_id' => $boost->id,
                'resolved_by'       => optional(AdminHelper::currentAdmin())->username,
                'resolved_at'       => Carbon::now(),
            ]);
        });

        Log::info('Admin completed unmatched payment', [
            'unmatched_payment_id' => $payment->id,
            'advert_id'            => $advert->id,
        ]);

        return redirect()->back()->with('status', [
            'text' => 'Payment matched to "' . $advert->ad_title . '" and boost activated.',
            'type' => 'success',
        ]);
    }
```

- [ ] **Step 3: Syntax-check the file**

Run: `php -l app/Http/Controllers/Admin/ManageBoost.php`
Expected: `No syntax errors detected`

- [ ] **Step 4: Add the two routes**

In `routes/admin.php`, find:

```php
    // Boost management
    Route::middleware(['admin.permission:view_active_boosts,manage_boost_status,manage_boost_payment'])->group(function () {
        Route::get('/boost/active', [ManageBoost::class, 'active'])->name('admin.boost.active');
        Route::get('/boost/completed', [ManageBoost::class, 'completed'])->name('admin.boost.completed');
        Route::get('/boost/unpaid', [ManageBoost::class, 'unpaid'])->name('admin.boost.unpaid');
        Route::get('/boost/confirm-payment', [ManageBoost::class, 'confirmPayment'])->name('admin.boost.confirm-payment');
        Route::get('/boost/paid', [ManageBoost::class, 'paid'])->name('admin.boost.paid');
        Route::post('/boost/status/{id}/{status}', [ManageBoost::class, 'status'])->name('admin.boost.status');
        Route::post('/boost/payment-status/{id}/{status}', [ManageBoost::class, 'payment'])->name('admin.boost.payment.status');
        Route::post('/boost/verify-and-activate/{id}', [ManageBoost::class, 'verifyAndActivate'])->name('admin.boost.verify-activate');
    });
```

Replace with (two new lines added before the closing `});`):

```php
    // Boost management
    Route::middleware(['admin.permission:view_active_boosts,manage_boost_status,manage_boost_payment'])->group(function () {
        Route::get('/boost/active', [ManageBoost::class, 'active'])->name('admin.boost.active');
        Route::get('/boost/completed', [ManageBoost::class, 'completed'])->name('admin.boost.completed');
        Route::get('/boost/unpaid', [ManageBoost::class, 'unpaid'])->name('admin.boost.unpaid');
        Route::get('/boost/confirm-payment', [ManageBoost::class, 'confirmPayment'])->name('admin.boost.confirm-payment');
        Route::get('/boost/paid', [ManageBoost::class, 'paid'])->name('admin.boost.paid');
        Route::post('/boost/status/{id}/{status}', [ManageBoost::class, 'status'])->name('admin.boost.status');
        Route::post('/boost/payment-status/{id}/{status}', [ManageBoost::class, 'payment'])->name('admin.boost.payment.status');
        Route::post('/boost/verify-and-activate/{id}', [ManageBoost::class, 'verifyAndActivate'])->name('admin.boost.verify-activate');
        Route::get('/boost/unmatched-payments', [ManageBoost::class, 'unmatchedPayments'])->name('admin.boost.unmatched-payments');
        Route::post('/boost/unmatched-payments/{id}/complete', [ManageBoost::class, 'completeUnmatchedPayment'])->name('admin.boost.unmatched-payments.complete');
    });
```

- [ ] **Step 5: Verify routes registered**

Run: `php artisan route:list --name=admin.boost.unmatched-payments`
Expected: two rows, `GET boost/unmatched-payments` and `POST boost/unmatched-payments/{id}/complete`, both listing `ManageBoost@unmatchedPayments` / `ManageBoost@completeUnmatchedPayment`.

- [ ] **Step 6: Commit**

```bash
git add app/Http/Controllers/Admin/ManageBoost.php routes/admin.php
git commit -m "Add admin controller methods and routes for completing unmatched payments"
```

---

### Task 4: Admin view + nav link

**Files:**
- Create: `resources/views/admin/adboost/unmatched-payments.blade.php`
- Modify: `resources/views/admin/layouts/nav.blade.php`

**Interfaces:**
- Consumes: view variables produced by `ManageBoost::unmatchedPayments()` (Task 3): `$title, $page_title, $payments, $boostTypes, $boostDurations, $advertsByPayment`; route names `admin.boost.unmatched-payments.complete`.

- [ ] **Step 1: Write the view**

```blade
@include('admin.layouts.header')
@include('admin.layouts.nav')

<main id="main" class="main">
    <div class="pagetitle">
        <div class="d-flex justify-content-between align-items-center">
            <h1>{{ $page_title }}</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/admin/dashboard">Dashboard</a></li>
                    <li class="breadcrumb-item active">{{ $page_title }}</li>
                </ol>
            </nav>
        </div>
    </div><!-- End Page Title -->

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title text-white mb-0">
                            <i class="bi bi-exclamation-octagon me-2"></i>Unmatched Payments
                        </h5>
                    </div>

                    <div class="card-body">
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

                        <p class="text-muted">
                            Paystack confirmed these payments as successful, but they don't match any boost or order record
                            in the database — usually because the paying client skipped our normal checkout flow. Pick the
                            advert the customer meant to boost and complete it below.
                        </p>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Reference</th>
                                        <th scope="col">Amount</th>
                                        <th scope="col">Customer</th>
                                        <th scope="col">Paid At</th>
                                        <th scope="col">Complete</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payments as $payment)
                                        @php $adverts = $advertsByPayment[$payment->id]; @endphp
                                        <tr>
                                            <td><code>{{ $payment->reference }}</code></td>
                                            <td class="fw-bold text-success">₦{{ number_format($payment->amount / 100, 2) }}</td>
                                            <td>{{ $payment->customer_email }}</td>
                                            <td>{{ $payment->paid_at ? $payment->paid_at->format('M j, Y g:ia') : '—' }}</td>
                                            <td>
                                                @if($adverts->isEmpty())
                                                    <span class="badge bg-light text-muted border">
                                                        No adverts found for this email — verify manually
                                                    </span>
                                                @else
                                                    <form method="POST" action="{{ route('admin.boost.unmatched-payments.complete', $payment->id) }}"
                                                          onsubmit="return confirm('Activate a boost on the selected advert for {{ $payment->customer_email }}?');"
                                                          class="d-flex flex-wrap gap-2 align-items-center">
                                                        @csrf
                                                        <select name="advert_id" class="form-select form-select-sm" style="width:auto" required>
                                                            @foreach($adverts as $advert)
                                                                <option value="{{ $advert->id }}">{{ $advert->ad_title }} (#{{ $advert->id }})</option>
                                                            @endforeach
                                                        </select>
                                                        <select name="boost_type_id" class="form-select form-select-sm" style="width:auto" required>
                                                            @foreach($boostTypes as $boostType)
                                                                <option value="{{ $boostType->id }}">{{ $boostType->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        <select name="duration_id" class="form-select form-select-sm" style="width:auto" required>
                                                            @foreach($boostDurations as $duration)
                                                                <option value="{{ $duration->id }}">{{ $duration->label }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button type="submit" class="btn btn-sm btn-outline-success d-flex align-items-center">
                                                            <i class="bi bi-check2-circle me-1"></i>Complete
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">No unmatched payments right now.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($payments->hasPages())
                            <div class="row align-items-center mt-4 pt-3 border-top">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center text-muted">
                                        <i class="bi bi-info-circle me-2"></i>
                                        <span>Showing <strong>{{ $payments->firstItem() }}</strong> to <strong>{{ $payments->lastItem() }}</strong> of <strong>{{ $payments->total() }}</strong> results</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex justify-content-end">
                                        {{ $payments->links('pagination::bootstrap-4') }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</main><!-- End #main -->
<style>
    .card-header {
        border-radius: 0.5rem 0.5rem 0 0 !important;
    }
    .table th {
        font-weight: 600;
        color: #495057;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(13, 110, 253, 0.05);
    }
</style>

@include('admin.layouts.footer')
```

- [ ] **Step 2: Add the nav link**

In `resources/views/admin/layouts/nav.blade.php`, find:

```blade
        <li>
          <a href="/boost/paid">
            <i class="bi bi-circle"></i><span>Paid Boosts</span>
          </a>
        </li>
      </ul>
    </li>
    @endadminCan
```

Replace with:

```blade
        <li>
          <a href="/boost/paid">
            <i class="bi bi-circle"></i><span>Paid Boosts</span>
          </a>
        </li>
        <li>
          <a href="/boost/unmatched-payments">
            <i class="bi bi-circle"></i><span>Unmatched Payments</span>
          </a>
        </li>
      </ul>
    </li>
    @endadminCan
```

- [ ] **Step 3: Clear compiled views and verify the page renders**

Run: `php artisan view:clear`
Run: `php artisan tinker --execute="echo view()->exists('admin.adboost.unmatched-payments') ? 'VIEW FOUND' : 'MISSING';"`
Expected: `VIEW FOUND`

- [ ] **Step 4: Commit**

```bash
git add resources/views/admin/adboost/unmatched-payments.blade.php resources/views/admin/layouts/nav.blade.php
git commit -m "Add unmatched-payments admin view and nav link"
```

---

### Task 5: End-to-end manual verification (local), then deploy

**Files:** none (verification + deployment only).

**Interfaces:** none — this task exercises Tasks 1–4 together.

- [ ] **Step 1: Seed one real-shaped unmatched payment locally and one candidate advert**

```bash
php artisan tinker --execute="
\$user = App\Models\User::first();
\$advert = App\Models\Advert::where('user_id', \$user->user_id)->where('ad_status', 'active')->first();
echo 'user_id=' . \$user->user_id . ' advert_id=' . (\$advert->id ?? 'NONE') . PHP_EOL;
App\Models\UnmatchedPayment::create([
    'reference' => 'plan-e2e-test',
    'transaction_id' => '888888',
    'amount' => 150000,
    'customer_email' => \$user->email,
    'paid_at' => now(),
]);
"
```
Note the printed `advert_id` — if `NONE`, pick any other user with an active advert and rerun with that user's id substituted.

- [ ] **Step 2: Load the admin page and confirm the row appears**

Log in to `/admin/login` as an admin with `manage_boost_payment` permission, visit `/boost/unmatched-payments`. Expected: one row for `plan-e2e-test`, showing the seeded amount/email, with a dropdown containing the advert from Step 1 pre-selected (most recent first).

- [ ] **Step 3: Complete it through the UI and verify the result**

Pick a boost type/duration, click Complete. Expected: redirected back with a green "Payment matched to... and boost activated" alert, and the row is gone from the unresolved list (since `unmatchedPayments()` filters `status = 'unresolved'`).

Verify in the database:
```bash
php artisan tinker --execute="
\$p = App\Models\UnmatchedPayment::where('reference', 'plan-e2e-test')->first();
echo \$p->status . ' boost_id=' . \$p->resolved_boost_id . ' resolved_by=' . \$p->resolved_by . PHP_EOL;
\$boost = \$p->boost;
echo \$boost->payment_status . ' ' . \$boost->boost_status . ' ' . \$boost->boost_type . ' ' . \$boost->duration . PHP_EOL;
echo App\Models\Advert::find(\$boost->advert_id)->featured . PHP_EOL;
"
```
Expected: `resolved boost_id=<some id> resolved_by=<admin username>`, then `paid active <type> <days>`, then `Yes`.

- [ ] **Step 4: Clean up the local test data**

```bash
php artisan tinker --execute="
\$p = App\Models\UnmatchedPayment::where('reference', 'plan-e2e-test')->first();
\$boostId = \$p->resolved_boost_id;
\$boost = App\Models\AdvertBoost::find(\$boostId);
App\Models\Advert::where('id', \$boost->advert_id)->update(['featured' => 'No']);
\$boost->delete();
\$p->delete();
echo 'cleaned up';
"
```

- [ ] **Step 5: Deploy to production — STOP and confirm with the user before running this step**

This touches the live database (new table) and live code. Do not run without explicit go-ahead in the moment, even though the plan lists the commands:

```bash
# Migration
scp -i /home/noble/.ssh/marketplace -P 22 -p database/migrations/2026_08_10_120000_create_unmatched_payments_table.php \
  admin@147.93.87.244:domains/marketplace.ng/marketplace/database/migrations/2026_08_10_120000_create_unmatched_payments_table.php

# Model, controller, webhook, routes, view, nav
scp -i /home/noble/.ssh/marketplace -P 22 -p app/Models/UnmatchedPayment.php \
  admin@147.93.87.244:domains/marketplace.ng/marketplace/app/Models/UnmatchedPayment.php
scp -i /home/noble/.ssh/marketplace -P 22 -p app/Http/Controllers/Api/PaystackWebhookController.php \
  admin@147.93.87.244:domains/marketplace.ng/marketplace/app/Http/Controllers/Api/PaystackWebhookController.php
scp -i /home/noble/.ssh/marketplace -P 22 -p app/Http/Controllers/Admin/ManageBoost.php \
  admin@147.93.87.244:domains/marketplace.ng/marketplace/app/Http/Controllers/Admin/ManageBoost.php
scp -i /home/noble/.ssh/marketplace -P 22 -p routes/admin.php \
  admin@147.93.87.244:domains/marketplace.ng/marketplace/routes/admin.php
scp -i /home/noble/.ssh/marketplace -P 22 -p resources/views/admin/adboost/unmatched-payments.blade.php \
  admin@147.93.87.244:domains/marketplace.ng/marketplace/resources/views/admin/adboost/unmatched-payments.blade.php
scp -i /home/noble/.ssh/marketplace -P 22 -p resources/views/admin/layouts/nav.blade.php \
  admin@147.93.87.244:domains/marketplace.ng/marketplace/resources/views/admin/layouts/nav.blade.php
```

Then, per [[feedback_verify_transfers_by_content]], diff every file back before trusting the transfer, run `php artisan migrate --force` on the remote app root, then `php artisan view:clear && php artisan route:clear && php artisan config:clear`.

- [ ] **Step 6: Final commit (if any local-only cleanup remains)**

```bash
git status --short
```
If clean (everything already committed in Tasks 1–4), no action needed.
