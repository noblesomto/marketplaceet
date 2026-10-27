<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mobile Phones specs — RAM and storage, as the option values in
 * App\Support\PhoneSpecs (filterable on listing pages).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('phone_details', function (Blueprint $table) {
            $table->string('ram', 10)->nullable()->after('condition')->index();
            $table->string('storage', 10)->nullable()->after('ram')->index();
        });
    }

    public function down(): void
    {
        Schema::table('phone_details', function (Blueprint $table) {
            $table->dropIndex(['ram']);
            $table->dropIndex(['storage']);
            $table->dropColumn(['ram', 'storage']);
        });
    }
};
