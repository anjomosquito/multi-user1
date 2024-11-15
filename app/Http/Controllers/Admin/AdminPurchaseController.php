<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Redirect;

class AdminPurchaseController extends Controller
{
    // View all purchases for the admin
    public function index()
    {
        // Retrieve purchases along with user info and group them by user_id
        $purchases = Purchase::with('user')
            ->get()
            ->groupBy('user_id')
            ->map(function ($userPurchases) {
                $user = $userPurchases->first()->user; // Get the user info
                return [
                    'name' => $user->name,
                    'purchases' => $userPurchases,
                ];
            });

        return Inertia::render('Admin/Purchase/Index', [
            'purchases' => $purchases,
        ]);
    }

    public function acceptPurchase($purchaseId)
    {
        $purchase = Purchase::find($purchaseId);
        $purchase->status = 'accepted';
        $purchase->save();

        return Redirect::route('admin.purchase.index')->with('status', 'Purchase accepted');
    }

    public function rejectPurchase($purchaseId)
    {
        $purchase = Purchase::find($purchaseId);
        $purchase->status = 'rejected';
        $purchase->save();

        return Redirect::route('admin.purchase.index')->with('status', 'Purchase rejected');
    }
}
