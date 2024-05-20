<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory()->create([
            'id' => User::max('id') + 1,
            'name' => 'Admin-1',
            'email' => 'admin@example.com',
            'password' => bcrypt('adminpass$'),
            'role' => 'admin',
        ]);
    }
}
