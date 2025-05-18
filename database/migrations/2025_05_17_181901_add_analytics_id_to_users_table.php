<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('analytics_id')->nullable()->after('id');
        });

        // generate ids for existing users
        User::whereNull('analytics_id')->each(function ($user) {
            // ensure unique
            do {
                $user->analytics_id = (string) Str::uuid();
            } while (User::where('analytics_id', $user->analytics_id)->exists());
            $user->save();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('analytics_id');
        });
    }
}; 