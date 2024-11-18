<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\Purchase;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $totalMedicines = Medicine::count();
        $totalPurchases = Purchase::count();
        $totalUsers = User::count();
        $lowStockCount = Medicine::where('quantity', '<', 10)->count();

        // Get expiring medicines (within next 30 days)
        $expiringMedicines = Medicine::whereDate('expdate', '<=', Carbon::now()->addDays(30))
            ->orderBy('expdate')
            ->take(5)
            ->get();

        // Get recent activity logs
        $recentActivity = Activity::with('causer')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'description' => $activity->description,
                    'causer_name' => $activity->causer ? $activity->causer->name : 'System',
                    'created_at' => $activity->created_at,
                    'properties' => $activity->properties,
                ];
            });

        // Get revenue data for chart (using mprice as the default price)
        $revenueData = Purchase::selectRaw('DATE(created_at) as date, SUM(mprice * quantity) as total')
            ->where('created_at', '>=', Carbon::now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get recent purchases with total amount
        $recentPurchases = Purchase::with('user')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($purchase) {
                return [
                    'id' => $purchase->id,
                    'user' => $purchase->user,
                    'name' => $purchase->name,
                    'created_at' => $purchase->created_at,
                    'status' => $purchase->status ?? 'pending',
                    'total_amount' => $purchase->mprice * $purchase->quantity
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'totalMedicines' => $totalMedicines,
            'totalPurchases' => $totalPurchases,
            'totalUsers' => $totalUsers,
            'lowStockCount' => $lowStockCount,
            'expiringMedicines' => $expiringMedicines,
            'recentActivity' => $recentActivity,
            'revenueData' => $revenueData,
            'recentPurchases' => $recentPurchases,
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->input('query');
        
        $medicines = Medicine::where('name', 'LIKE', "%{$query}%")
            ->orWhere('dosage', 'LIKE', "%{$query}%")
            ->take(5)
            ->get()
            ->map(function ($medicine) {
                return [
                    'id' => $medicine->id,
                    'name' => $medicine->name,
                    'quantity' => $medicine->quantity,
                    'dosage' => $medicine->dosage,
                    'type' => 'medicine'
                ];
            });

        $purchases = Purchase::with('user')
            ->where('name', 'LIKE', "%{$query}%")
            ->orWhereHas('user', function($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%");
            })
            ->take(5)
            ->get()
            ->map(function ($purchase) {
                return [
                    'id' => $purchase->id,
                    'name' => $purchase->name,
                    'user' => $purchase->user ? $purchase->user->name : 'N/A',
                    'created_at' => $purchase->created_at->format('Y-m-d'),
                    'type' => 'purchase'
                ];
            });

        $users = User::where('name', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->take(5)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'type' => 'user'
                ];
            });

        return Inertia::render('Admin/Dashboard', [
            'searchResults' => [
                'medicines' => $medicines,
                'purchases' => $purchases,
                'users' => $users,
            ],
            'filters' => [
                'query' => $query
            ]
        ]);
    }
}
