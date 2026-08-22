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

        $lines = [
            'PURCHASE RECEIPT',
            'Receipt No: PURCHASE-' . $purchase->id,
            'Date: ' . $purchase->purchase_date->format('Y-m-d'),
            '',
            'Customer: ' . ($purchase->customer->name ?? 'N/A'),
            'Phone: ' . ($purchase->customer->phone_number ?? 'N/A'),
            'Car: ' . $purchase->car_name,
            'Model Year: ' . $purchase->model_year,
            '',
            'Overall Price: $' . number_format((float) $purchase->overall_price, 2),
            'Basic Price: $' . number_format((float) $purchase->basic_price, 2),
            'Upfront Payment: $' . number_format((float) $purchase->upfront_payment, 2),
            'Remaining Balance: $' . number_format($this->purchaseBalance($purchase), 2),
            'Payment Months: ' . ($purchase->months ?: 'Completed'),
            '',
            'Car Folder: ' . ($car->drive_folder_id ? 'Created in Google Drive' : 'Not available'),
        ];

        return $this->writePdf($lines, 'purchase-' . $purchase->id . '.pdf');
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

        $lines = [
            'RENT PAYMENT RECEIPT',
            'Receipt No: RENT-' . $rentPayment->id,
            'Payment Date: ' . $rentPayment->payment_date->format('Y-m-d'),
            '',
            'Customer: ' . ($rentPayment->customer->name ?? 'N/A'),
            'Phone: ' . ($rentPayment->customer->phone_number ?? 'N/A'),
            'Car: ' . $rentPayment->car->name,
            'Model Year: ' . $rentPayment->car->model_year,
            '',
            'Covered Month From: ' . ($rentPayment->covered_month_from ?? 'N/A'),
            'Covered Month To: ' . ($rentPayment->covered_month_to ?? 'N/A'),
            'Months Covered: ' . $rentPayment->months_count,
            'Amount Paid: $' . number_format((float) $rentPayment->amount, 2),
            '',
            'Balance Before This Payment: $' . number_format($balanceBeforePayment, 2),
            'Balance After This Payment: $' . number_format($balanceAfterPayment, 2),
        ];

        return $this->writePdf($lines, 'rent-' . $rentPayment->id . '.pdf');
    }

    private function purchaseBalance(Purchase $purchase): float
    {
        return max(0, (float) $purchase->overall_price - (float) $purchase->upfront_payment);
    }

    private function writePdf(array $lines, string $fileName): string
    {
        $directory = storage_path('app/private/receipts');
        File::ensureDirectoryExists($directory);

        $path = $directory . DIRECTORY_SEPARATOR . $fileName;
        file_put_contents($path, $this->buildSimplePdf($lines));

        return $path;
    }

    private function buildSimplePdf(array $lines): string
    {
        $content = "BT\n/F1 12 Tf\n50 780 Td\n";

        foreach ($lines as $index => $line) {
            if ($index > 0) {
                $content .= "0 -20 Td\n";
            }

            $content .= '(' . $this->escapePdfText($line) . ") Tj\n";
        }

        $content .= "ET";

        $objects = [
            "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n",
            "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n",
            "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n",
            "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n",
            "5 0 obj\n<< /Length " . strlen($content) . " >>\nstream\n{$content}\nendstream\nendobj\n",
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
        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $text);
    }
}
