<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Purchase;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');

        $purchases = Purchase::with('customer')
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
        $customers = Customer::orderBy('name')->get();
        return view('purchases.create', compact('customers'));
    }

    public function store(Request $request)
    {
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

        DB::transaction(function () use ($validated) {
            $purchase = Purchase::create($validated);

            Car::create([
                'purchase_id' => $purchase->id,
                'customer_id' => $validated['customer_id'],
                'name'        => $validated['car_name'],
                'model_year'  => $validated['model_year'],
                'status'      => 'available',
            ]);
        });

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase recorded successfully.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load('customer');
        return view('purchases.show', compact('purchase'));
    }

    public function edit(Purchase $purchase)
    {
        $customers = Customer::orderBy('name')->get();
        return view('purchases.edit', compact('purchase', 'customers'));
    }

    public function update(Request $request, Purchase $purchase)
    {
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
        $purchase->delete();
        return redirect()->route('purchases.index')
            ->with('success', 'Purchase deleted successfully.');
    }
}