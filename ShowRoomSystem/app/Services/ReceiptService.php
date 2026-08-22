<?php

namespace App\Services;

use App\Models\Car;
use App\Models\Purchase;
use App\Models\RentPayment;
use Illuminate\Support\Facades\File;

class ReceiptService
{
    public function createPurchaseReceipt(Purchase $purchase, Car $car): string
    {
        $purchase->loadMissing('customer');

        $details = [
            'Customer' => $purchase->customer->name ?? 'N/A',
            'Phone' => $purchase->customer->phone_number ?? 'N/A',
            'Car' => $purchase->car_name,
            'Model Year' => (string) $purchase->model_year,
            'Purchase Date' => $purchase->purchase_date->format('Y-m-d'),
            'Payment Months' => $purchase->months ? $purchase->months . ' months' : 'Completed',
            'Notes' => $purchase->notes ?: 'None',
        ];

        $amounts = [
            'Overall Price' => '$' . number_format((float) $purchase->overall_price, 2),
            'Basic Price' => '$' . number_format((float) $purchase->basic_price, 2),
            'Upfront Payment' => '$' . number_format((float) $purchase->upfront_payment, 2),
            'Remaining Balance' => '$' . number_format($this->purchaseBalance($purchase), 2),
        ];

        return $this->writePdf(
            $this->buildReceiptPdf('PURCHASE RECEIPT', $details, $amounts),
            $this->purchaseReceiptFileName($purchase)
        );
    }

    public function createRentReceipt(RentPayment $rentPayment): string
    {
        $rentPayment->loadMissing('customer', 'car.purchase', 'car.rentPayments');
        $purchase = $rentPayment->car->purchase;
        $paidBeforeThisReceipt = $rentPayment->car->rentPayments
            ->where('id', '!=', $rentPayment->id)
            ->sum('amount');
        $balanceBeforePayment = max(0, $this->purchaseBalance($purchase) - (float) $paidBeforeThisReceipt);
        $balanceAfterPayment = max(0, $balanceBeforePayment - (float) $rentPayment->amount);

        $details = [
            'Customer' => $rentPayment->customer->name ?? 'N/A',
            'Phone' => $rentPayment->customer->phone_number ?? 'N/A',
            'Car' => $rentPayment->car->name,
            'Model Year' => (string) $rentPayment->car->model_year,
            'Payment Date' => $rentPayment->payment_date->format('Y-m-d'),
            'Notes' => $rentPayment->notes ?: 'None',
        ];

        $amounts = [
            'Amount Paid' => '$' . number_format((float) $rentPayment->amount, 2),
            'Balance Before Payment' => '$' . number_format($balanceBeforePayment, 2),
            'Balance After Payment' => '$' . number_format($balanceAfterPayment, 2),
        ];

        return $this->writePdf(
            $this->buildReceiptPdf('RENT PAYMENT RECEIPT', $details, $amounts),
            $this->rentReceiptFileName($rentPayment)
        );
    }

    public function purchaseReceiptFileName(Purchase $purchase): string
    {
        return $this->receiptFileName(
            $purchase->car_name,
            (string) $purchase->model_year,
            $purchase->purchase_date->format('j_n_Y'),
            'purchase'
        );
    }

    public function rentReceiptFileName(RentPayment $rentPayment): string
    {
        $rentPayment->loadMissing('car');

        return $this->receiptFileName(
            $rentPayment->car->name,
            (string) $rentPayment->car->model_year,
            $rentPayment->payment_date->format('j_n_Y'),
            'rent'
        );
    }

    private function purchaseBalance(Purchase $purchase): float
    {
        return max(0, (float) $purchase->overall_price - (float) $purchase->upfront_payment);
    }

    private function writePdf(string $pdf, string $fileName): string
    {
        $directory = storage_path('app/private/receipts');
        File::ensureDirectoryExists($directory);

        $path = $directory . DIRECTORY_SEPARATOR . $fileName;
        file_put_contents($path, $pdf);

        return $path;
    }

    private function buildReceiptPdf(string $title, array $details, array $amounts): string
    {
        $content = '';
        $content .= $this->rect(0, 0, 612, 792, '0.98 0.98 0.97');
        $content .= $this->rect(40, 690, 532, 72, '0.10 0.12 0.15');
        $content .= $this->text(60, 735, 'ShowRoom', 'F2', 18, '1 1 1');
        $content .= $this->text(60, 711, $title, 'F2', 24, '1 1 1');
        $content .= $this->text(420, 724, now()->format('Y-m-d'), 'F2', 13, '1 1 1');

        $content .= $this->rect(40, 80, 532, 585, '1 1 1');
        $content .= $this->strokeRect(40, 80, 532, 585, '0.88 0.89 0.91');

        $content .= $this->text(60, 630, 'Transaction Details', 'F2', 15, '0.10 0.12 0.15');
        $content .= $this->line(60, 616, 552, 616, '0.88 0.89 0.91');

        $y = 590;
        $index = 0;
        foreach ($details as $label => $value) {
            $x = $index % 2 === 0 ? 60 : 315;
            if ($index > 0 && $index % 2 === 0) {
                $y -= 54;
            }

            $content .= $this->text($x, $y, strtoupper($label), 'F1', 8, '0.45 0.48 0.54');
            $content .= $this->text($x, $y - 18, (string) $value, 'F2', 11, '0.12 0.14 0.18');
            $index++;
        }

        $summaryTop = $y - 90;
        $content .= $this->text(60, $summaryTop, 'Payment Summary', 'F2', 15, '0.10 0.12 0.15');
        $content .= $this->line(60, $summaryTop - 14, 552, $summaryTop - 14, '0.88 0.89 0.91');

        $boxY = $summaryTop - 75;
        $boxIndex = 0;
        foreach ($amounts as $label => $value) {
            $x = $boxIndex % 2 === 0 ? 60 : 315;
            if ($boxIndex > 0 && $boxIndex % 2 === 0) {
                $boxY -= 82;
            }

            $content .= $this->rect($x, $boxY, 215, 58, '0.96 0.97 0.98');
            $content .= $this->strokeRect($x, $boxY, 215, 58, '0.87 0.89 0.91');
            $content .= $this->text($x + 14, $boxY + 36, strtoupper($label), 'F1', 8, '0.45 0.48 0.54');
            $content .= $this->text($x + 14, $boxY + 16, (string) $value, 'F2', 15, '0.10 0.12 0.15');
            $boxIndex++;
        }

        $content .= $this->line(60, 145, 552, 145, '0.88 0.89 0.91');
        $content .= $this->text(60, 123, 'Generated by ShowRoom loan system', 'F1', 9, '0.45 0.48 0.54');
        $content .= $this->text(402, 123, 'Thank you for your payment', 'F2', 10, '0.10 0.12 0.15');

        $objects = [
            "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
            "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n",
            "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R /F2 5 0 R >> >> /Contents 6 0 R >>\nendobj\n",
            "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n",
            "5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>\nendobj\n",
            "6 0 obj\n<< /Length " . strlen($content) . " >>\nstream\n{$content}\nendstream\nendobj\n",
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $object) {
            $offsets[] = strlen($pdf);
            $pdf .= $object;
        }

        $xref = strlen($pdf);
        $pdf .= "xref\n0 " . (count($objects) + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";

        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= "trailer\n<< /Size " . (count($objects) + 1) . " /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xref}\n%%EOF";

        return $pdf;
    }

    private function escapePdfText(string $text): string
    {
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], substr($text, 0, 80));
    }

    private function receiptFileName(string $carName, string $modelYear, string $date, string $type): string
    {
        return $this->fileSafePart($carName) . '_' . $modelYear . '_' . $date . '_' . $type . '_receipt.pdf';
    }

    private function fileSafePart(string $value): string
    {
        $value = preg_replace('/[^\pL\pN]+/u', '_', trim($value));
        $value = trim($value, '_');

        return $value ?: 'car';
    }

    private function text(float $x, float $y, string $text, string $font, int $size, string $color): string
    {
        return "{$color} rg\nBT\n/{$font} {$size} Tf\n{$x} {$y} Td\n(" . $this->escapePdfText($text) . ") Tj\nET\n";
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
}
