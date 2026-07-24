<?php

namespace App\Console\Commands;

use App\Enums\MilestoneType;
use App\Models\User;
use App\Services\CompletionReportService;
use Illuminate\Console\Command;

class BackfillCompletionReports extends Command
{
    protected $signature = 'completion-reports:backfill';

    protected $description = 'Create completion report rows and PDFs for existing Part 4 completers';

    public function handle(CompletionReportService $completionReportService): int
    {
        $users = User::query()
            ->whereHas('milestones', fn ($query) => $query->where('type', MilestoneType::Module4))
            ->get();

        $created = 0;
        $skipped = 0;
        $failedPdf = 0;

        foreach ($users as $user) {
            $existing = $user->completionReport()->exists();
            if ($existing) {
                $skipped++;
                continue;
            }

            $report = $completionReportService->createForUser($user, notifyAdmin: false);
            $created++;

            if (!$report->pdf_path) {
                $failedPdf++;
                $this->warn("Report created without PDF for {$user->hh_id} (may lack survey data).");
            } else {
                $this->line("Created report for {$user->hh_id}.");
            }
        }

        $this->info("Backfill complete. Created: {$created}, skipped: {$skipped}, missing PDF: {$failedPdf}.");

        return self::SUCCESS;
    }
}
