<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Purchase;
use App\Models\Medicine;
use Illuminate\Support\Facades\Log;

class CancelExpiredPickups extends Command
{
    protected $signature = 'purchases:cancel-expired';
    protected $description = 'Cancel purchases that have exceeded their pickup deadline';

    public function handle()
    {
        $expiredPurchases = Purchase::where('ready_for_pickup', true)
            ->whereNotNull('pickup_deadline')
            ->where('pickup_deadline', '<', now())
            ->get();

        foreach ($expiredPurchases as $purchase) {
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

        $this->info("Cancelled {$expiredPurchases->count()} expired pickups.");
    }
} 