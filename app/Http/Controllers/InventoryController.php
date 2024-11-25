<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\Announcement;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Medicine::with('category');

            if ($request->has('search')) {
                $query->search($request->search);
            }

            $medicines = $query->where('quantity', '>', 0)
                           ->where('status', '!=', 'disabled')  // Exclude disabled medicines
                           ->latest()
                           ->paginate(10);
            Medicine::where('quantity', 0)->update(['status' => 'disabled']);


            return Inertia::render('Medicines/Index', [
                'medicines' => $medicines->items() ?? [],
                'filters' => $request->only(['search'])
            ]);
        } catch (\Exception $e) {
            return Inertia::render('Medicines/Index', [
                'medicines' => [],
                'filters' => $request->only(['search']),
                'error' => 'Failed to load medicines'
            ]);
        }
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
        $user = Auth::user();

        // Get user-specific data
        $medicineCount = Medicine::count();
        $purchaseCount = Purchase::where('user_id', $user->id)->count();

        // Get recent purchases with medicine details
        $recentPurchases = Purchase::where('user_id', $user->id)
            ->with('medicine')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($purchase) {
                return [
                    'id' => $purchase->id,
                    'medicine' => $purchase->medicine,
                    'name' => $purchase->name,
                    'created_at' => $purchase->created_at,
                    'quantity' => $purchase->quantity,
                    'status' => $purchase->status ?? 'pending',
                    'total_amount' => $purchase->mprice * $purchase->quantity
                ];
            });

        // Get available medicines (in stock)
        $availableMedicines = Medicine::where('quantity', '>', 0)
            ->latest()
            ->take(6)
            ->get();

        // Get medicines with special prices
        $promoMedicines = Medicine::with('category')
            ->where('quantity', '>', 0)
            ->where('status', '!=', 'disabled')
            ->latest()
            ->take(5)
            ->get();

        $totalSpent = Purchase::where('user_id', $user->id)
            ->where('status', 'completed')
            ->selectRaw('SUM(mprice * quantity) as total_spent')
            ->value('total_spent') ?? 0;

        // Get latest announcements
        $announcements = Announcement::with('admin')
            ->published()
            ->recent()
            ->take(4)
            ->get();

        return Inertia::render('Dashboard', [
            'medicineCount' => $medicineCount,
            'purchaseCount' => $purchaseCount,
            'recentPurchases' => $recentPurchases,
            'totalSpent' => $totalSpent,
            'availableMedicines' => $availableMedicines,
            'promoMedicines' => $promoMedicines,
            'announcements' => $announcements
        ]);
    }
}
