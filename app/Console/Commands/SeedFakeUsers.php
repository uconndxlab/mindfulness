<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class SeedFakeUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:seed {count} {--truncate}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed fake users into the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = $this->argument('count');
        $truncate = $this->option('truncate');

        if ($truncate) {
            $this->info('Truncating users table (excluding admins)...');
            User::where('role', '!=', 'admin')->delete();
        }

        $this->info("Seeding {$count} fake users...");
        
        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();

        for ($i = 0; $i < $count; $i++) {
            User::factory()->create();
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info("\nSuccessfully seeded {$count} fake users.");
    }
}
