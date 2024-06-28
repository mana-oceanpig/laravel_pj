<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Services\ReportGenerationService;
use App\Exports\ReportExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ConversationReportController extends Controller
{
    protected $reportGenerationService;

    public function __construct(ReportGenerationService $reportGenerationService)
    {
        $this->reportGenerationService = $reportGenerationService;
    }

    public function show(ConversationReport $report)
    {
        $this->authorize('view', $report);
        return view('report.show', compact('report'));
    }

    public function download(ConversationReport $report)
    {
        $this->authorize('view', $report);
        return Excel::download(new ReportExport($report), 'mental_health_report.pdf');
    }
}