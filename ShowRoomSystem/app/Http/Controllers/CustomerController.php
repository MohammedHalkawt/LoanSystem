<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;

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
    public function create()
    {
        $this->authorizeEditor();
        return view('customers.create');
    }

    /**
     * Store a newly created customer.
     */
    public function store(Request $request)
    {
        $this->authorizeEditor();

        $request->validate([
            'name'          => 'required|string|max:255',
            'phone_number'  => 'nullable|string|max:20',
            'folder_path'   => 'nullable|string|max:255',
        ]);

        Customer::create($request->only('name', 'phone_number', 'folder_path'));

        return redirect()->route('customers.index')
                         ->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer)
    {
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
}