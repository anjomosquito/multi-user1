<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medicine;
use App\Models\Purchase;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;

class InventoryController extends Controller
{
    public function index()
    {
        // Retrieve all medicines using the Medicine model
        $medicines = Medicine::all();

        // Pass the retrieved data to the view using Inertia
        return Inertia::render('Medicines/Index', [
            'medicines' => $medicines
        ]);
    }

    public function store(Request $request)
    {
        Medicine::create([
            'name' => $request->name,
            'lprice' => $request->lprice,
            'mprice' => $request->mprice,
            'hprice' => $request->hprice,
            'quantity' => $request->quantity,
            'dosage' => $request->dosage,
            'expdate' => $request->expdate,
        ]);

        return redirect()->route('medicines.index');
    }

    public function dashboard()
{
    // Get the count of medicines
    $medicineCount = Medicine::count();

    // Get the count of purchases for the authenticated user
    $purchaseCount = Purchase::where('user_id', Auth::id())->count();

    // Pass both counts to the dashboard view
    return Inertia::render('Dashboard', [
        'medicineCount' => $medicineCount,
        'purchaseCount' => $purchaseCount
    ]);
}


}
