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
        // Drop the existing table
        Schema::dropIfExists('archived_messages');

        // Recreate with correct foreign keys
        Schema::create('archived_messages', function (Blueprint $table) {
        $table->id();
        $table->string('user_id', 5); // Stores users.user_id (string, not foreign key)
        $table->unsignedBigInteger('advert_id'); // the advert conversation
        $table->string('other_user_id', 5); // Stores users.user_id (string, not foreign key)
        $table->timestamp('archived_at');
        $table->timestamps();

        // Indexes for performance
        $table->index(['user_id', 'advert_id']);
        $table->index('other_user_id');
        $table->unique(['user_id', 'advert_id', 'other_user_id']);

        // Only foreign key for advert_id (actual foreign key relationship)
        $table->foreign('advert_id')->references('id')->on('adverts')->onDelete('cascade');

        // NO foreign keys for user_id and other_user_id since they're custom string identifiers
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
