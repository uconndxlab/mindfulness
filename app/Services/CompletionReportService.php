<?php

namespace App\Services;

use App\Enums\MilestoneType;
use App\Mail\ModuleCompletedMail;
use App\Mail\ReportReadyAdminMail;
use App\Models\CompletionReport;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class CompletionReportService
{
    public function __construct(
        private ModuleEmailReportService $reportService,
        private ModuleChartService $chartService,
    ) {}

    public function createForUser(User $user, bool $notifyAdmin = true): CompletionReport
    {
        $existing = CompletionReport::where('user_id', $user->id)->first();
        if ($existing) {
            return $existing;
        }

        $pdfPath = $this->generateAndStorePdf($user);

        $report = CompletionReport::create([
            'user_id' => $user->id,
            'pdf_path' => $pdfPath,
        ]);

        if ($notifyAdmin) {
            $adminEmail = config('mail.contact_email');
            if ($adminEmail) {
                Mail::to($adminEmail)->send(new ReportReadyAdminMail($user, $report));
            }
        }

        return $report;
    }

    public function sendToUser(CompletionReport $report): void
    {
        $user = $report->user;

        Mail::to($user->email)->send(new ModuleCompletedMail(
            $user,
            MilestoneType::Module4,
            $report->pdf_path,
        ));

        $report->update(['last_sent_at' => now()]);
    }

    public function buildReportViewData(User $user): array
    {
        $partOrder = MilestoneType::Module4->moduleOrder();
        $report = $this->reportService->forUserAndPart($user, $partOrder);
        $chartData = $this->chartService->generateCharts($report);

        return [
            'user' => $user,
            'partOrder' => $partOrder,
            'module' => $report['module'],
            'report' => $report,
            'chartData' => $chartData,
            'forPdf' => false,
        ];
    }

    public function generateAndStorePdf(User $user): ?string
    {
        $relativePath = CompletionReport::pdfPathForHhId($user->hh_id);

        try {
            Storage::makeDirectory('completion-reports');

            $viewData = array_merge($this->buildReportViewData($user), ['forPdf' => true]);

            $pdf = Pdf::loadView('emails.module-completed', $viewData)
                ->setPaper('a4');

            Storage::put($relativePath, $pdf->output());

            return $relativePath;
        } catch (\Throwable $e) {
            Log::error('Failed to generate completion report PDF.', [
                'user_id' => $user->id,
                'hh_id' => $user->hh_id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
