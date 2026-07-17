<?php

namespace App\Mail;

use App\Enums\MilestoneType;
use App\Models\User;
use App\Services\ModuleChartService;
use App\Services\ModuleEmailReportService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ModuleCompletedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public MilestoneType $type
    ) {}

    public function build()
    {
        $partOrder = $this->type->moduleOrder();
        $reportService = app(ModuleEmailReportService::class);
        $chartService = app(ModuleChartService::class);

        $report = $reportService->forUserAndPart($this->user, $partOrder);
        $chartData = $chartService->generateCharts($report);

        return $this->subject("Your ".config('app.name')." Journey Report - Part {$partOrder} ({$report['module']->flowerColorName()} Flower)")
            ->view('emails.module-completed')
            ->with([
                'user' => $this->user,
                'partOrder' => $partOrder,
                'module' => $report['module'],
                'report' => $report,
                'chartData' => $chartData,
            ]);
    }
}
