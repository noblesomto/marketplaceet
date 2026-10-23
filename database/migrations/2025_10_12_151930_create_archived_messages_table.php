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
        // Migration: create_archived_messages_table.php
        Schema::create('archived_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // user who archived
            $table->unsignedBigInteger('advert_id'); // the advert conversation
            $table->unsignedBigInteger('other_user_id'); // the other party in conversation
            $table->timestamp('archived_at');
            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'advert_id']);
            $table->unique(['user_id', 'advert_id', 'other_user_id']);

            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('advert_id')->references('id')->on('adverts')->onDelete('cascade');
            $table->foreign('other_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archived_messages');
    }
};
