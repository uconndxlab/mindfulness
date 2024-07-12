<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Config;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Config::updateOrCreate(
            ['key' => 'first_activity_id'],
            ['value' => '1']
        );
        Config::updateOrCreate(
            ['key' => 'first_day_id'],
            ['value' => '1']
        );
        Config::updateOrCreate(
            ['key' => 'first_module_id'],
            ['value' => '1']
        );
    }
}
