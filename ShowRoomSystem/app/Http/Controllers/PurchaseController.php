<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Purchase;
use App\Models\Car;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    /**
     * Display a listing of purchases.
     */
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

    /**
     * Show the form for creating a new purchase.
     */
    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        return view('purchases.create', compact('customers'));
    }

    /**
     * Store a newly created purchase in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'     => 'required|exists:customers,id',
            'car_name'        => 'required|string|max:255',
            'model_year'      => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'overall_price'   => 'required|numeric|min:0',
            'basic_price'     => 'required|numeric|min:0',
            'upfront_payment' => 'required|numeric|min:0|lte:overall_price',
            'purchase_date'   => 'nullable|date',
        ]);

        // Default purchase date to now if not provided
        $validated['purchase_date'] = $validated['purchase_date'] ?? now();

        DB::transaction(function () use ($validated) {
            // Create the purchase record
            $purchase = Purchase::create($validated);

            // Automatically create a car record linked to this purchase
            Car::create([
                'purchase_id' => $purchase->id,
                'customer_id' => $validated['customer_id'],
                'name'        => $validated['car_name'],
                'model_year'  => $validated['model_year'],
                'status'      => 'available', // default status, adjust as needed
            ]);
        });

        return redirect()->route('purchases.index')
            ->with('success', 'Purchase recorded successfully.');
    }
}