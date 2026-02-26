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
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->enum('type', ['check_in', 'self_rating'])->nullable();
        });

        Schema::table('quiz_answers', function (Blueprint $table) {
            $table->dropIndex(['reflection_type']);
            $table->dropColumn('reflection_type');
        });

        Schema::table('quiz_answers', function (Blueprint $table) {
            $table->enum('reflection_type', ['check_in', 'self_rating'])->nullable();
            $table->index('reflection_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('type');
        });

        Schema::table('quizzes', function (Blueprint $table) {
            $table->enum('type', ['check_in', 'rate_my_awareness'])->nullable();
        });

        Schema::table('quiz_answers', function (Blueprint $table) {
            $table->dropIndex(['reflection_type']);
            $table->dropColumn('reflection_type');
        });

        Schema::table('quiz_answers', function (Blueprint $table) {
            $table->enum('reflection_type', ['check_in', 'rate_my_awareness'])->nullable();
            $table->index('reflection_type');
        });
    }
};
