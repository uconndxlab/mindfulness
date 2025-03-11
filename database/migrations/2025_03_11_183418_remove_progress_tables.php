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
        Schema::dropIfExists('user_activity');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('user_activity', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('activity_id')->constrained('activities');
            $table->boolean('completed')->default(false);
            $table->boolean('unlocked')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'activity_id']);
        });
    }
};
