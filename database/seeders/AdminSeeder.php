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
        $user = User::where('email', 'zoro@op.com')->first();
        if ($user) {
            if ($user->role ==='admin') {
                $user->update(['role' => 'user']);
            }
            else {
                $user->update(['role' => 'admin']);
            }
        }
    }
}
