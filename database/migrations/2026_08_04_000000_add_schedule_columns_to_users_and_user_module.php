<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('active_days_count')->default(0)->after('last_active_at');
        });

        Schema::table('user_module', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('unlocked');
            $table->unsignedInteger('active_days_count')->default(0)->after('start_date');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('active_days_count');
        });

        Schema::table('user_module', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'active_days_count']);
        });
    }
};
