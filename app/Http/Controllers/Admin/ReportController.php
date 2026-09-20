<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $reports = Report::query()
            ->with(['submitter', 'candidateAdvocate.user', 'lawFirm'])
            ->orderByDesc('created_at')
            ->get();

        return view('admin.reports', [
            'reports' => $reports,
            'unread' => $reports->whereNull('read_at')->count(),
        ]);
    }

    public function show(Report $report): View
    {
        $report->load(['submitter', 'candidateAdvocate.user', 'lawFirm']);
        if ($report->read_at === null) {
            $report->update(['read_at' => now()]);
        }

        return view('admin.report-show', ['report' => $report]);
    }
}
