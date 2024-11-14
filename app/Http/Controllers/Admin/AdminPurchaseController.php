<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AdminPurchaseController extends Controller
{
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
                    'ready_for_pickup' => $purchase->ready_for_pickup,
                    'pickup_ready_at' => $purchase->pickup_ready_at,
                    'pickup_deadline' => $purchase->pickup_deadline,
                    'time_remaining' => $this->calculateTimeRemaining($purchase),
                    'created_at' => $purchase->created_at,
                    'admin_pickup_verified' => $purchase->admin_pickup_verified,
                    'user_pickup_verified' => $purchase->user_pickup_verified,
                    'verification_status' => $this->getVerificationStatus($purchase)
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
            
            // Check if already confirmed
            if ($purchase->status === 'confirmed') {
                Log::warning('Attempted to confirm already confirmed purchase', [
                    'purchase_id' => $id,
                    'admin_id' => auth('admin')->id()
                ]);
                return back()->with('error', 'Purchase is already confirmed');
            }

            // Perform the update within a transaction
            \DB::transaction(function () use ($purchase) {
                $purchase->update([
                    'status' => 'confirmed',
                    'confirmed_at' => now(),
                    'confirmed_by' => auth('admin')->id()
                ]);
            });

            Log::info('Purchase confirmed successfully', [
                'purchase_id' => $purchase->id,
                'confirmed_by' => auth('admin')->id(),
                'confirmed_at' => now()->toDateTimeString()
            ]);

            return back()->with('success', 'Purchase confirmed successfully');

        } catch (ModelNotFoundException $e) {
            Log::error('Purchase not found for confirmation', [
                'purchase_id' => $id,
                'admin_id' => auth('admin')->id()
            ]);
            
            return back()->with('error', 'Purchase not found');
        }
    }

    public function markAsReady($id)
    {
        $purchase = Purchase::findOrFail($id);
        
        if ($purchase->status !== 'confirmed') {
            return redirect()->back()->with('error', 'Purchase must be confirmed first');
        }
        
        $now = now();
        $pickup_deadline = $now->copy()->addHours(12);
        
        $purchase->update([
            'ready_for_pickup' => true,
            'pickup_ready_at' => $now,
            'pickup_deadline' => $pickup_deadline
        ]);

        return redirect()->back()->with('success', 'Purchase marked as ready for pickup. Customer has 12 hours to pick up.');
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

        Log::info('Admin verified pickup completion', [
            'purchase_id' => $purchase->id,
            'admin_id' => auth('admin')->id(),
            'user_id' => $purchase->user_id
        ]);

        return redirect()->back()->with('success', 'Pickup verified and order completed.');
    }

    protected function formatPurchase($purchase)
    {
        return [
            'id' => $purchase->id,
            'name' => $purchase->name,
            'quantity' => $purchase->quantity,
            'status' => $purchase->status,
            'ready_for_pickup' => $purchase->ready_for_pickup,
            'pickup_ready_at' => $purchase->pickup_ready_at,
            'confirmed_at' => $purchase->confirmed_at,
            'purchase_date' => $purchase->purchase_date,
            'dosage' => $purchase->dosage,
            'expdate' => $purchase->expdate,
            'lprice' => $purchase->lprice,
            'mprice' => $purchase->mprice,
            'hprice' => $purchase->hprice,
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
}
