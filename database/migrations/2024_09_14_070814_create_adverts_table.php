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
        Schema::create('adverts', function (Blueprint $table) {
            $table->id();
            $table->string('user_id', 5);
            $table->string('ad_id');
            $table->string('ad_type');
            $table->string('ad_title')->nullable();
            $table->string('title_slug');
            $table->string('category');
            $table->string('sub_category');
            $table->string('brand');
            $table->string('price');
            $table->string('price_type');
            $table->string('buy_direct');
            $table->text('description');
            $table->string('state');
            $table->string('lga');
            $table->string('ad_status');
            $table->string('featured');
            $table->string('shipment')->nullable();
            $table->string('shipping')->nullable();
            $table->string('views');
            $table->string('keyword')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adverts');
    }
};
