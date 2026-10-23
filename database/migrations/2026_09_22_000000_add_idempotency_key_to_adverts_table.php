<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('adverts', function (Blueprint $table) {
            // Client-generated key (e.g. a UUID the mobile app creates once per
            // "Post Ad" tap and resends on retry) so a retried request after a
            // network error — timeout, connection reset — returns the advert that
            // was already created instead of creating a duplicate.
            //
            // Nullable + unique per user: rows without a key (old app versions,
            // other creation paths) never collide, since MySQL allows multiple
            // NULLs in a unique index.
            $table->string('idempotency_key', 64)->nullable()->after('user_id');
            $table->unique(['user_id', 'idempotency_key']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adverts', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'idempotency_key']);
            $table->dropColumn('idempotency_key');
        });
    }
};
