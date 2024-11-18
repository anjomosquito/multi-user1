<?php

namespace App\Observers;

use App\Models\Medicine;
use Spatie\Activitylog\Facades\LogActivity;

class MedicineObserver
{
    public function updated(Medicine $medicine)
    {
        if ($medicine->isDirty('quantity')) {
            $oldQuantity = $medicine->getOriginal('quantity');
            $newQuantity = $medicine->quantity;
            $difference = $newQuantity - $oldQuantity;

            $action = $difference > 0 ? 'restocked' : 'reduced';
            $amount = abs($difference);

            activity()
                ->performedOn($medicine)
                ->log("Medicine {$medicine->name} {$action} by {$amount} units");
        }

        if ($medicine->isDirty('price')) {
            $oldPrice = $medicine->getOriginal('price');
            $newPrice = $medicine->price;
            
            activity()
                ->performedOn($medicine)
                ->withProperties([
                    'old_price' => $oldPrice,
                    'new_price' => $newPrice
                ])
                ->log("Price updated for {$medicine->name}");
        }
    }
}
