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
        Schema::create('phone_details', function (Blueprint $table) {
            $table->id();
            $table->string('cat_id');
            $table->foreignId('advert_id')->constrained()->onDelete('cascade');
            $table->string('brand_id');
            $table->string('phone_id');
            $table->string('model');
            $table->string('color');
            $table->string('device');
            $table->string('condition');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phone_details');
    }
};
