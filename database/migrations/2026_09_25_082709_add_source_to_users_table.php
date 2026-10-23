<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('source', ['web', 'app', 'google', 'facebook', 'apple', 'unknown'])
                ->default('unknown')
                ->after('acc_type');
        });

        // Backfill accounts created before this column existed. Social sign-ups
        // are recoverable from their provider id columns; everything else
        // predates this tracking and stays 'unknown' rather than guessing.
        DB::table('users')->whereNotNull('google_id')->update(['source' => 'google']);
        DB::table('users')->whereNull('google_id')->whereNotNull('facebook_id')->update(['source' => 'facebook']);
        DB::table('users')->whereNull('google_id')->whereNull('facebook_id')->whereNotNull('apple_id')->update(['source' => 'apple']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
