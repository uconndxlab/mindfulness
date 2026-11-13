<?php

namespace Database\Seeders;

use App\Models\Config;
use Illuminate\Database\Seeder;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Config::updateOrCreate(
            ['key' => 'registration_locked'],
            ['value' => false]
        );

        Config::updateOrCreate(
            ['key' => 'invitation_only_mode'],
            ['value' => false]
        );
    }
}
