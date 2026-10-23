<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("UPDATE payments SET seller_status = 'shipped' WHERE seller_status = 'delivered'");
        DB::statement("ALTER TABLE payments MODIFY seller_status ENUM('pending', 'shipped', 'canceled') NOT NULL DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE payments MODIFY seller_status ENUM('pending', 'delivered', 'canceled') NOT NULL DEFAULT 'pending'");
    }
};
