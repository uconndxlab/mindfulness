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
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('day_id')->nullable()->constrained('days')->onDelete('set null');
            $table->string('title');
            $table->enum('type', ['lesson', 'practice', 'reflection', 'journal'])->nullable();
            $table->integer('time')->nullable();
            $table->string('completion_message')->default('Congrats on completing the activity!');
            $table->integer('order');
            $table->foreignId('next')->nullable()->constrained('activities')->onDelete('set null');
            $table->boolean('optional')->default(false);
            $table->boolean('deleted')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
