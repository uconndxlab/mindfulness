<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompletionReport;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

class CompletionReportController extends Controller
{
    public function index()
    {
        return view('admin.completion-reports');
    }

    public function showPdf(string $hhId)
    {
        $report = $this->findReportByHhId($hhId);

        if (!$report->pdf_path || !Storage::exists($report->pdf_path)) {
            abort(404, 'Report PDF not found.');
        }

        return response()->file(Storage::path($report->pdf_path), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="completion-report-' . $hhId . '.pdf"',
        ]);
    }

    public function download(string $hhId)
    {
        $report = $this->findReportByHhId($hhId);

        if (!$report->pdf_path || !Storage::exists($report->pdf_path)) {
            abort(404, 'Report PDF not found.');
        }

        return Storage::download($report->pdf_path, 'completion-report-' . $hhId . '.pdf', [
            'Content-Type' => 'application/pdf',
        ]);
    }

    private function findReportByHhId(string $hhId): CompletionReport
    {
        $user = User::where('hh_id', $hhId)->firstOrFail();

        return CompletionReport::where('user_id', $user->id)->firstOrFail();
    }
}
