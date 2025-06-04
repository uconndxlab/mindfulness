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
        // store xapi packages
        Schema::create('xapi_packages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('package_id');
            $table->string('entry_point');
            $table->string('xapi_activity_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('xapi_packages');
    }
};
