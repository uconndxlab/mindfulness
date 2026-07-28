<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('days', function (Blueprint $table) {
            $table->unsignedTinyInteger('num_petals')->nullable()->after('completion_message');
        });

        DB::table('days')
            ->whereNotNull('media_path')
            ->orderBy('id')
            ->each(function (object $day) {
                if (!preg_match('/(\d+)\.svg$/', $day->media_path, $matches)) {
                    return;
                }

                DB::table('days')
                    ->where('id', $day->id)
                    ->update(['num_petals' => min(5, (int) $matches[1])]);
            });

        Schema::table('days', function (Blueprint $table) {
            $table->dropColumn('media_path');
        });
    }

    public function down(): void
    {
        Schema::table('days', function (Blueprint $table) {
            $table->string('media_path')->nullable()->after('completion_message');
        });

        DB::table('days')
            ->whereNotNull('num_petals')
            ->orderBy('id')
            ->each(function (object $day) {
                DB::table('days')
                    ->where('id', $day->id)
                    ->update(['media_path' => 'Flower-'.$day->num_petals.'.svg']);
            });

        Schema::table('days', function (Blueprint $table) {
            $table->dropColumn('num_petals');
        });
    }
};
