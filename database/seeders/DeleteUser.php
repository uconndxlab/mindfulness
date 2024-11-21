<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DeleteUser extends Seeder
{
    /**
     * Run the database seeds.
     * @param string $email
     */
    public function run(bool $email = null): void
    {
        User::where('email', $email)->delete();
    }
}
