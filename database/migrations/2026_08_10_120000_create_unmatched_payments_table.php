<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unmatched_payments', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('transaction_id')->nullable();
            $table->unsignedBigInteger('amount');
            $table->string('customer_email');
            $table->timestamp('paid_at')->nullable();
            $table->enum('status', ['unresolved', 'resolved'])->default('unresolved');
            $table->unsignedBigInteger('resolved_boost_id')->nullable();
            $table->string('resolved_by')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->foreign('resolved_boost_id')->references('id')->on('advert_boosts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unmatched_payments');
    }
};
