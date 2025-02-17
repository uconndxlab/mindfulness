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
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_day_completed_at')->nullable();
            $table->string('last_day_name')->nullable();
            $table->integer('block_next_day_act')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('last_day_completed_at');
            $table->dropColumn('last_day_name');
            $table->dropColumn('block_next_day_act');
        });
    }
};
