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
        Schema::create('advert_boosts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advert_id')->constrained()->onDelete('cascade');
            $table->string('user_id');
            $table->string('trans_id');
            $table->string('amount');
            $table->enum('duration', ['7', '14', '30', '90', '180']);
            $table->enum('payment_status', ['pending', 'paid']);
            $table->enum('boost_status', ['pending', 'completed']);
            $table->timestamps();

            $table->foreign('user_id')->references('user_id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('advert_boosts');
    }
};
