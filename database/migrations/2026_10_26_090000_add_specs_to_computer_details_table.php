<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Laptops & Computers specs — processor, RAM, storage and storage type, as
 * the option values in App\Support\ComputerSpecs (filterable on listing pages).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('computer_details', function (Blueprint $table) {
            $table->string('processor', 40)->nullable()->after('model')->index();
            $table->string('ram', 10)->nullable()->after('processor')->index();
            $table->string('storage', 10)->nullable()->after('ram')->index();
            $table->string('storage_type', 12)->nullable()->after('storage');
        });
    }

    public function down(): void
    {
        Schema::table('computer_details', function (Blueprint $table) {
            $table->dropIndex(['processor']);
            $table->dropIndex(['ram']);
            $table->dropIndex(['storage']);
            $table->dropColumn(['processor', 'ram', 'storage', 'storage_type']);
        });
    }
};
