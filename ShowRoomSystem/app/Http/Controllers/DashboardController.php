<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Purchase;
use App\Models\RentPayment;

class DashboardController extends Controller
{
    public function index()
    {
        $totalCustomers    = Customer::count();
        $totalPurchases    = Purchase::count();
        $totalRevenue      = Purchase::sum('overall_price') ?? 0;
        $totalRentPayments = RentPayment::sum('amount') ?? 0; // Ensure 0 if no records

        return view('dashboard', compact(
            'totalCustomers',
            'totalPurchases',
            'totalRevenue',
            'totalRentPayments'
        ));
    }
}