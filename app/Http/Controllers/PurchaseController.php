<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Purchase;
use App\Models\Cart;
use App\Models\Medicine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PurchaseController extends Controller
{
    public function store(Request $request)
    {
        $cartItems = $request->input('cartItems');

        // Start a transaction to ensure data integrity
        DB::transaction(function () use ($cartItems) {
            foreach ($cartItems as $item) {
                $medicine = Medicine::find($item['medicine_id']);

                // Check if the requested quantity is available
                if ($medicine && $medicine->quantity >= $item['quantity']) {
                    // Decrease the quantity in the medicine's inventory
                    $medicine->decrement('quantity', $item['quantity']);

                    // Create the purchase record
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
                } else {
                    // If quantity is insufficient, throw an exception to cancel the transaction
                    throw new \Exception("Insufficient quantity for {$item['name']}");
                }
            }

            // Delete the items from the cart after successful purchase
            Cart::where('user_id', Auth::id())->delete();
        });

        return redirect()->route('purchase.index')->with('success', 'Purchase completed successfully.');
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
        // Find the purchase by ID
        $purchase = Purchase::where('user_id', Auth::id())->findOrFail($id);

        // Retrieve the corresponding medicine and update its quantity
        $medicine = Medicine::find($purchase->medicine_id);
        
        if ($medicine) {
            // Increment the medicine's quantity in the inventory by the canceled purchase quantity
            $medicine->increment('quantity', $purchase->quantity);
        }

        // Delete the purchase record
        $purchase->delete();

        return redirect()->route('purchase.index')->with('success', 'Purchase canceled and inventory updated successfully.');
    }
}
