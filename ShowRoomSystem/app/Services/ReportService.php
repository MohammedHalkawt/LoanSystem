<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\RentPayment;
use Carbon\Carbon;

class ReportService
{
    public function monthlyReport(Carbon $startDate, Carbon $endDate): string
    {
        $purchases = Purchase::with(['customer', 'car.rentPayments'])
            ->whereBetween('purchase_date', [$startDate, $endDate])
            ->orderBy('purchase_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $rents = RentPayment::with(['customer', 'car.purchase', 'car.rentPayments'])
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->orderBy('payment_date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $allPurchasesThroughEnd = Purchase::with('car.rentPayments')
            ->whereDate('purchase_date', '<=', $endDate)
            ->get();

        $totalOutstanding = $allPurchasesThroughEnd->sum(function ($purchase) use ($endDate) {
            $startingBalance = max(0, (float) $purchase->overall_price - (float) $purchase->upfront_payment);
            $paid = $purchase->car?->rentPayments
                ->filter(fn ($payment) => $payment->payment_date->lte($endDate))
                ->sum('amount') ?? 0;

            return max(0, $startingBalance - (float) $paid);
        });

        $summary = [
            'Purchase Count' => (string) $purchases->count(),
            'Purchase Value' => '$' . number_format((float) $purchases->sum('overall_price'), 2),
            'Rent Return' => '$' . number_format((float) $rents->sum('amount'), 2),
            'Outstanding Balance' => '$' . number_format((float) $totalOutstanding, 2),
        ];

        $purchaseRows = $purchases->map(fn ($purchase) => [
            $purchase->purchase_date->format('Y-m-d'),
            $purchase->customer->name ?? 'N/A',
            $purchase->car_name . ' ' . $purchase->model_year,
            '$' . number_format((float) $purchase->overall_price, 2),
            '$' . number_format(max(0, (float) $purchase->overall_price - (float) $purchase->upfront_payment), 2),
        ])->all();

        $rentRows = $rents->map(fn ($rent) => [
            $rent->payment_date->format('Y-m-d'),
            $rent->customer->name ?? 'N/A',
            ($rent->car->name ?? 'N/A') . ' ' . ($rent->car->model_year ?? ''),
            '$' . number_format((float) $rent->amount, 2),
            '$' . number_format($this->remainingAfterRent($rent), 2),
        ])->all();

        return $this->buildPdf(
            'Monthly Report',
            $startDate->format('M d, Y') . ' - ' . $endDate->format('M d, Y'),
            $summary,
            [
                ['title' => 'Purchases', 'headers' => ['Date', 'Customer', 'Car', 'Total', 'Balance'], 'rows' => $purchaseRows],
                ['title' => 'Rent Payments', 'headers' => ['Date', 'Customer', 'Car', 'Paid', 'Remaining'], 'rows' => $rentRows],
            ]
        );
    }

    private function remainingAfterRent(RentPayment $rent): float
    {
        $purchase = $rent->car?->purchase;

        if (!$purchase) {
            return 0;
        }

        $startingBalance = max(0, (float) $purchase->overall_price - (float) $purchase->upfront_payment);
        $paid = $rent->car->rentPayments
            ->filter(function ($payment) use ($rent) {
                if ($payment->payment_date->lt($rent->payment_date)) {
                    return true;
                }

                return $payment->payment_date->isSameDay($rent->payment_date) && $payment->id <= $rent->id;
            })
            ->sum('amount');

        return max(0, $startingBalance - (float) $paid);
    }

    private function buildPdf(string $title, string $subtitle, array $summary, array $sections): string
    {
        $pages = [];
        $content = $this->newPage($title, $subtitle);
        $y = 640;

        $x = 54;
        foreach ($summary as $label => $value) {
            $content .= $this->rect($x, $y, 118, 58, '1 1 1');
            $content .= $this->strokeRect($x, $y, 118, 58, '0.86 0.87 0.89');
            $content .= $this->text($x + 12, $y + 35, strtoupper($label), 'F1', 7, '0.43 0.45 0.50');
            $content .= $this->text($x + 12, $y + 15, $value, 'F2', 12, '0.10 0.10 0.12');
            $x += 126;
        }

        $y -= 55;

        foreach ($sections as $section) {
            if ($y < 170) {
                $pages[] = $this->finishPage($content);
                $content = $this->newPage($title, $subtitle);
                $y = 650;
            }

            $content .= $this->text(54, $y, $section['title'], 'F2', 15, '0.10 0.10 0.12');
            $y -= 24;
            $content .= $this->tableHeader($section['headers'], $y);
            $y -= 24;

            if (empty($section['rows'])) {
                $content .= $this->text(64, $y, 'No records in this period.', 'F1', 10, '0.43 0.45 0.50');
                $y -= 34;
                continue;
            }

            foreach ($section['rows'] as $row) {
                if ($y < 80) {
                    $pages[] = $this->finishPage($content);
                    $content = $this->newPage($title, $subtitle);
                    $y = 650;
                    $content .= $this->tableHeader($section['headers'], $y);
                    $y -= 24;
                }

                $content .= $this->tableRow($row, $y);
                $y -= 24;
            }

            $y -= 22;
        }

        $pages[] = $this->finishPage($content);

        return $this->compilePdf($pages);
    }

    private function newPage(string $title, string $subtitle): string
    {
        $content = '';
        $content .= $this->rect(0, 0, 612, 792, '0.96 0.96 0.95');
        $content .= $this->rect(40, 696, 532, 66, '0.10 0.10 0.12');
        $content .= $this->text(60, 735, 'ShowRoom', 'F2', 17, '1 1 1');
        $content .= $this->text(60, 713, $title, 'F2', 23, '1 1 1');
        $content .= $this->text(398, 724, $subtitle, 'F1', 10, '0.86 0.86 0.88');

        return $content;
    }

    private function finishPage(string $content): string
    {
        $content .= $this->line(54, 54, 558, 54, '0.84 0.85 0.87');
        $content .= $this->text(54, 34, 'Generated by ShowRoom loan system', 'F1', 8, '0.43 0.45 0.50', 40);

        return $content;
    }

    private function tableHeader(array $headers, float $y): string
    {
        $content = $this->rect(54, $y - 6, 504, 22, '0.91 0.92 0.94');
        $widths = [72, 126, 130, 88, 88];
        $x = 64;

        foreach ($headers as $index => $header) {
            $content .= $this->text($x, $y, strtoupper($header), 'F2', 7, '0.28 0.29 0.33');
            $x += $widths[$index];
        }

        return $content;
    }

    private function tableRow(array $row, float $y): string
    {
        $content = $this->line(54, $y - 8, 558, $y - 8, '0.89 0.90 0.92');
        $widths = [72, 126, 130, 88, 88];
        $x = 64;

        foreach ($row as $index => $value) {
            $content .= $this->text($x, $y, (string) $value, 'F1', 8, '0.10 0.10 0.12', $index === 2 ? 22 : 18);
            $x += $widths[$index];
        }

        return $content;
    }

    private function compilePdf(array $pages): string
    {
        $fontObject = 3 + (count($pages) * 2);
        $boldFontObject = $fontObject + 1;
        $objects = [
            "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
            "2 0 obj\n<< /Type /Pages /Kids [] /Count " . count($pages) . " >>\nendobj\n",
        ];

        $pageKids = [];
        $objectNumber = 3;

        foreach ($pages as $page) {
            $pageObject = $objectNumber++;
            $contentObject = $objectNumber++;
            $pageKids[] = "{$pageObject} 0 R";
            $objects[] = "{$pageObject} 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 {$fontObject} 0 R /F2 {$boldFontObject} 0 R >> >> /Contents {$contentObject} 0 R >>\nendobj\n";
            $objects[] = "{$contentObject} 0 obj\n<< /Length " . strlen($page) . " >>\nstream\n{$page}\nendstream\nendobj\n";
        }

        $objects[1] = "2 0 obj\n<< /Type /Pages /Kids [" . implode(' ', $pageKids) . "] /Count " . count($pages) . " >>\nendobj\n";
        $objects[] = "{$fontObject} 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";
        $objects[] = "{$boldFontObject} 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>\nendobj\n";

        return $this->assembleObjects($objects);
    }

    private function assembleObjects(array $objects): string
    {
        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\nstartxref\n{$xref}\n%%EOF";

        return $pdf;
    }

    private function text(float $x, float $y, string $text, string $font, int $size, string $color, int $limit = 28): string
    {
        return "{$color} rg\nBT\n/{$font} {$size} Tf\n{$x} {$y} Td\n(" . $this->escape($text, $limit) . ") Tj\nET\n";
    }

    private function rect(float $x, float $y, float $width, float $height, string $color): string
    {
        return "{$color} rg\n{$x} {$y} {$width} {$height} re f\n";
    }

    private function strokeRect(float $x, float $y, float $width, float $height, string $color): string
    {
        return "{$color} RG\n{$x} {$y} {$width} {$height} re S\n";
    }

    private function line(float $x1, float $y1, float $x2, float $y2, string $color): string
    {
        return "{$color} RG\n{$x1} {$y1} m\n{$x2} {$y2} l\nS\n";
    }

    private function escape(string $text, int $limit): string
    {
        $text = substr($text, 0, $limit);

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}
