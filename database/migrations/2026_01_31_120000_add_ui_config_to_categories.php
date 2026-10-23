<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * SAFE FOR PRODUCTION:
     * - Adds nullable JSON column
     * - No data modification
     * - Fully backward compatible
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->json('ui_config')->nullable()->after('category_slug');
        });

        Schema::table('sub_categories', function (Blueprint $table) {
            $table->json('ui_config')->nullable()->after('sub_cat_slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('ui_config');
        });

        Schema::table('sub_categories', function (Blueprint $table) {
            $table->dropColumn('ui_config');
        });
    }
};
