<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * `subject` and `status` already exist on production's `reports` table but
 * were added directly to the DB, not through a tracked migration (the
 * original create_reports_table migration only has user_id/advert_id/
 * message). This migration formalizes them (guarded, so it's a no-op where
 * they already exist) and adds the ad title/number snapshot columns needed
 * to fix reports showing "N/A" once the reported advert is deleted.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            if (! Schema::hasColumn('reports', 'subject')) {
                $table->string('subject', 225)->default('')->after('advert_id');
            }

            if (! Schema::hasColumn('reports', 'status')) {
                $table->enum('status', ['pending', 'resolved'])->default('pending')->after('message');
            }

            if (! Schema::hasColumn('reports', 'reported_ad_title')) {
                $table->string('reported_ad_title')->nullable()->after('advert_id');
            }

            if (! Schema::hasColumn('reports', 'reported_ad_number')) {
                $table->string('reported_ad_number')->nullable()->after('reported_ad_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            if (Schema::hasColumn('reports', 'reported_ad_number')) {
                $table->dropColumn('reported_ad_number');
            }

            if (Schema::hasColumn('reports', 'reported_ad_title')) {
                $table->dropColumn('reported_ad_title');
            }
        });
    }
};
