<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeFeedbackFieldsToInteger extends Migration
{
    public function up()
    {
        Schema::table('feedback', function (Blueprint $table) {
            $table->integer('rating')->change();
            $table->integer('satisfaction')->change();
            $table->integer('reliable')->change();
            $table->integer('friendly')->change();
        });
    }

    public function down()
    {
        Schema::table('feedback', function (Blueprint $table) {
            $table->string('rating')->change();
            $table->string('satisfaction')->change();
            $table->string('reliable')->change();
            $table->string('friendly')->change();
        });
    }
}

