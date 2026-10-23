<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * When a reported advert is deleted, admins lose all record of who posted
 * it — including for cases that may need to be handed to law enforcement
 * (paid, undelivered order; seller deletes the ad to destroy evidence).
 * Snapshots the ad owner's identity onto the report at submission time,
 * the same way reported_ad_title/reported_ad_number already snapshot the
 * ad itself, so that identity survives the advert being deleted later.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('reported_seller_id')->nullable()->after('reported_ad_number');
            $table->string('reported_seller_name')->nullable()->after('reported_seller_id');
            $table->string('reported_seller_phone')->nullable()->after('reported_seller_name');
            $table->string('reported_seller_email')->nullable()->after('reported_seller_phone');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn(['reported_seller_id', 'reported_seller_name', 'reported_seller_phone', 'reported_seller_email']);
        });
    }
};
