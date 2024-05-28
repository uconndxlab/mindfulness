<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ExampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //used to pass arguments into DatabaseSeeder - helper
        $examples = true;
        $this->call(DatabaseSeeder::class, false, compact('examples'));
    }
}
