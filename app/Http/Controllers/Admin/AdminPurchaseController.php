<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use App\Models\Medicine;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Notifications\OrderReadyForPickup;
use App\Notifications\PurchaseNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class AdminPurchaseController extends Controller
{
    
    public function generateReport()
    {
        $purchases = Purchase::with('user')
            ->selectRaw('
            DATE(purchase_date) as date,
            SUM(quantity * mprice) as total_sales,
            SUM(quantity) as total_quantity,
            COUNT(id) as total_purchases
        ')
            ->where('status', 'Completed') // Only include purchases with 'Completed' status
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        return inertia('Admin/Reports/Index', ['reports' => $purchases]);
    }



    public function index()
    {
        $purchases = Purchase::with('user')
            ->latest()
            ->get()
            ->groupBy('transaction_id')
            ->map(function ($group) {
                $firstPurchase = $group->first();
                $pickup_deadline = $firstPurchase->pickup_deadline ? \Carbon\Carbon::parse($firstPurchase->pickup_deadline) : null;
                $now = now();

                // Auto cancel if past deadline
                if ($pickup_deadline && $now->isAfter($pickup_deadline) && $firstPurchase->ready_for_pickup) {
                    $this->cancelExpiredPickup($firstPurchase);
                    $firstPurchase->refresh();
                }

                return [
                    'transaction_id' => $firstPurchase->transaction_id,
                    'transaction_number' => str_pad($firstPurchase->id, 5, '0', STR_PAD_LEFT),
                    'created_at' => $firstPurchase->created_at,
                    'status' => $this->determineStatus($firstPurchase),
                    'ready_for_pickup' => $firstPurchase->ready_for_pickup,
                    'pickup_ready_at' => $firstPurchase->pickup_ready_at,
                    'pickup_deadline' => $firstPurchase->pickup_deadline,
                    'admin_pickup_verified' => $firstPurchase->admin_pickup_verified,
                    'user_pickup_verified' => $firstPurchase->user_pickup_verified,
                    'time_remaining' => $this->calculateTimeRemaining($firstPurchase),
                    'payment_proof' => $firstPurchase->payment_proof,
                    'payment_proof_url' => $firstPurchase->payment_proof_url,
                    'payment_status' => $firstPurchase->payment_status,
                    'user' => $firstPurchase->user ? [
                        'id' => $firstPurchase->user->id,
                        'name' => $firstPurchase->user->name,
                    ] : null,
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

        return Inertia::render('Admin/Purchase/Index', [
            'purchases' => $purchases
        ]);
    }

    public function show($id)
    {
        $purchase = Purchase::with('user')->findOrFail($id);
        
        return Inertia::render('Admin/Purchase/Show', [
            'purchase' => [
                'id' => $purchase->id,
                'user' => $purchase->user,
                'name' => $purchase->name,
                'quantity' => $purchase->quantity,
                'total_amount' => $purchase->mprice * $purchase->quantity,
                'status' => $purchase->status ?? 'pending',
                'created_at' => $purchase->created_at,
                'updated_at' => $purchase->updated_at,
                'dosage' => $purchase->dosage,
                'pickup_ready_at' => $purchase->pickup_ready_at,
                'pickup_deadline' => $purchase->pickup_deadline,
                'admin_pickup_verified' => $purchase->admin_pickup_verified,
                'user_pickup_verified' => $purchase->user_pickup_verified,
                'verification_status' => $this->getVerificationStatus($purchase),
                'payment_proof' => $purchase->payment_proof,
                'payment_proof_url' => $purchase->payment_proof_url,
                'payment_status' => $purchase->payment_status,
                'payment_verified_at' => $purchase->payment_verified_at,
            ]
        ]);
    }

    public function confirm($transactionId)
    {
        try {
            $purchases = Purchase::where('transaction_id', $transactionId)->get();
            
            if ($purchases->isEmpty()) {
                throw new ModelNotFoundException('Transaction not found');
            }

            // Validate purchase state
            $firstPurchase = $purchases->first();
            if ($firstPurchase->status !== 'pending') {
                return back()->with('error', 'Only pending purchases can be confirmed');
            }

            if ($firstPurchase->payment_status === 'rejected') {
                return back()->with('error', 'Cannot confirm purchase with rejected payment');
            }

            \DB::transaction(function () use ($purchases) {
                foreach ($purchases as $purchase) {
                    $purchase->update([
                        'status' => 'confirmed',
                        'confirmed_at' => now(),
                        'confirmed_by' => auth('admin')->id(),
                        'last_status_update' => now()
                    ]);

                    // Log the status change
                    Log::info('Purchase confirmed', [
                        'transaction_id' => $purchase->transaction_id,
                        'purchase_id' => $purchase->id,
                        'admin_id' => auth('admin')->id()
                    ]);
                }

                // Send confirmation notification to user
                $firstPurchase->user->notify(new PurchaseNotification(
                    'Purchase Confirmed',
                    'Your purchase #' . $firstPurchase->transaction_id . ' has been confirmed. Please proceed with the payment.'
                ));
            });

            return back()->with('success', 'Purchase confirmed successfully');
        } catch (\Exception $e) {
            Log::error('Failed to confirm purchase', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to confirm purchase. Please try again.');
        }
    }

    public function markAsReady($transactionId)
    {
        try {
            $purchases = Purchase::where('transaction_id', $transactionId)->get();
            
            if ($purchases->isEmpty()) {
                throw new ModelNotFoundException('Transaction not found');
            }

            $firstPurchase = $purchases->first();
            
            // Validate purchase state
            if ($firstPurchase->status !== 'confirmed') {
                return back()->with('error', 'Purchase must be confirmed first');
            }

            if ($firstPurchase->payment_status !== 'verified') {
                return back()->with('error', 'Payment must be verified before marking as ready');
            }

            \DB::transaction(function () use ($purchases) {
                $now = now();
                $deadline = $now->copy()->addHours(24);

                foreach ($purchases as $purchase) {
                    $purchase->update([
                        'ready_for_pickup' => true,
                        'pickup_ready_at' => $now,
                        'pickup_deadline' => $deadline,
                        'last_status_update' => $now
                    ]);

                    // Log the status change
                    Log::info('Purchase marked as ready', [
                        'transaction_id' => $purchase->transaction_id,
                        'purchase_id' => $purchase->id,
                        'pickup_deadline' => $deadline
                    ]);
                }

                // Send ready for pickup notification
                $firstPurchase->user->notify(new OrderReadyForPickup([
                    'transaction_id' => $firstPurchase->transaction_id,
                    'deadline' => $deadline->format('M d, Y h:i A'),
                    'items' => $purchases->pluck('name')->join(', ')
                ]));
            });

            return back()->with('success', 'Order marked as ready for pickup and customer has been notified.');
        } catch (\Exception $e) {
            Log::error('Failed to mark order as ready', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to mark order as ready. Please try again.');
        }
    }

    public function markAsPickedUp($transactionId)
    {
        try {
            $purchases = Purchase::where('transaction_id', $transactionId)->get();
            
            if ($purchases->isEmpty()) {
                throw new ModelNotFoundException('Transaction not found');
            }

            $firstPurchase = $purchases->first();

            // Validate purchase state
            if (!$firstPurchase->ready_for_pickup) {
                return back()->with('error', 'Purchase must be ready for pickup first');
            }

            if (!$firstPurchase->user_pickup_verified) {
                return back()->with('error', 'User must verify pickup first');
            }

            if ($firstPurchase->payment_status !== 'verified') {
                return back()->with('error', 'Payment must be verified before completing pickup');
            }

            \DB::transaction(function () use ($purchases) {
                $now = now();
                foreach ($purchases as $purchase) {
                    $purchase->update([
                        'admin_pickup_verified' => true,
                        'admin_verified_at' => $now,
                        'status' => 'completed',
                        'completed_at' => $now,
                        'ready_for_pickup' => false,
                        'last_status_update' => $now
                    ]);

                    // Log the completion
                    Log::info('Purchase completed', [
                        'transaction_id' => $purchase->transaction_id,
                        'purchase_id' => $purchase->id,
                        'admin_id' => auth('admin')->id()
                    ]);
                }

                // Send completion notification
                $firstPurchase->user->notify(new PurchaseNotification(
                    'Purchase Completed',
                    'Your purchase #' . $firstPurchase->transaction_id . ' has been completed. Thank you for your business!'
                ));
            });

            return back()->with('success', 'Pickup verified and purchase completed successfully.');
        } catch (\Exception $e) {
            Log::error('Failed to verify pickup', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to verify pickup. Please try again.');
        }
    }

    public function verifyPayment(Request $request, $transactionId)
    {
        try {
            $purchases = Purchase::where('transaction_id', $transactionId)->get();
            
            if ($purchases->isEmpty()) {
                throw new ModelNotFoundException('Transaction not found');
            }

            $firstPurchase = $purchases->first();
            
            // Validate current state
            if (!in_array($firstPurchase->status, ['confirmed', 'ready_for_pickup'])) {
                return back()->with('error', 'Purchase must be confirmed first');
            }

            if (!$firstPurchase->payment_proof) {
                return back()->with('error', 'No payment proof uploaded');
            }

            $status = $request->input('status', 'verified');
            $reason = $request->input('reason');

            if ($status === 'rejected' && !$reason) {
                return back()->with('error', 'Please provide a reason for payment rejection');
            }

            \DB::transaction(function () use ($purchases, $status, $reason) {
                $now = now();
                foreach ($purchases as $purchase) {
                    $updates = [
                        'payment_status' => $status,
                        'payment_verified_at' => $status === 'verified' ? $now : null,
                        'payment_verified_by' => $status === 'verified' ? auth('admin')->id() : null,
                        'last_payment_update' => $now
                    ];

                    if ($status === 'rejected') {
                        $updates['payment_rejection_reason'] = $reason;
                    }

                    $purchase->update($updates);

                    // Log the verification
                    Log::info('Payment verification processed', [
                        'transaction_id' => $purchase->transaction_id,
                        'purchase_id' => $purchase->id,
                        'status' => $status,
                        'admin_id' => auth('admin')->id(),
                        'reason' => $reason
                    ]);
                }

                // Send appropriate notification
                $firstPurchase->user->notify(new PurchaseNotification(
                    $status === 'verified' ? 'Payment Verified' : 'Payment Rejected',
                    $status === 'verified' 
                        ? 'Your payment for purchase #' . $firstPurchase->transaction_id . ' has been verified.'
                        : 'Your payment for purchase #' . $firstPurchase->transaction_id . ' has been rejected. Reason: ' . $reason
                ));
            });

            $message = $status === 'verified' ? 'Payment verified successfully.' : 'Payment rejected successfully.';
            return back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Failed to process payment verification', [
                'transaction_id' => $transactionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to process payment verification. Please try again.');
        }
    }

    protected function formatPurchase($purchase)
    {
        return [
            'id' => $purchase->id,
            'name' => $purchase->name,
            'quantity' => $purchase->quantity,
            'status' => $purchase->determineStatus(),
            'ready_for_pickup' => $purchase->ready_for_pickup,
            'pickup_ready_at' => $purchase->pickup_ready_at,
            'confirmed_at' => $purchase->confirmed_at,
            'purchase_date' => $purchase->purchase_date,
            'dosage' => $purchase->dosage,
            'expdate' => $purchase->expdate,
            'lprice' => $purchase->lprice,
            'mprice' => $purchase->mprice,
            'hprice' => $purchase->hprice,
            'pickup_status' => $purchase->getPickupStatusAttribute(),
        ];
    }

    private function calculateTimeRemaining($purchase)
    {
        if (!$purchase->pickup_deadline) {
            return null;
        }

        $deadline = Carbon::parse($purchase->pickup_deadline);
        $now = now();

        if ($now->isAfter($deadline)) {
            return 'Expired';
        }

        $diff = $now->diff($deadline);
        if ($diff->days > 0) {
            return $diff->days . ' days ' . $diff->h . ' hours';
        }
        if ($diff->h > 0) {
            return $diff->h . ' hours ' . $diff->i . ' minutes';
        }
        return $diff->i . ' minutes';
    }

    private function cancelExpiredPickup($purchase)
    {
        $purchase->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => 'Pickup deadline expired'
        ]);

        // Notify user about cancellation
        $purchase->user->notify(new PurchaseNotification(
            'Order Cancelled',
            'Your order #' . $purchase->transaction_id . ' has been cancelled due to expired pickup deadline.'
        ));
    }

    private function determineStatus($purchase)
    {
        if ($purchase->status === 'cancelled') {
            return 'cancelled';
        }
        
        if ($purchase->admin_pickup_verified && $purchase->user_pickup_verified) {
            return 'completed';
        }
        
        if ($purchase->ready_for_pickup) {
            if ($purchase->user_pickup_verified && !$purchase->admin_pickup_verified) {
                return 'verified';
            }
            return 'ready';
        }
        
        if ($purchase->status === 'confirmed') {
            return 'confirmed';
        }
        
        return 'pending';
    }

    private function getVerificationStatus($purchase)
    {
        if ($purchase->status === 'completed') {
            return 'completed';
        }
        if ($purchase->user_pickup_verified && !$purchase->admin_pickup_verified) {
            return 'verified_by_user';
        }
        if ($purchase->admin_pickup_verified && !$purchase->user_pickup_verified) {
            return 'waiting_user';
        }
        if ($purchase->ready_for_pickup) {
            return 'ready';
        }
        return null;
    }

    public function generatePurchaseReport(Purchase $purchase)
    {
        // Get the purchase with related data
        $purchase = Purchase::with(['user', 'medicine'])
            ->where('id', $purchase->id)
            ->first();

        if (!$purchase) {
            abort(404);
        }

        // Format the data for the report
        $reportData = [
            'transaction_number' => str_pad($purchase->id, 5, '0', STR_PAD_LEFT),
            'date' => Carbon::parse($purchase->created_at)->format('M d, Y h:i A'),
            'customer_name' => $purchase->user->name ?? 'N/A',
            'medicine_name' => $purchase->name,
            'dosage' => $purchase->dosage,
            'quantity' => $purchase->quantity,
            'unit_price' => number_format($purchase->mprice, 2),
            'total_amount' => number_format($purchase->quantity * $purchase->mprice, 2),
            'status' => $purchase->status,
            'payment_status' => $purchase->payment_status,
            'payment_proof' => $purchase->payment_proof,
            'verification_date' => $purchase->payment_verified_at ? Carbon::parse($purchase->payment_verified_at)->format('M d, Y h:i A') : 'N/A'
        ];

        // Generate PDF
        $pdf = PDF::loadView('reports.purchase-detail', ['purchase' => $reportData]);
        
        // Return the PDF for download or viewing
        return $pdf->stream("purchase-{$reportData['transaction_number']}.pdf");
    }
}
