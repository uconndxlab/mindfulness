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
        Schema::table('quiz_answers', function (Blueprint $table) {
            $table->enum('reflection_type', ['check_in', 'rate_my_awareness'])->nullable();
            $table->foreignId('activity_id')->nullable()->constrained('activities')->onDelete('cascade');
            $table->decimal('average', 6, 3)->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_type')->nullable();
            
            // better query performance
            $table->index('reflection_type');
            $table->index(['subject_id', 'subject_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_answers', function (Blueprint $table) {
            $table->dropIndex(['reflection_type']);
            $table->dropIndex(['subject_id_subject_type']);
            $table->dropForeign(['activity_id']);
            $table->dropColumn(['reflection_type', 'activity_id', 'average', 'subject_id', 'subject_type']);
        });
    }
};
