<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Customer;
use App\Models\RentPayment;
use App\Services\GoogleDriveService;
use App\Services\ReceiptService;
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

        $rents = RentPayment::with(['customer', 'car.purchase', 'car.rentPayments'])
            ->when($search, function ($query, $search) {
                $query->whereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhereHas('car', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->latest('payment_date')
            ->latest('id')
            ->paginate(15);

        $rents->getCollection()->transform(function ($rent) {
            $purchase = $rent->car?->purchase;
            $startingBalance = $purchase
                ? max(0, (float) $purchase->overall_price - (float) $purchase->upfront_payment)
                : 0;

            $paidThroughThisPayment = $rent->car?->rentPayments
                ->filter(function ($payment) use ($rent) {
                    if ($payment->payment_date->lt($rent->payment_date)) {
                        return true;
                    }

                    return $payment->payment_date->isSameDay($rent->payment_date)
                        && $payment->id <= $rent->id;
                })
                ->sum('amount') ?? 0;

            $rent->remaining_after_payment = max(0, $startingBalance - (float) $paidThroughThisPayment);

            return $rent;
        });

        return view('rents.index', compact('rents'));
    }

    public function create()
    {
        $this->authorizeEditor();

        $customers = Customer::with(['cars.purchase', 'cars.rentPayments'])
            ->orderBy('name')
            ->get();

        $customerOptions = $customers->map(function ($customer) {
            return [
                'id' => $customer->id,
                'label' => $customer->name,
                'phone' => $customer->phone_number ?: 'No phone',
            ];
        })->values();

        $cars = $customers->flatMap(function ($customer) {
            return $customer->cars->map(function ($car) use ($customer) {
                $purchase = $car->purchase;
                $totalMonths = (int) ($purchase->months ?? 0);
                $paidAmount = (float) $car->rentPayments->sum('amount');
                $startingBalance = max(0, (float) $purchase->overall_price - (float) $purchase->upfront_payment);
                $remainingBalance = max(0, $startingBalance - $paidAmount);
                $remainingMonths = $totalMonths;
                $monthlyAmount = $remainingMonths > 0 ? $remainingBalance / $remainingMonths : $remainingBalance;

                if ($remainingBalance <= 0 || $remainingMonths <= 0) {
                    return null;
                }

                return [
                    'id' => $car->id,
                    'customer_id' => $customer->id,
                    'label' => $car->name . ' (' . $car->model_year . ') - remaining $' . number_format($remainingBalance, 2),
                    'name' => $car->name,
                    'model_year' => $car->model_year,
                    'remaining_balance' => round($remainingBalance, 2),
                    'monthly_amount' => round($monthlyAmount, 2),
                    'remaining_months' => $remainingMonths,
                ];
            })->filter();
        })->values();

        return view('rents.create', compact('customers', 'customerOptions', 'cars'));
    }

    public function store(Request $request, GoogleDriveService $driveService, ReceiptService $receiptService)
    {
        $this->authorizeEditor();

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'car_id' => 'required|exists:cars,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'nullable|date',
            'notes' => 'nullable|string|max:5000',
        ]);

        $car = Car::with(['purchase', 'customer', 'rentPayments'])->findOrFail($validated['car_id']);

        if ((int) $car->customer_id !== (int) $validated['customer_id']) {
            return back()
                ->withInput()
                ->withErrors(['car_id' => 'The selected car does not belong to the selected customer.']);
        }

        $rentPlan = $this->calculateRentPlan($car, (float) $validated['amount']);

        if ($rentPlan['remaining_balance'] <= 0 || $rentPlan['remaining_months'] <= 0) {
            return back()
                ->withInput()
                ->withErrors(['car_id' => 'This car is already fully paid. No more rent can be recorded.']);
        }

        if ((float) $validated['amount'] > $rentPlan['remaining_balance'] + 0.009) {
            return back()
                ->withInput()
                ->withErrors(['amount' => 'This payment is more than the remaining balance. Remaining balance is $' . number_format($rentPlan['remaining_balance'], 2) . '.']);
        }

        $receiptUploaded = false;

        DB::transaction(function () use ($validated, $rentPlan, $car, $driveService, $receiptService, &$receiptUploaded) {
            $rentPayment = RentPayment::create([
                'customer_id' => $validated['customer_id'],
                'car_id' => $validated['car_id'],
                'amount' => $validated['amount'],
                'covered_month_from' => $rentPlan['covered_month_from'],
                'covered_month_to' => $rentPlan['covered_month_to'],
                'months_count' => $rentPlan['months_count'],
                'payment_date' => $validated['payment_date'] ?? now(),
                'notes' => $validated['notes'] ?? null,
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
                    $receiptService->rentReceiptFileName($rentPayment),
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

    private function calculateRentPlan(Car $car, float $amount): array
    {
        $purchase = $car->purchase;
        $totalMonths = (int) ($purchase->months ?? 0);
        $paidAmount = (float) $car->rentPayments->sum('amount');
        $startingBalance = max(0, (float) $purchase->overall_price - (float) $purchase->upfront_payment);
        $remainingBalance = max(0, $startingBalance - $paidAmount);
        $remainingMonths = $totalMonths;
        $monthlyAmount = $remainingMonths > 0 ? $remainingBalance / $remainingMonths : 0;

        return [
            'remaining_balance' => $remainingBalance,
            'remaining_months' => $remainingMonths,
            'monthly_amount' => $monthlyAmount,
            'months_count' => 0,
            'covered_month_from' => null,
            'covered_month_to' => null,
        ];
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
