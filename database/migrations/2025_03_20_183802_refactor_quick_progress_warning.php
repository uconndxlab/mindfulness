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
            $table->dropColumn(['last_day_completed_at', 'last_day_name']);

            $table->boolean('quick_progress_warning')->default(false);
            $table->integer('last_day_completed_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['quick_progress_warning', 'last_day_completed_id']);

            $table->timestamp('last_day_completed_at')->nullable();
            $table->string('last_day_name')->nullable();
        });
    }
};
