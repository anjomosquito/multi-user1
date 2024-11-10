<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    //
    public function index()
    {
        // Get the count of medicines and purchases
        $medicineCount = Medicine::count();
        $purchaseCount = Purchase::count();

        // Pass the counts to the dashboard view
        return Inertia::render('Admin/Dashboard', [
            'medicineCount' => $medicineCount,
            'purchaseCount' => $purchaseCount
        ]);
    }
}
