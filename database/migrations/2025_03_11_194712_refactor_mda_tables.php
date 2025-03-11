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
        // nothing to be done for modules
        Schema::table('days', function (Blueprint $table) {
            // drop columns
            $table->dropColumn(['deleted', 'time']);
        });
        Schema::table('activities', function(Blueprint $table) {
            $table->boolean('skippable')->default(false)->after('optional');
            
            // drop columns
            $table->dropColumn(['final', 'deleted', 'no_skip']);
            // 'next' foreignKey
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('days', function (Blueprint $table) {
            $table->boolean('deleted')->default(false);
            $table->integer('time')->nullable();
        });
        Schema::table('activities', function(Blueprint $table) {
            $table->boolean('final')->default(false);
            // $table->foreignId('next')->nullable()->constrained('activities');
            $table->boolean('deleted')->default(false);
            $table->boolean('no_skip')->default(false);
            
            // drop columns
            $table->dropColumn(['skippable']);
        });
    }
};
