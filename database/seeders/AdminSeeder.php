<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * @param string $email
     */
    public function run(bool $email = null): void
    {
        $user = User::where('email', $email)->first();
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
