<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Users who already received a reminder under the old 7-day system are
     * marked at milestone 7 so they are not immediately re-emailed on deploy.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereNotNull('last_reminded_at')
            ->whereNull('last_inactivity_reminder_day')
            ->update(['last_inactivity_reminder_day' => 7]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')
            ->where('last_inactivity_reminder_day', 7)
            ->update(['last_inactivity_reminder_day' => null]);
    }
};
