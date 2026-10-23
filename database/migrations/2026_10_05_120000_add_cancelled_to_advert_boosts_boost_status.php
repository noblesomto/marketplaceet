<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE advert_boosts MODIFY boost_status ENUM('pending','active','completed','cancelled') NOT NULL");
    }

    public function down(): void
    {
        DB::table('advert_boosts')->where('boost_status', 'cancelled')->update(['boost_status' => 'pending']);
        DB::statement("ALTER TABLE advert_boosts MODIFY boost_status ENUM('pending','active','completed') NOT NULL");
    }
};
