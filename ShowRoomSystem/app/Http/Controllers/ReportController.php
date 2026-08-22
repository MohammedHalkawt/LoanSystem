<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function monthly(Request $request, ReportService $reportService)
    {
        $validated = $request->validate([
            'start_month' => 'required|date_format:Y-m',
            'end_month' => 'nullable|date_format:Y-m|after_or_equal:start_month',
        ]);

        $startDate = Carbon::createFromFormat('Y-m', $validated['start_month'])->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $validated['end_month'] ?? $validated['start_month'])->endOfMonth();
        $pdf = $reportService->monthlyReport($startDate, $endDate);
        $fileName = 'showroom_report_' . $startDate->format('Y_m') . '_' . $endDate->format('Y_m') . '.pdf';

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $fileName . '"',
        ]);
    }
}
