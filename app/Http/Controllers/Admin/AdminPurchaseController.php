<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AdminPurchaseController extends Controller
{
    // View all purchases for the admin
    public function index()
    {
        $purchases = Purchase::with('user')->get(); // Retrieve purchases with user info

        return Inertia::render('Admin/Purchase/Index', [
            'purchases' => $purchases
        ]);
    }
}
