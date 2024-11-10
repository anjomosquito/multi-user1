<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Purchase;
use App\Models\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;

class PurchaseController extends Controller
{
    public function store(Request $request)
    {
        // Get the cart items from the request
        $cartItems = $request->input('cartItems');

        // Process each cart item and store it in the purchases table
        foreach ($cartItems as $item) {
            Purchase::create([
                'user_id' => Auth::id(),
                'medicine_id' => $item['medicine_id'],
                'quantity' => $item['quantity'],
                'name' => $item['name'],
                'lprice' => $item['lprice'],
                'mprice' => $item['mprice'],
                'hprice' => $item['hprice'],
                'dosage' => $item['dosage'],
                'expdate' => $item['expdate'],
            ]);
        }

        // Delete the items from the cart after purchase
        Cart::where('user_id', Auth::id())->delete();

        return redirect()->route('purchase.index'); // Redirect to the purchase index page
    }

    public function index()
    {
        $purchases = Purchase::where('user_id', Auth::id())->get();

        return Inertia::render('Purchase/Index', [
            'purchases' => $purchases
        ]);
    }
    public function cancel($id)
    {
        // Find the purchase by ID and delete it if it exists
        $purchase = Purchase::where('user_id', Auth::id())->findOrFail($id);
        $purchase->delete();

        return redirect()->route('purchase.index')->with('success', 'Purchase canceled successfully.');
    }

}
