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
            ->map(function ($purchase) {
                $pickup_deadline = $purchase->pickup_deadline ? \Carbon\Carbon::parse($purchase->pickup_deadline) : null;
                $now = now();

                // Auto cancel if past deadline
                if ($pickup_deadline && $now->isAfter($pickup_deadline) && $purchase->ready_for_pickup) {
                    $this->cancelExpiredPickup($purchase);
                    $purchase->refresh();
                }

                return [
                    'id' => $purchase->id,
                    'user' => $purchase->user ? [
                        'id' => $purchase->user->id,
                        'name' => $purchase->user->name,
                    ] : null,
                    'name' => $purchase->name,
                    'quantity' => $purchase->quantity,
                    'dosage' => $purchase->dosage,
                    'total_amount' => $purchase->mprice * $purchase->quantity,
                    'status' => $this->determineStatus($purchase),
                    'transaction_number' => str_pad($purchase->id, 5, '0', STR_PAD_LEFT),
                    'ready_for_pickup' => $purchase->ready_for_pickup,
                    'pickup_ready_at' => $purchase->pickup_ready_at,
                    'pickup_deadline' => $purchase->pickup_deadline,
                    'time_remaining' => $this->calculateTimeRemaining($purchase),
                    'created_at' => $purchase->created_at,
                    'admin_pickup_verified' => $purchase->admin_pickup_verified,
                    'user_pickup_verified' => $purchase->user_pickup_verified,
                    'verification_status' => $this->getVerificationStatus($purchase),
                    'payment_proof' => $purchase->payment_proof,
                    'payment_proof_url' => $purchase->payment_proof_url,
                    'payment_status' => $purchase->payment_status,
                    'payment_verified_at' => $purchase->payment_verified_at,
                ];
            });

        return Inertia::render('Admin/Purchase/Index', [
            'purchases' => $purchases
        ]);
    }

    public function confirm($id)
    {
        try {
            $purchase = Purchase::findOrFail($id);

            \DB::transaction(function () use ($purchase) {
                $purchase->update([
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                    'confirmed_by' => auth('admin')->id()
                ]);

                // Send confirmation email using user's email
                $purchase->sendNotification('confirmed');
            });

            return back()->with('success', 'Purchase confirmed successfully');
        } catch (\Exception $e) {
            Log::error('Failed to confirm purchase', [
                'purchase_id' => $id,
                'error' => $e->getMessage()
            ]);
            return back()->with('error', 'Failed to confirm purchase');
        }
    }

    public function markAsReady($id)
    {
        $purchase = Purchase::findOrFail($id);

        \DB::transaction(function () use ($purchase) {
            $purchase->update([
                'ready_for_pickup' => true,
                'pickup_ready_at' => now(),
                'pickup_deadline' => now()->addHours(24)
            ]);

            // Send ready for pickup email
            try {
                $purchase->sendNotification('ready');
            } catch (\Exception $e) {
                Log::error('Failed to send notification: ' . $e->getMessage());
                // Continue execution even if notification fails
            }
        });

        return back()->with('success', 'Order marked as ready for pickup and customer has been notified.');
    }

    public function markAsCompleted($id)
    {
        $purchase = Purchase::findOrFail($id);

        if (!$purchase->ready_for_pickup) {
            return redirect()->back()->with('error', 'Purchase must be ready for pickup first');
        }

        $purchase->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);

        return redirect()->back()->with('success', 'Purchase marked as completed.');
    }

    public function markAsPickedUp($id)
    {
        $purchase = Purchase::findOrFail($id);

        if (!$purchase->ready_for_pickup) {
            return redirect()->back()->with('error', 'Purchase must be ready for pickup first');
        }

        if (!$purchase->user_pickup_verified) {
            return redirect()->back()->with('error', 'User must verify pickup first');
        }

        \DB::transaction(function () use ($purchase) {
            $purchase->update([
                'admin_pickup_verified' => true,
                'admin_verified_at' => now(),
                'status' => 'completed',
                'completed_at' => now(),
                'ready_for_pickup' => false
            ]);
        });

        return redirect()->back()->with('success', 'Pickup verified and order completed.');
    }

    public function verifyPickup($id)
    {
        $purchase = Purchase::findOrFail($id);

        \DB::transaction(function () use ($purchase) {
            $purchase->update([
                'admin_pickup_verified' => true,
                'admin_verified_at' => now(),
                'status' => 'completed'
            ]);

            // Send completion email
            $purchase->sendNotification('completed');
        });

        Log::info('Admin verified pickup completion', [
            'purchase_id' => $purchase->id,
            'admin_id' => auth('admin')->id(),
            'user_id' => $purchase->user_id
        ]);

        return redirect()->back()->with('success', 'Pickup verified and order completed.');
    }

    public function verifyPayment(Request $request, $id)
    {
        $purchase = Purchase::findOrFail($id);

        $request->validate([
            'status' => 'required|in:verified,rejected'
        ]);

        $purchase->update([
            'payment_status' => $request->status,
            'payment_verified_at' => now(),
            'payment_verified_by' => Auth::guard('admin')->id()
        ]);

        return back()->with('success', 'Payment ' . $request->status . ' successfully.');
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

    private function cancelExpiredPickup($purchase)
    {
        // Return medicine to inventory
        $medicine = Medicine::find($purchase->medicine_id);
        if ($medicine) {
            $medicine->increment('quantity', $purchase->quantity);
        }

        // Update purchase status
        $purchase->update([
            'status' => 'cancelled',
            'ready_for_pickup' => false,
            'pickup_ready_at' => null,
            'pickup_deadline' => null
        ]);

        Log::info('Purchase auto-cancelled due to pickup deadline', [
            'purchase_id' => $purchase->id,
            'user_id' => $purchase->user_id
        ]);
    }

    private function determineStatus($purchase)
    {
        if ($purchase->status === 'cancelled') {
            return 'cancelled';
        }
        if ($purchase->status === 'completed') {
            return 'completed';
        }
        if ($purchase->user_pickup_verified && !$purchase->admin_pickup_verified) {
            return 'verified';
        }
        if ($purchase->ready_for_pickup) {
            return 'ready_for_pickup';
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
