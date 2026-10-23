# Admin: View & Complete Unmatched Paystack Payments

## Background

Some clients (most likely the mobile app) pay for ad boosts by calling Paystack directly with a self-generated reference, bypassing this backend's boost-initialize endpoint entirely and attaching no metadata. When Paystack later sends the `charge.success` webhook, `PaystackWebhookController` can't find any `AdvertBoost` or `Payment` row to match the reference against. As of 2026-08-10, this previously caused the payment to be silently misclassified as `buy_direct` and dropped — confirmed via Paystack's own transaction history to have happened 7 times since 2026-05-20 across 6 customers (₦16,500 total), all requiring manual DB fixes once discovered.

A same-day fix (`PaystackWebhookController::reportUnmatchedPayment()`) stopped the silent drop: it now logs a critical error and emails `config('global.admin_email')` via `App\Mail\UnmatchedPaymentMail` when this happens. That is a detection mechanism only — there is no persisted record and no way for admin to act on it from the backend. This spec covers giving admin a proper queue to review and manually complete these payments (i.e. link the payment to the correct advert and activate the boost), without needing to dig through logs/email/SSH.

Root cause (the mobile client bypassing the backend) is out of scope — that code lives outside this repo. This is a detection + remediation tool for as long as that gap exists.

## Data model

New migration + model: `unmatched_payments`.

| column | type | notes |
|---|---|---|
| `id` | bigint PK | |
| `reference` | varchar, unique | Paystack transaction reference |
| `transaction_id` | varchar | Paystack's numeric transaction id |
| `amount` | integer | kobo, as returned by Paystack |
| `customer_email` | varchar | from Paystack `customer.email` |
| `paid_at` | timestamp | from Paystack |
| `status` | enum(`unresolved`,`resolved`) | default `unresolved` |
| `resolved_boost_id` | bigint, nullable, FK → `advert_boosts.id` | set on completion |
| `resolved_by` | varchar, nullable | admin identifier |
| `resolved_at` | timestamp, nullable | |
| `created_at` / `updated_at` | timestamp | |

Model `App\Models\UnmatchedPayment` with a `boost()` belongsTo relation to `AdvertBoost`.

## Webhook change

`PaystackWebhookController::reportUnmatchedPayment()` gains one line: `UnmatchedPayment::firstOrCreate(['reference' => $reference], [...])` alongside the existing `Log::critical` and dedup'd `Mail::queue`. `firstOrCreate` on the unique `reference` column makes this safe against Paystack redelivering the same webhook event.

## Admin backend

New methods on the existing `App\Http\Controllers\Admin\ManageBoost` controller (same file as `verifyAndActivate`/`payment`, following the same conventions):

- **`unmatchedPayments(Request $request)`** — lists `UnmatchedPayment::where('status', 'unresolved')->orderBy('paid_at', 'desc')->paginate(20)`. Same `$title`/`$page_title`/`compact()`/`view('admin.adboost.unmatched-payments', ...)` pattern as every other list method in this controller.

- **`completeUnmatchedPayment(Request $request, $id)`** — validates `advert_id`, `boost_type_id`, `duration_id` are present and belong to real records. Looks up the advert (must belong to a user whose email matches `customer_email`, as a guard against admin picking an unrelated advert by tampering with the form). Inside `DB::transaction()`:
  1. Creates an `AdvertBoost` row: `payment_status: paid`, `boost_status: active`, `trans_id` = the unmatched payment's `transaction_id`, `payment_reference` = its `reference`, `start_date: now()`. Populates both the FK columns (`boost_type_id`, `duration_id` from the chosen `boost_types`/`boost_durations` rows) and the legacy enum/int columns (`boost_type` = the type's slug/name, `duration` = the duration's `days`) so the row reads correctly under both the old and new schema — `amount` computed as `boost_type.daily_rate * duration.days` (matching how existing rows' amounts were derived, e.g. Highlight's 214.29/day × 7 = 1500.03).
  2. Sets `advert.featured = 'Yes'`.
  3. Updates the `unmatched_payments` row: `status: resolved`, `resolved_boost_id`, `resolved_by` (current admin), `resolved_at: now()`.
  
  Flash `redirect()->back()->with('status', ['text' => '...', 'type' => 'success'|'danger'])`, matching the existing pattern (using `'danger'` rather than the pre-existing `'error'` typo seen elsewhere, since this new view needs the flash to actually render styled — not touching other views' existing behavior).

## Advert lookup for the complete form

`User::where('email', $unmatchedPayment->customer_email)->first()` → that user's active adverts (`ad_status = 'active'`), ordered `created_at desc`. The most recent is pre-selected in the dropdown (matches the empirical pattern seen in the 6 real incidents: payment happened within minutes of the advert being created/edited). If the customer has 2+ active adverts, admin picks freely from the dropdown — no forced confirmation step, since the tool can't determine intent better than admin judgment, and the customer's email is shown prominently so admin can contact them directly if unsure. If no user/adverts are found for that email, the row shows "no adverts found for this email — verify manually" instead of a form.

## Routes

```php
Route::middleware(['admin.permission:manage_boost_payment'])->group(function () {
    Route::get('/boost/unmatched-payments', [ManageBoost::class, 'unmatchedPayments'])->name('admin.boost.unmatched-payments');
    Route::post('/boost/unmatched-payments/{id}/complete', [ManageBoost::class, 'completeUnmatchedPayment'])->name('admin.boost.unmatched-payments.complete');
});
```
Reuses the existing `manage_boost_payment` permission slug (already gates "mark boost paid") rather than adding a new one — no permission-seeder changes needed.

## View & navigation

`resources/views/admin/adboost/unmatched-payments.blade.php` — same skeleton as `confirm-payment.blade.php` (header/nav includes, card, session-status alert block, table, pagination, footer). Table columns: Reference, Amount, Customer Email, Paid At, Actions. The "Actions" cell is an inline form per row (not a JS-confirm redirect, since this action needs real input): advert dropdown, boost-type dropdown, duration dropdown, Complete button. Rows with no matching customer adverts show a plain note instead of the form.

New `<li>` inside the existing `<ul id="boost">` block in `resources/views/admin/layouts/nav.blade.php`, same `bi-circle` icon convention, labeled "Unmatched Payments", gated by `@adminCan('manage_boost_payment')`.

## Testing

No automated tests exist today for `ManageBoost`'s other methods, so this matches that convention: manual verification only. Plan: create/reuse a real `unmatched_payments` row (either wait for a natural occurrence or manually insert a test row pointing at a real historical reference), complete it through the admin UI, confirm the resulting `advert_boosts` row and `adverts.featured` flag, and confirm the `unmatched_payments` row flips to `resolved` with `resolved_boost_id` set.

## Explicitly out of scope

- Buy-Direct order payments going through this same screen — every real incident so far has been a boost payment; Buy-Direct already has a separate resume-payment flow (`UserController::resumePayment`).
- Fixing the actual client (mobile app) that generates bypass references — outside this repo.
- Forced confirmation/notes when multiple candidate adverts exist — left to admin judgment per user decision during design.
