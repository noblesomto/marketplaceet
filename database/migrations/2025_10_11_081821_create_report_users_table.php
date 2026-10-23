<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_users', function (Blueprint $table) {
            $table->id();

            // The user being reported
            $table->foreignId('reported')
                  ->constrained('users')
                  ->onDelete('cascade');

            // The user who made the report
            $table->foreignId('reporter')
                  ->constrained('users')
                  ->onDelete('cascade');

            // Report details
            $table->string('subject');
            $table->text('message')->nullable();

            $table->timestamps();

            // Prevent duplicate reports from the same reporter to the same reported user
            $table->unique(['reported', 'reporter']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_users');
    }
};

