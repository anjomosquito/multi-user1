<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        // Get counts and analytics
        $medicineCount = Medicine::count();
        $purchaseCount = Purchase::count();
        
        // Get low stock medicines with details
        $lowStockMedicines = Medicine::where('quantity', '<=', 10)
            ->select('id', 'name', 'quantity', 'dosage')
            ->get();
        $lowStockCount = $lowStockMedicines->count();
        
        // Calculate total revenue
        $totalRevenue = Purchase::where('status', 'completed')
            ->selectRaw('SUM(mprice * quantity) as total_revenue')
            ->value('total_revenue') ?? 0;

        // Get recent purchases with user details
        $recentPurchases = Purchase::with('user')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($purchase) {
                return [
                    'id' => $purchase->id,
                    'user' => $purchase->user ? [
                        'id' => $purchase->user->id,
                        'name' => $purchase->user->name,
                    ] : null,
                    'name' => $purchase->name,
                    'created_at' => $purchase->created_at,
                    'total_amount' => $purchase->mprice * $purchase->quantity,
                    'status' => $purchase->status ?? 'pending'
                ];
            });

        // Get medicines expiring within 3 months with more details
        $expiringMedicines = Medicine::whereDate('expdate', '<=', now()->addMonths(3))
            ->orderBy('expdate')
            ->get()
            ->map(function ($medicine) {
                $expiryDate = \Carbon\Carbon::parse($medicine->expdate);
                $now = \Carbon\Carbon::now();
                $daysUntilExpiry = $now->diffInDays($expiryDate, false);
                
                // Get a more detailed time description
                $timeUntilExpiry = '';
                if ($daysUntilExpiry < 0) {
                    $timeUntilExpiry = 'Expired ' . abs($daysUntilExpiry) . ' days ago';
                } elseif ($daysUntilExpiry === 0) {
                    $timeUntilExpiry = 'Expires today';
                } elseif ($daysUntilExpiry === 1) {
                    $timeUntilExpiry = 'Expires tomorrow';
                } elseif ($daysUntilExpiry <= 30) {
                    $timeUntilExpiry = $daysUntilExpiry . ' days left';
                } else {
                    $months = ceil($daysUntilExpiry / 30);
                    $timeUntilExpiry = $months . ' month' . ($months > 1 ? 's' : '') . ' left';
                }

                return [
                    'id' => $medicine->id,
                    'name' => $medicine->name,
                    'expdate' => $medicine->expdate,
                    'quantity' => $medicine->quantity,
                    'dosage' => $medicine->dosage,
                    'days_until_expiry' => $daysUntilExpiry,
                    'time_until_expiry' => $timeUntilExpiry,
                    'status' => $daysUntilExpiry <= 30 ? 'critical' : 'warning',
                    'is_expired' => $daysUntilExpiry < 0
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'medicineCount' => $medicineCount,
            'purchaseCount' => $purchaseCount,
            'lowStockCount' => $lowStockCount,
            'lowStockMedicines' => $lowStockMedicines,
            'totalRevenue' => $totalRevenue,
            'recentPurchases' => $recentPurchases,
            'expiringMedicines' => $expiringMedicines
        ]);
    }
}
