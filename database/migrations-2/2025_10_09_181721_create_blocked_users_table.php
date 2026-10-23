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
        // Migration: create_blocked_users_table.php
        Schema::create('blocked_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('blocker_id'); // user who is blocking
            $table->unsignedBigInteger('blocked_id'); // user being blocked
            $table->unsignedBigInteger('advert_id')->nullable(); // specific advert context
            $table->timestamps();

            // Indexes for performance
            $table->index(['blocker_id', 'blocked_id']);
            $table->index(['blocker_id', 'advert_id']);

            // Prevent duplicate blocks
            $table->unique(['blocker_id', 'blocked_id', 'advert_id']);

            // Foreign keys
            $table->foreign('blocker_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('blocked_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('advert_id')->references('id')->on('adverts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blocked_users');
    }
};
