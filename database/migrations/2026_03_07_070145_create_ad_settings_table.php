<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ad_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value');
            $table->timestamps();
        });

        DB::table('ad_settings')->insert([
            ['key' => 'max_images',       'value' => '8', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'min_images',       'value' => '3', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'image_strictness', 'value' => '7', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_settings');
    }
};
