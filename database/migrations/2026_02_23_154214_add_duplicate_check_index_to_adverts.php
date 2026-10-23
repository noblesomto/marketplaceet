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
            // Composite index for duplicate-ad check query:
            // WHERE user_id = ? AND category = ? AND sub_category = ? AND ad_status = 'active'
            $table->index(['user_id', 'category', 'sub_category', 'ad_status'], 'adverts_duplicate_check_index');
        });
    }

    public function down(): void
    {
        Schema::table('adverts', function (Blueprint $table) {
            $table->dropIndex('adverts_duplicate_check_index');
        });
    }
};
