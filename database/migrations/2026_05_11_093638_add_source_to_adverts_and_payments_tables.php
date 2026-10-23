<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adverts', function (Blueprint $table) {
            $table->enum('source', ['web', 'api'])->default('web')->after('ad_status');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->enum('source', ['web', 'api'])->default('web')->after('payment_status');
        });
    }

    public function down(): void
    {
        Schema::table('adverts', function (Blueprint $table) {
            $table->dropColumn('source');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
};
