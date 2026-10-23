<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Buy Direct escrow: the platform holds the buyer's payment until the seller
 * ships and the buyer confirms receipt (or the auto-release window passes),
 * then pays the seller out. Covers delivery details now that courier
 * integrations are off, deadlines, disputes, refunds and payout tracking.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('delivery_address', 500)->nullable()->after('state');
            $table->string('delivery_lga')->nullable()->after('delivery_address');

            $table->timestamp('paid_at')->nullable()->after('payment_status');
            $table->timestamp('ship_by')->nullable()->after('paid_at');
            $table->timestamp('shipped_at')->nullable()->after('ship_by');
            $table->timestamp('auto_release_at')->nullable()->after('shipped_at');
            $table->timestamp('buyer_confirmed_at')->nullable()->after('auto_release_at');
            $table->string('release_type', 20)->nullable()->after('buyer_confirmed_at'); // buyer | auto | admin

            $table->string('canceled_by', 20)->nullable(); // buyer | seller | system | admin
            $table->string('cancel_reason')->nullable();
            $table->timestamp('canceled_at')->nullable();

            $table->enum('dispute_status', ['none', 'open', 'resolved'])->default('none');
            $table->text('dispute_reason')->nullable();
            $table->timestamp('dispute_opened_at')->nullable();
            $table->string('dispute_resolution', 20)->nullable(); // refund | release
            $table->text('dispute_admin_note')->nullable();
            $table->timestamp('dispute_resolved_at')->nullable();

            $table->enum('refund_status', ['none', 'processing', 'refunded', 'failed'])->default('none');
            $table->decimal('refund_amount', 14, 2)->nullable();
            $table->string('refund_reference')->nullable();
            $table->string('refund_reason')->nullable();
            $table->text('refund_error')->nullable();
            $table->timestamp('refunded_at')->nullable();

            $table->enum('payout_status', ['none', 'processing', 'paid', 'failed'])->default('none');
            $table->decimal('payout_amount', 14, 2)->nullable();
            $table->string('payout_reference')->nullable()->unique();
            $table->string('payout_transfer_id')->nullable();
            $table->text('payout_error')->nullable();
            $table->unsignedInteger('payout_attempts')->default(0);
            $table->timestamp('paid_out_at')->nullable();

            $table->index(['payment_status', 'seller_status', 'ship_by'], 'payments_ship_deadline_idx');
            $table->index(['payment_status', 'buyer_status', 'auto_release_at'], 'payments_auto_release_idx');
        });

        // Orders settled before escrow tracking existed are already paid out.
        DB::table('payments')->where('seller_settlement', 'yes')->update(['payout_status' => 'paid']);
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('payments_ship_deadline_idx');
            $table->dropIndex('payments_auto_release_idx');
            $table->dropUnique(['payout_reference']);
            $table->dropColumn([
                'delivery_address', 'delivery_lga',
                'paid_at', 'ship_by', 'shipped_at', 'auto_release_at', 'buyer_confirmed_at', 'release_type',
                'canceled_by', 'cancel_reason', 'canceled_at',
                'dispute_status', 'dispute_reason', 'dispute_opened_at', 'dispute_resolution', 'dispute_admin_note', 'dispute_resolved_at',
                'refund_status', 'refund_amount', 'refund_reference', 'refund_reason', 'refund_error', 'refunded_at',
                'payout_status', 'payout_amount', 'payout_reference', 'payout_transfer_id', 'payout_error', 'payout_attempts', 'paid_out_at',
            ]);
        });
    }
};
