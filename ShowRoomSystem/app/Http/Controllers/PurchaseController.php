<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Purchase;
use App\Models\Car;
use App\Services\GoogleDriveService;
use App\Services\ReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
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

        $purchases = Purchase::with(['customer', 'car'])
            ->when($search, function ($query, $search) {
                return $query->whereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhere('car_name', 'like', "%{$search}%");
            })
            ->orderBy('purchase_date', 'desc')
            ->paginate(15);

        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $this->authorizeEditor();
        $customers = Customer::orderBy('name')->get();
        return view('purchases.create', compact('customers'));
    }

    public function store(Request $request, GoogleDriveService $driveService, ReceiptService $receiptService)
    {
        $this->authorizeEditor();

        $validated = $request->validate([
            'customer_id'     => 'required|exists:customers,id',
            'car_name'        => 'required|string|max:255',
            'model_year'      => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'overall_price'   => 'required|numeric|min:0',
            'basic_price'     => 'required|numeric|min:0',
            'upfront_payment' => 'required|numeric|min:0|lte:overall_price',
            'months'          => 'nullable|integer|min:0',
            'purchase_date'   => 'nullable|date',
        ]);
        $validated['months'] = $request->filled('months') ? (int) $request->months : null;
        $validated['purchase_date'] = $validated['purchase_date'] ?? now();

        $receiptUploaded = false;

        DB::transaction(function () use ($validated, $driveService, $receiptService, &$receiptUploaded) {
            $purchase = Purchase::create($validated);

            $car = Car::create([
                'purchase_id' => $purchase->id,
                'customer_id' => $validated['customer_id'],
                'name'        => $validated['car_name'],
                'model_year'  => $validated['model_year'],
                'status'      => 'available',
            ]);

            $purchase->load('customer');
            $customerFolderId = $purchase->customer->folder_path;

            if (!$customerFolderId) {
                $customerFolderId = $driveService->createFolder($this->sanitizeFolderName($purchase->customer->name));

                if ($customerFolderId) {
                    $purchase->customer->update(['folder_path' => $customerFolderId]);
                }
            }

            $folderName = $this->buildCarFolderName($car, $purchase);
            $folderId = $driveService->findOrCreateFolder($folderName, $customerFolderId);

            if ($folderId) {
                $car->update(['drive_folder_id' => $folderId]);

                $receiptPath = $receiptService->createPurchaseReceipt($purchase, $car->fresh());
                $fileId = $driveService->uploadFile(
                    $receiptPath,
                    'purchase-receipt-' . $purchase->id . '.pdf',
                    $folderId
                );

                if ($fileId) {
                    $car->update(['purchase_receipt_file_id' => $fileId]);
                    $receiptUploaded = true;
                }
            }
        });

        return redirect()->route('purchases.index')
            ->with('success', $receiptUploaded
                ? 'Purchase recorded successfully and receipt uploaded to Google Drive.'
                : 'Purchase recorded successfully. Receipt upload was skipped or failed; check the customer Google Drive folder.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['customer', 'car']);
        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        $this->authorizeEditor();
        $customers = Customer::orderBy('name')->get();
        return view('purchases.edit', compact('purchase', 'customers'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        $this->authorizeEditor();

        $validated = $request->validate([
            'customer_id'     => 'required|exists:customers,id',
            'car_name'        => 'required|string|max:255',
            'model_year'      => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'overall_price'   => 'required|numeric|min:0',
            'basic_price'     => 'required|numeric|min:0',
            'upfront_payment' => 'required|numeric|min:0|lte:overall_price',
            'months'          => 'nullable|integer|min:0',
            'purchase_date'   => 'nullable|date',
        ]);
        $validated['months'] = $request->filled('months') ? (int) $request->months : null;
        $validated['purchase_date'] = $validated['purchase_date'] ?? $purchase->purchase_date;

        $purchase->update($validated);

        // Sync the car record if it exists
        if ($purchase->car) {
            $purchase->car->update([
                'customer_id' => $validated['customer_id'],
                'name'        => $validated['car_name'],
                'model_year'  => $validated['model_year'],
            ]);
        }

        return redirect()->route('purchases.show', $purchase)
            ->with('success', 'Purchase updated successfully.');
    }

    public function destroy(Purchase $purchase)
    {
        $this->authorizeEditor();

        $purchase->delete();
        return redirect()->route('purchases.index')
            ->with('success', 'Purchase deleted successfully.');
    }

    private function sanitizeFolderName($name)
    {
        return trim(preg_replace('/[^\w\s-]/', '', $name));
    }

    private function buildCarFolderName(Car $car, Purchase $purchase)
    {
        $datePart = $purchase->purchase_date->format('j_n_Y');
        $baseName = $this->folderSafePart($car->name) . '_' . $car->model_year . '_' . $datePart;

        $sameDayCarCount = Car::where('customer_id', $car->customer_id)
            ->where('name', $car->name)
            ->where('model_year', $car->model_year)
            ->whereHas('purchase', function ($query) use ($purchase) {
                $query->whereDate('purchase_date', $purchase->purchase_date->toDateString());
            })
            ->count();

        if ($sameDayCarCount <= 1) {
            return $baseName;
        }

        return $baseName . '_' . $sameDayCarCount;
    }

    private function folderSafePart($value)
    {
        $value = preg_replace('/[^\pL\pN]+/u', '_', trim($value));
        $value = trim($value, '_');

        return $value ?: 'Car';
    }
}
