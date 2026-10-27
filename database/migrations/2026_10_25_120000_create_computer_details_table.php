<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Laptops & Computers details — like phone_details, it holds the listing's
 * model (models.id, e.g. HP → EliteBook) so laptops get model pages.
 * Specs (RAM, storage, processor) are planned to live here too.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('computer_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advert_id')->unique()->constrained('adverts')->cascadeOnDelete();
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->foreignId('model')->nullable()->constrained('models')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('computer_details');
    }
};
