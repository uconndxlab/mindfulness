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
        //this will be a simple way to track progress - assuming content will not really change on launch
        Schema::table('lessons', function (Blueprint $table) {
            $table->integer('order')->default(100000);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->integer('progress')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn('order');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('progress');
        });
    }
};
