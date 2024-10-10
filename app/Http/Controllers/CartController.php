<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class CartController extends Controller
{
    // Add an item to the cart
    public function store(Request $request)
{
    $request->validate([
        'medicine_id' => 'required|exists:medicines,id',
        'quantity' => 'required|integer|min:1',
        'name' => 'required|string',
        'lprice' => 'required|numeric',
        'mprice' => 'required|numeric',
        'hprice' => 'required|numeric',
        'dosage' => 'required|string',
        'expdate' => 'required|date',
    ]);

    $user = Auth::user();

    // Check if the medicine is already in the cart
    $cartItem = Cart::where('user_id', $user->id)
                    ->where('medicine_id', $request->medicine_id)
                    ->first();

    if ($cartItem) {
        // Update the quantity if already in the cart
        $cartItem->quantity += $request->quantity;
        $cartItem->save();
    } else {
        // Add a new item to the cart with additional fields
        Cart::create([
            'user_id' => $user->id,
            'medicine_id' => $request->medicine_id,
            'quantity' => $request->quantity,
            'name' => $request->name,
            'lprice' => $request->lprice,
            'mprice' => $request->mprice,
            'hprice' => $request->hprice,
            'dosage' => $request->dosage,
            'expdate' => $request->expdate,
        ]);
    }

    return redirect()->route('cart.index')->with('success', 'Medicine added to cart successfully');
}


    // Remove an item from the cart
    public function destroy($id)
    {
        $cartItem = Cart::findOrFail($id);
    
        if ($cartItem->user_id == Auth::id()) {
            $cartItem->delete();
            // Redirect back to the cart page with a success message
            return redirect()->route('cart.index')->with('success', 'Item removed from cart successfully');
        }
    
        // Redirect back with an error message if the user is not authorized
        return redirect()->route('cart.index')->with('error', 'Unauthorized to remove this item');
    }
    
    // View cart items
    public function index()
    {
        $user = Auth::user();
        $cartItems = Cart::with('medicine')->where('user_id', $user->id)->get();

        return inertia('Cart/Index', [
            'cartItems' => $cartItems,
        ]);
    }
}
