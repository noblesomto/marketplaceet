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
        Schema::table('models', function (Blueprint $table) {
            $table->unsignedBigInteger('cat_id')->nullable()->after('id');
            $table->unsignedBigInteger('subcat_id')->nullable()->after('cat_id');
            $table->integer('model_id')->nullable()->after('brand_id');
            // keywords may already exist on live — only add if missing
            if (!Schema::hasColumn('models', 'keywords')) {
                $table->text('keywords')->nullable()->after('model_slug');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('models', function (Blueprint $table) {
            $table->dropColumn(['cat_id', 'subcat_id', 'model_id', 'keywords']);
        });
    }
};
