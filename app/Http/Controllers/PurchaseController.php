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

    public function verifyPickup($transactionId)
    {
        try {
            // Find all purchases in the transaction
            $purchases = Purchase::where('transaction_id', $transactionId)
                ->where('user_id', Auth::id())
                ->get();

            if ($purchases->isEmpty()) {
                return back()->with('error', 'Transaction not found.');
            }

            $firstPurchase = $purchases->first();

            // Validate purchase state
            if (!$firstPurchase->ready_for_pickup) {
                return back()->with('error', 'Purchase is not ready for pickup yet.');
            }

            if ($firstPurchase->user_pickup_verified) {
                return back()->with('error', 'You have already verified this pickup.');
            }

            if ($firstPurchase->payment_status !== 'verified') {
                return back()->with('error', 'Payment must be verified before pickup.');
            }

            // Check if pickup deadline has passed
            $deadline = Carbon::parse($firstPurchase->pickup_deadline);
            if ($deadline && now()->isAfter($deadline)) {
                return back()->with('error', 'Pickup deadline has passed. Please contact admin for assistance.');
            }

            \DB::transaction(function () use ($purchases) {
                $now = now();

                foreach ($purchases as $purchase) {
                    $updates = [
                        'user_pickup_verified' => true,
                        'user_verified_at' => $now,
                        'last_status_update' => $now
                    ];

                    // If admin has already verified, complete the purchase
                    if ($purchase->admin_pickup_verified) {
                        $updates = array_merge($updates, [
                            'status' => 'completed',
                            'completed_at' => $now,
                            'ready_for_pickup' => false
                        ]);
                    }

                    $purchase->update($updates);

                    // Log the verification
                    \Log::info('User verified pickup', [
                        'transaction_id' => $purchase->transaction_id,
                        'purchase_id' => $purchase->id,
                        'user_id' => Auth::id(),
                        'admin_verified' => $purchase->admin_pickup_verified
                    ]);
                }

                // Send notification to admin about user pickup verification
                $adminNotification = new \App\Notifications\AdminNotification(
                    'User Pickup Verification',
                    'User has verified pickup for transaction #' . $purchases->first()->transaction_id,
                    route('admin.purchase.show', $purchases->first()->id)
                );
                
                // Notify all admins
                \App\Models\Admin::all()->each(function ($admin) use ($adminNotification) {
                    $admin->notify($adminNotification);
                });
            });

            $message = $firstPurchase->admin_pickup_verified 
                ? 'Pickup verified and order completed successfully.'
                : 'Pickup verified successfully. Waiting for admin verification.';

            return back()->with('success', $message);

        } catch (\Exception $e) {
            \Log::error('Failed to verify pickup', [
                'transaction_id' => $transactionId,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to verify pickup. Please try again.');
        }
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

    public function uploadPaymentProof(Request $request, $transactionId)
    {
        try {
            // Validate request
            $request->validate([
                'payment_proof' => [
                    'required',
                    'image',
                    'max:2048', // 2MB max
                    'mimes:jpeg,png,jpg',
                    'dimensions:min_width=200,min_height=200' // Ensure minimum image quality
                ]
            ], [
                'payment_proof.max' => 'The payment proof must not be larger than 2MB.',
                'payment_proof.mimes' => 'The payment proof must be a JPEG, PNG, or JPG image.',
                'payment_proof.dimensions' => 'The payment proof must be at least 200x200 pixels.'
            ]);

            // Find all purchases in the transaction
            $purchases = Purchase::where('transaction_id', $transactionId)
                ->where('user_id', Auth::id())
                ->get();

            if ($purchases->isEmpty()) {
                \Log::warning('Attempted to upload payment proof for non-existent transaction', [
                    'transaction_id' => $transactionId,
                    'user_id' => Auth::id()
                ]);
                return back()->with('error', 'Transaction not found.');
            }

            // Check if payment proof can be uploaded
            $firstPurchase = $purchases->first();
            if (!in_array($firstPurchase->status, ['confirmed', 'ready_for_pickup'])) {
                \Log::warning('Attempted to upload payment proof for invalid purchase status', [
                    'transaction_id' => $transactionId,
                    'status' => $firstPurchase->status
                ]);
                return back()->with('error', 'Payment proof can only be uploaded for confirmed or ready purchases.');
            }

            if ($firstPurchase->payment_status === 'verified') {
                \Log::info('Attempted to upload payment proof for already verified payment', [
                    'transaction_id' => $transactionId
                ]);
                return back()->with('error', 'Payment has already been verified.');
            }

            DB::transaction(function () use ($request, $purchases, $transactionId) {
                $file = $request->file('payment_proof');
                
                // Generate a unique filename with timestamp and random string
                $filename = 'payment_' . $transactionId . '_' . time() . '_' . uniqid() . '.' . 
                    $file->getClientOriginalExtension();

                try {
                    // Store the new payment proof in public disk
                    $path = $file->storeAs('payment_proofs', $filename, 'public');
                    
                    // Ensure file was stored successfully
                    if (!Storage::disk('public')->exists($path)) {
                        throw new \Exception('Failed to store payment proof file.');
                    }

                    // Delete old payment proof if exists
                    $oldProof = $purchases->first()->payment_proof;
                    if ($oldProof && Storage::disk('public')->exists($oldProof)) {
                        Storage::disk('public')->delete($oldProof);
                    }

                    // Update all purchases in the transaction
                    foreach ($purchases as $purchase) {
                        $purchase->update([
                            'payment_proof' => $path,
                            'payment_proof_url' => Storage::disk('public')->url($path),
                            'payment_status' => 'pending',
                            'payment_uploaded_at' => now(),
                            'last_payment_update' => now()
                        ]);
                    }

                    \Log::info('Payment proof uploaded successfully', [
                        'transaction_id' => $transactionId,
                        'file_path' => $path
                    ]);

                } catch (\Exception $e) {
                    // Clean up the uploaded file if it exists
                    if (isset($path) && Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                    throw $e;
                }
            });

            return back()->with('success', 'Payment proof uploaded successfully. Please wait for admin verification.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::notice('Payment proof validation failed', [
                'transaction_id' => $transactionId,
                'errors' => $e->errors()
            ]);
            return back()->withErrors($e->errors())->withInput();

        } catch (\Exception $e) {
            \Log::error('Failed to upload payment proof', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to upload payment proof. Please try again.');
        }
    }

    public function generatePurchaseReport($transactionId)
    {
        // Find all purchases in the transaction
        $purchases = Purchase::where('transaction_id', $transactionId)
            ->where('user_id', Auth::id())
            ->with('user')
            ->get();

        if ($purchases->isEmpty()) {
            abort(404, 'Transaction not found');
        }

        $firstPurchase = $purchases->first();

        // Format the data for the report
        $reportData = [
            'transaction_number' => str_pad($firstPurchase->id, 5, '0', STR_PAD_LEFT),
            'date' => Carbon::parse($firstPurchase->created_at)->format('M d, Y h:i A'),
            'customer_name' => Auth::user()->name,
            'status' => $firstPurchase->status,
            'payment_status' => $firstPurchase->payment_status,
            'verification_date' => $firstPurchase->payment_verified_at 
                ? Carbon::parse($firstPurchase->payment_verified_at)->format('M d, Y h:i A') 
                : 'N/A',
            'items' => $purchases->map(function ($purchase) {
                return [
                    'medicine_name' => $purchase->name,
                    'dosage' => $purchase->dosage,
                    'quantity' => $purchase->quantity,
                    'unit_price' => number_format($purchase->mprice, 2),
                    'total_amount' => number_format($purchase->quantity * $purchase->mprice, 2),
                ];
            })->toArray(),
            'total_amount' => number_format($purchases->sum(function ($purchase) {
                return $purchase->quantity * $purchase->mprice;
            }), 2)
        ];

        // Generate PDF
        $pdf = PDF::loadView('reports.purchase-receipt', ['purchase' => $reportData]);
        
        // Set paper size and orientation
        $pdf->setPaper('a4', 'portrait');
        
        // Return the PDF for download or viewing
        return $pdf->stream("receipt-{$reportData['transaction_number']}.pdf");
    }
}
