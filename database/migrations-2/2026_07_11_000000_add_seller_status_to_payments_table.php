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
        Schema::table('payments', function (Blueprint $table) {
            $table->enum('seller_status', ['pending', 'delivered', 'canceled'])->default('pending')->after('buyer_status');
            $table->timestamp('seller_status_date')->nullable()->after('seller_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['seller_status', 'seller_status_date']);
        });
    }
};
