<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Customer;
use App\Models\RentPayment;
use App\Services\GoogleDriveService;
use App\Services\ReceiptService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RentController extends Controller
{
    private function authorizeEditor()
    {
        if (session('user_role') !== 'editor') {
            abort(403, 'Unauthorized. Only editors can perform this action.');
        }
    }

    public function index(Request $request)
    {
        $search = $request->get('search');

        $rents = RentPayment::with(['customer', 'car.purchase'])
            ->when($search, function ($query, $search) {
                $query->whereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhereHas('car', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest('payment_date')
            ->paginate(15);

        return view('rents.index', compact('rents'));
    }

    public function create()
    {
        $this->authorizeEditor();

        $customers = Customer::with(['cars.purchase', 'cars.rentPayments'])
            ->orderBy('name')
            ->get();

        $cars = $customers->flatMap(function ($customer) {
            return $customer->cars->map(function ($car) use ($customer) {
                $purchase = $car->purchase;
                $totalMonths = (int) ($purchase->months ?? 0);
                $paidAmount = (float) $car->rentPayments->sum('amount');
                $paidMonths = (int) $car->rentPayments->sum('months_count');
                $startingBalance = max(0, (float) $purchase->overall_price - (float) $purchase->upfront_payment);
                $remainingBalance = max(0, $startingBalance - $paidAmount);
                $remainingMonths = $totalMonths > 0 ? max(0, $totalMonths - $paidMonths) : 0;

                return [
                    'id' => $car->id,
                    'customer_id' => $customer->id,
                    'label' => $car->name . ' (' . $car->model_year . ') - remaining $' . number_format($remainingBalance, 2),
                    'name' => $car->name,
                    'model_year' => $car->model_year,
                    'remaining_balance' => round($remainingBalance, 2),
                    'monthly_amount' => $totalMonths > 0 ? round($startingBalance / $totalMonths, 2) : $remainingBalance,
                    'remaining_months' => $remainingMonths,
                ];
            });
        })->values();

        return view('rents.create', compact('customers', 'cars'));
    }

    public function store(Request $request, GoogleDriveService $driveService, ReceiptService $receiptService)
    {
        $this->authorizeEditor();

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'car_id' => 'required|exists:cars,id',
            'amount' => 'required|numeric|min:0.01',
            'covered_month_from' => 'required|date_format:Y-m',
            'covered_month_to' => 'required|date_format:Y-m|after_or_equal:covered_month_from',
            'payment_date' => 'nullable|date',
        ]);

        $car = Car::with('purchase', 'customer')->findOrFail($validated['car_id']);

        if ((int) $car->customer_id !== (int) $validated['customer_id']) {
            return back()
                ->withInput()
                ->withErrors(['car_id' => 'The selected car does not belong to the selected customer.']);
        }

        $monthsCount = $this->countCoveredMonths(
            $validated['covered_month_from'],
            $validated['covered_month_to']
        );

        $receiptUploaded = false;

        DB::transaction(function () use ($validated, $monthsCount, $car, $driveService, $receiptService, &$receiptUploaded) {
            $rentPayment = RentPayment::create([
                'customer_id' => $validated['customer_id'],
                'car_id' => $validated['car_id'],
                'amount' => $validated['amount'],
                'covered_month_from' => $validated['covered_month_from'],
                'covered_month_to' => $validated['covered_month_to'],
                'months_count' => $monthsCount,
                'payment_date' => $validated['payment_date'] ?? now(),
            ]);

            $carFolderId = $car->drive_folder_id;

            if (!$carFolderId) {
                $folderName = $this->buildCarFolderName($car);
                $carFolderId = $driveService->findOrCreateFolder($folderName, $car->customer->folder_path);

                if ($carFolderId) {
                    $car->update(['drive_folder_id' => $carFolderId]);
                }
            }

            if ($carFolderId) {
                $receiptPath = $receiptService->createRentReceipt($rentPayment);
                $fileId = $driveService->uploadFile(
                    $receiptPath,
                    'rent-receipt-' . $rentPayment->id . '.pdf',
                    $carFolderId
                );

                if ($fileId) {
                    $rentPayment->update([
                        'receipt_path' => $receiptPath,
                        'receipt_drive_file_id' => $fileId,
                    ]);
                    $receiptUploaded = true;
                }
            }
        });

        return redirect()->route('rents.index')
            ->with('success', $receiptUploaded
                ? 'Rent payment recorded successfully and receipt uploaded to Google Drive.'
                : 'Rent payment recorded successfully. Receipt upload was skipped or failed; check the customer Google Drive folder.');
    }

    public function show(RentPayment $rent)
    {
        $rent->load(['customer', 'car.purchase']);
        return view('rents.show', compact('rent'));
    }

    private function countCoveredMonths(string $from, string $to): int
    {
        $fromDate = Carbon::createFromFormat('Y-m', $from)->startOfMonth();
        $toDate = Carbon::createFromFormat('Y-m', $to)->startOfMonth();

        return (int) $fromDate->diffInMonths($toDate) + 1;
    }

    private function sanitizeFolderName($name)
    {
        return trim(preg_replace('/[^\w\s-]/', '', $name));
    }

    private function buildCarFolderName(Car $car)
    {
        $purchase = $car->purchase;

        if (!$purchase) {
            return $this->folderSafePart($car->name) . '_' . $car->model_year;
        }

        $datePart = $purchase->purchase_date->format('j_n_Y');
        $baseName = $this->folderSafePart($car->name) . '_' . $car->model_year . '_' . $datePart;

        $sameDayCars = Car::where('customer_id', $car->customer_id)
            ->where('name', $car->name)
            ->where('model_year', $car->model_year)
            ->whereHas('purchase', function ($query) use ($purchase) {
                $query->whereDate('purchase_date', $purchase->purchase_date->toDateString());
            })
            ->orderBy('id')
            ->pluck('id')
            ->values();

        $position = $sameDayCars->search($car->id);

        if ($position === false || $position === 0) {
            return $baseName;
        }

        return $baseName . '_' . ($position + 1);
    }

    private function folderSafePart($value)
    {
        $value = preg_replace('/[^\pL\pN]+/u', '_', trim($value));
        $value = trim($value, '_');

        return $value ?: 'Car';
    }
}
