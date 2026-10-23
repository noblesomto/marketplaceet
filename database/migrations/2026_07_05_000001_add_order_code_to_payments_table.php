<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('order_code', 8)->nullable()->unique()->after('id');
        });

        // Backfill existing rows
        DB::table('payments')->whereNull('order_code')->orderBy('id')->each(function ($row) {
            do {
                $code = strtoupper(Str::random(8));
            } while (DB::table('payments')->where('order_code', $code)->exists());

            DB::table('payments')->where('id', $row->id)->update(['order_code' => $code]);
        });
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropUnique(['order_code']);
            $table->dropColumn('order_code');
        });
    }
};
