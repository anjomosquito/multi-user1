<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function restock(Request $request)
    {
        $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'quantity' => 'required|integer|min:1'
        ]);

        $medicine = Medicine::findOrFail($request->medicine_id);
        $oldQuantity = $medicine->quantity;

        DB::transaction(function () use ($medicine, $request, $oldQuantity) {
            // Update medicine quantity
            $medicine->quantity += $request->quantity;
            $medicine->save();

            // Log restock action
            activity('inventory')
                ->performedOn($medicine)
                ->causedBy(auth()->user())
                ->withProperties([
                    'attributes' => ['quantity' => $medicine->quantity],
                    'old' => ['quantity' => $oldQuantity]
                ])
                ->log('Medicine restocked: Added ' . $request->quantity . ' units');
        });

        return back()->with('success', 'Medicine restocked successfully');
    }

    public function discount(Request $request)
    {
        $request->validate([
            'medicine_id' => 'required|exists:medicines,id',
            'discount_percent' => 'required|numeric|min:1|max:99'
        ]);

        $medicine = Medicine::findOrFail($request->medicine_id);

        DB::transaction(function () use ($medicine, $request) {
            // Calculate and apply discount to all price levels
            $oldLPrice = $medicine->lprice;
            $oldMPrice = $medicine->mprice;
            $oldHPrice = $medicine->hprice;

            $discountMultiplier = (100 - $request->discount_percent) / 100;

            $medicine->lprice = round($oldLPrice * $discountMultiplier, 2);
            $medicine->mprice = round($oldMPrice * $discountMultiplier, 2);
            $medicine->hprice = round($oldHPrice * $discountMultiplier, 2);
            $medicine->save();

            // Log discount action
            activity('inventory')
                ->performedOn($medicine)
                ->causedBy(auth()->user())
                ->withProperties([
                    'attributes' => [
                        'lprice' => $medicine->lprice,
                        'mprice' => $medicine->mprice,
                        'hprice' => $medicine->hprice
                    ],
                    'old' => [
                        'lprice' => $oldLPrice,
                        'mprice' => $oldMPrice,
                        'hprice' => $oldHPrice
                    ],
                    'discount_percent' => $request->discount_percent
                ])
                ->log('Medicine discounted by ' . $request->discount_percent . '%');
        });

        return back()->with('success', 'Discount applied successfully');
    }
}
