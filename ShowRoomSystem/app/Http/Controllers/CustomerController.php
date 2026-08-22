<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Services\GoogleDriveService;

class CustomerController extends Controller
{
    /**
     * Ensure the user is an editor.
     */
    private function authorizeEditor()
    {
        if (session('user_role') !== 'editor') {
            abort(403, 'Unauthorized. Only editors can perform this action.');
        }
    }

    /**
     * Display a listing of customers with search.
     */
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('phone_number', 'like', '%' . $search . '%');
            });
        }

        $customers = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('customers.index', compact('customers'));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create(Request $request)
    {
        $this->authorizeEditor();
        $redirectToPurchase = $request->get('redirect') === 'purchase';
        return view('customers.create', compact('redirectToPurchase'));
    }
    
    public function store(Request $request, GoogleDriveService $driveService)
    {
        $this->authorizeEditor();

        $request->validate([
            'name'         => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
        ]);

        $customer = Customer::create([
            'name'         => $request->name,
            'phone_number' => $request->phone_number,
        ]);

        $folderName = $this->sanitizeFolderName($customer->name);
        $folderId = $driveService->createFolder($folderName);

        if ($folderId) {
            $customer->folder_path = $folderId;
            $customer->save();
        } else {
            \Log::warning("Google Drive folder creation failed for customer {$customer->id}");
        }

        // Redirect back to purchase form if coming from there
        if ($request->get('redirect') === 'purchase') {
            return redirect()->route('purchases.create')
                ->with('selected_customer_id', $customer->id)
                ->with('success', 'Customer created! Now record their purchase.');
        }

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer)
    {
        $customer->load(['cars.purchase', 'cars.rentPayments']);
        return view('customers.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer)
    {
        $this->authorizeEditor();
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, Customer $customer)
    {
        $this->authorizeEditor();

        $request->validate([
            'name'          => 'required|string|max:255',
            'phone_number'  => 'nullable|string|max:20',
            'folder_path'   => 'nullable|string|max:255',
        ]);

        $customer->update($request->only('name', 'phone_number', 'folder_path'));

        return redirect()->route('customers.show', $customer)
                         ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified customer.
     */
    public function destroy(Customer $customer)
    {
        $this->authorizeEditor();
        $customer->delete();

        return redirect()->route('customers.index')
                         ->with('success', 'Customer deleted successfully.');
    }
   
    private function sanitizeFolderName($name)
    {
        // Remove characters that are problematic in Drive folder names
        return preg_replace('/[^\w\s-]/', '', $name);
    }
}
