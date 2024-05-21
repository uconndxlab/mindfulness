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
        Schema::table('lessons', function (Blueprint $table) {
            $table->string('sub_header')->nullable()->after('title');
            $table->string('file_name')->nullable()->after('updated_at');
            $table->enum('end_behavior', ['quiz', 'journal', 'none'])->default('none');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn('sub_header');
            $table->dropColumn('file_name');
            $table->dropColumn('end_behavior');
        });
    }
};
