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
        // store scorm packages
        Schema::create('scorm_packages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type')->default('scorm');
            $table->string('version')->default('1.2');
            $table->string('entry_point');
            $table->string('status')->default('active');
            $table->string('xapi_activity_id')->nullable();
            $table->timestamps();
        });

        // store scorm data
        Schema::create('scorm_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('scorm_package_id')->constrained('scorm_packages');
            $table->string('lesson_status')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->text('suspend_data')->nullable();
            $table->string('lesson_location')->nullable();
            $table->json('cmi_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scorm_sessions');
        Schema::dropIfExists('scorm_packages');
    }
};
