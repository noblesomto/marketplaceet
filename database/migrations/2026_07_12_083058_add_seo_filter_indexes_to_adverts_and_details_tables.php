<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Indexes needed by the SEO location/category/brand/model/price/condition
     * pages — adverts.state, adverts.brand, adverts.price, adverts.item_condition,
     * and car_details/phone_details.model previously had no index at all
     * (only adverts.state_slug did), forcing full scans on every filtered page.
     */
    public function up(): void
    {
        Schema::table('adverts', function (Blueprint $table) {
            $table->index('state');
            $table->index('brand');
            $table->index('price');
            $table->index('item_condition');
            $table->index(['category', 'state_slug']);
            $table->index(['category', 'state']);
        });

        Schema::table('car_details', function (Blueprint $table) {
            $table->index('model');
        });

        Schema::table('phone_details', function (Blueprint $table) {
            $table->index('model');
        });
    }

    public function down(): void
    {
        Schema::table('adverts', function (Blueprint $table) {
            $table->dropIndex(['state']);
            $table->dropIndex(['brand']);
            $table->dropIndex(['price']);
            $table->dropIndex(['item_condition']);
            $table->dropIndex(['category', 'state_slug']);
            $table->dropIndex(['category', 'state']);
        });

        Schema::table('car_details', function (Blueprint $table) {
            $table->dropIndex(['model']);
        });

        Schema::table('phone_details', function (Blueprint $table) {
            $table->dropIndex(['model']);
        });
    }
};
