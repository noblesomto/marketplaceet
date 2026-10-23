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
        Schema::table('advert_boosts', function (Blueprint $table) {
            $table->foreignId('boost_type_id')->nullable()->after('advert_id')->constrained('boost_types')->onDelete('set null');
            $table->foreignId('duration_id')->nullable()->after('boost_type_id')->constrained('boost_durations')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advert_boosts', function (Blueprint $table) {
            $table->dropForeign(['boost_type_id']);
            $table->dropForeign(['duration_id']);
            $table->dropColumn(['boost_type_id', 'duration_id']);
        });
    }
};
