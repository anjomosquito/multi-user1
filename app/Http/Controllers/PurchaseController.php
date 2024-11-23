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
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PurchaseController extends Controller
{
    public function store(Request $request)
    {
        $cartItems = $request->input('cartItems');

        // Generate a unique transaction ID for this group of purchases
        $transactionId = 'TXN-' . time() . '-' . Auth::id() . '-' . uniqid();

        // Start a transaction to ensure data integrity
        DB::transaction(function () use ($cartItems, $transactionId) {
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
                        'status' => 'pending',
                        'transaction_id' => $transactionId
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
            ->with('user')
            ->latest()
            ->get()
            ->groupBy('transaction_id') // Group purchases by transaction_id
            ->map(function ($group) {
                $firstPurchase = $group->first();
                return [
                    'transaction_id' => $firstPurchase->transaction_id,
                    'transaction_number' => str_pad($firstPurchase->id, 5, '0', STR_PAD_LEFT),
                    'created_at' => $firstPurchase->created_at,
                    'status' => $firstPurchase->status,
                    'ready_for_pickup' => $firstPurchase->ready_for_pickup,
                    'pickup_ready_at' => $firstPurchase->pickup_ready_at,
                    'pickup_deadline' => $firstPurchase->pickup_deadline,
                    'admin_pickup_verified' => $firstPurchase->admin_pickup_verified,
                    'user_pickup_verified' => $firstPurchase->user_pickup_verified,
                    'time_remaining' => $this->calculateTimeRemaining($firstPurchase),
                    'payment_proof' => $firstPurchase->payment_proof,
                    'payment_proof_url' => $firstPurchase->payment_proof_url,
                    'payment_status' => $firstPurchase->payment_status,
                    'items' => $group->map(function ($purchase) {
                        return [
                            'id' => $purchase->id,
                            'name' => $purchase->name,
                            'quantity' => $purchase->quantity,
                            'mprice' => $purchase->mprice,
                            'total_amount' => $purchase->mprice * $purchase->quantity,
                            'dosage' => $purchase->dosage,
                        ];
                    })->values(),
                    'total_amount' => $group->sum(function ($purchase) {
                        return $purchase->mprice * $purchase->quantity;
                    })
                ];
            })->values();

        return Inertia::render('Purchase/Index', [
            'purchases' => $purchases
        ]);
    }

    public function cancel($id)
    {
        // Find the purchase by transaction_id
        $purchases = Purchase::where('transaction_id', $id)
            ->where('user_id', Auth::id())
            ->where('status', 'pending')
            ->get();

        if ($purchases->isEmpty()) {
            return back()->with('error', 'Purchase not found or cannot be cancelled.');
        }

        DB::transaction(function () use ($purchases) {
            foreach ($purchases as $purchase) {
                // Return the quantity back to inventory
                $medicine = Medicine::find($purchase->medicine_id);
                if ($medicine) {
                    $medicine->increment('quantity', $purchase->quantity);
                }

                // Update purchase status to cancelled
                $purchase->update(['status' => 'cancelled']);
            }
        });

        return back()->with('success', 'Purchase cancelled successfully.');
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

    public function generatePurchaseReport(Purchase $purchase)
    {
        // Ensure the user can only view their own purchase reports
        if ($purchase->user_id !== Auth::id()) {
            abort(403);
        }

        // Format the data for the report
        $reportData = [
            'transaction_number' => str_pad($purchase->id, 5, '0', STR_PAD_LEFT),
            'date' => Carbon::parse($purchase->created_at)->format('M d, Y h:i A'),
            'customer_name' => Auth::user()->name,
            'medicine_name' => $purchase->name,
            'dosage' => $purchase->dosage,
            'quantity' => $purchase->quantity,
            'unit_price' => number_format($purchase->mprice, 2),
            'total_amount' => number_format($purchase->quantity * $purchase->mprice, 2),
            'status' => $purchase->status,
            'payment_status' => $purchase->payment_status,
            'verification_date' => $purchase->payment_verified_at ? Carbon::parse($purchase->payment_verified_at)->format('M d, Y h:i A') : 'N/A'
        ];

        // Generate PDF
        $pdf = PDF::loadView('reports.purchase-receipt', ['purchase' => $reportData]);
        
        // Return the PDF for download or viewing
        return $pdf->stream("receipt-{$reportData['transaction_number']}.pdf");
    }
}
