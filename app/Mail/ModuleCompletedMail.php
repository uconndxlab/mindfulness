<?php

namespace App\Mail;

use App\Enums\MilestoneType;
use App\Models\User;
use App\Services\ModuleChartService;
use App\Services\ModuleEmailReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ModuleCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public MilestoneType $type,
        public ?string $pdfPath = null,
    ) {}

    public function build()
    {
        $partOrder = $this->type->moduleOrder();
        $reportService = app(ModuleEmailReportService::class);
        $chartService = app(ModuleChartService::class);

        $report = $reportService->forUserAndPart($this->user, $partOrder);
        $chartData = $chartService->generateCharts($report);

        $mail = $this->subject("Your ".config('app.name')." Journey Report")
            ->view('emails.module-completed')
            ->with([
                'user' => $this->user,
                'partOrder' => $partOrder,
                'module' => $report['module'],
                'report' => $report,
                'chartData' => $chartData,
            ]);

        if ($this->pdfPath && Storage::exists($this->pdfPath)) {
            $mail->attach(Storage::path($this->pdfPath), [
                'as' => 'journey-report-part-' . $partOrder . '.pdf',
                'mime' => 'application/pdf',
            ]);
        }

        return $mail;
    }
}
