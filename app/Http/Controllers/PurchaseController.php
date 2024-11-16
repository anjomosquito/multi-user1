<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Purchase;
use App\Models\Cart;
use App\Models\Medicine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

                    // Create the purchase record with pending status
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
                        'purchase_date' => now(),
                        'status' => 'pending', // Changed from 'completed' to 'pending'
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
        $purchases = Purchase::where('user_id', Auth::id())
            ->with('user') // Load the user relationship
            ->latest()
            ->get()
            ->map(function ($purchase) {
                return [
                    'id' => $purchase->id,
                    'name' => $purchase->name,
                    'user' => $purchase->user ? ['name' => $purchase->user->name] : null, // Include the user's name
                    'quantity' => $purchase->quantity,
                    'mprice' => $purchase->mprice,
                    'total_amount' => $purchase->mprice * $purchase->quantity,
                    'dosage' => $purchase->dosage,
                    'expdate' => $purchase->expdate,
                    'purchase_date' => $purchase->purchase_date,
                    'status' => $purchase->status,
                    'ready_for_pickup' => $purchase->ready_for_pickup,
                    'pickup_ready_at' => $purchase->pickup_ready_at,
                    'pickup_deadline' => $purchase->pickup_deadline,
                    'created_at' => $purchase->created_at,
                    'admin_pickup_verified' => $purchase->admin_pickup_verified,
                    'user_pickup_verified' => $purchase->user_pickup_verified,
                    'time_remaining' => $this->calculateTimeRemaining($purchase),
                    'payment_proof' => $purchase->payment_proof,
                    'payment_proof_url' => $purchase->payment_proof_url,
                    'payment_status' => $purchase->payment_status,
                    'transaction_number' => str_pad($purchase->id, 5, '0', STR_PAD_LEFT),
                ];
            });

        return Inertia::render('Purchase/Index', [
            'purchases' => $purchases
        ]);
    }


    public function cancel($id)
    {
        // Find the purchase by ID
        $purchase = Purchase::where('user_id', Auth::id())->findOrFail($id);

        // Only allow cancellation of pending purchases
        if ($purchase->status === 'completed') {
            return redirect()->route('purchase.index')
                ->with('error', 'Cannot cancel completed purchases.');
        }

        // Retrieve the corresponding medicine and update its quantity
        $medicine = Medicine::find($purchase->medicine_id);

        if ($medicine) {
            // Increment the medicine's quantity in the inventory by the canceled purchase quantity
            $medicine->increment('quantity', $purchase->quantity);
        }

        // Delete the purchase record
        $purchase->delete();

        return redirect()->route('purchase.index')
            ->with('success', 'Purchase canceled and inventory updated successfully.');
    }

    public function verifyPickup($id)
    {
        $purchase = Purchase::where('user_id', Auth::id())->findOrFail($id);

        if (!$purchase->ready_for_pickup) {
            return redirect()->back()->with('error', 'Purchase must be ready for pickup first');
        }

        \DB::transaction(function () use ($purchase) {
            $purchase->update([
                'user_pickup_verified' => true,
                'user_verified_at' => now()
            ]);

            // Check if admin has already verified
            if ($purchase->admin_pickup_verified) {
                $purchase->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                    'ready_for_pickup' => false
                ]);
            }
        });

        if ($purchase->admin_pickup_verified) {
            return redirect()->back()->with('success', 'Pickup verified and order completed.');
        }

        return redirect()->back()->with('success', 'Pickup verified. Waiting for admin verification.');
    }

    private function calculateTimeRemaining($purchase)
    {
        if (!$purchase->pickup_deadline || !$purchase->ready_for_pickup) {
            return null;
        }

        $now = now();
        $deadline = \Carbon\Carbon::parse($purchase->pickup_deadline);

        if ($now->isAfter($deadline)) {
            return 'Expired';
        }

        $minutes = $now->diffInMinutes($deadline, false);
        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        return sprintf('%02d:%02d hours remaining', $hours, $remainingMinutes);
    }

    public function uploadPaymentProof(Request $request, $id)
    {
        $request->validate([
            'payment_proof' => 'required|image|max:2048|mimes:jpeg,png,jpg'
        ]);

        $purchase = Purchase::where('user_id', Auth::id())->findOrFail($id);

        if ($purchase->payment_proof) {
            // Delete old payment proof if exists
            Storage::disk('public')->delete($purchase->payment_proof);
        }

        // Store the new payment proof in public disk
        $path = $request->file('payment_proof')->store('payment_proofs', 'public');

        $purchase->update([
            'payment_proof' => $path,
            'payment_status' => Purchase::PAYMENT_STATUS_PENDING
        ]);

        return back()->with('success', 'Payment proof uploaded successfully.');
    }
}
