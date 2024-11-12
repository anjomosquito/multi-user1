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
        try {
            // Retrieve purchases with eager loading
            $purchases = Purchase::with(['user', 'medicine'])
                ->latest('created_at')
                ->get()
                ->groupBy('user_id')
                ->map(function ($userPurchases) {
                    $user = $userPurchases->first()->user;
                    return [
                        'name' => $user->name,
                        'purchases' => $userPurchases->map(function ($purchase) {
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
                                'user' => [
                                    'id' => $purchase->user->id,
                                    'name' => $purchase->user->name,
                                ],
                                'medicine' => $purchase->medicine ? [
                                    'id' => $purchase->medicine->id,
                                    'name' => $purchase->medicine->name,
                                ] : null,
                            ];
                        })->values()
                    ];
                });

            Log::info('Purchases index loaded successfully', [
                'admin_id' => auth('admin')->id(),
                'purchase_count' => $purchases->sum(function ($group) {
                    return count($group['purchases']);
                })
            ]);

            return Inertia::render('Admin/Purchase/Index', [
                'purchases' => $purchases,
                'filters' => request()->all(['search', 'status', 'date']),
            ]);

        } catch (ModelNotFoundException $e) {
            Log::error('Model not found in purchases index', [
                'error' => $e->getMessage(),
                'admin_id' => auth('admin')->id()
            ]);

            return back()->with('error', 'Required data not found. Please try again.');

        } catch (\Exception $e) {
            Log::error('Failed to load purchases index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'admin_id' => auth('admin')->id()
            ]);

            $errorMessage = app()->environment('local') 
                ? 'Failed to load purchases: ' . $e->getMessage()
                : 'Failed to load purchases. Please try again.';

            return back()->with('error', $errorMessage);
        }
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

            // Log successful confirmation
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

        } catch (\Exception $e) {
            Log::error('Failed to confirm purchase', [
                'purchase_id' => $id,
                'error' => $e->getMessage(),
                'admin_id' => auth('admin')->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // In production, you might want to hide the actual error message
            $errorMessage = app()->environment('local') 
                ? 'Failed to confirm purchase: ' . $e->getMessage()
                : 'Failed to confirm purchase. Please try again.';
            
            return back()->with('error', $errorMessage);
        }
    }

    public function markAsReady($id)
    {
        try {
            $purchase = Purchase::findOrFail($id);
            
            // Check if confirmed first
            if ($purchase->status !== 'confirmed') {
                Log::warning('Attempted to mark unconfirmed purchase as ready', [
                    'purchase_id' => $id,
                    'admin_id' => auth('admin')->id()
                ]);
                return back()->with('error', 'Purchase must be confirmed first');
            }

            if ($purchase->ready_for_pickup) {
                Log::warning('Attempted to mark already ready purchase', [
                    'purchase_id' => $id,
                    'admin_id' => auth('admin')->id()
                ]);
                return back()->with('error', 'Purchase is already marked as ready');
            }

            \DB::transaction(function () use ($purchase) {
                $purchase->update([
                    'ready_for_pickup' => true,
                    'pickup_ready_at' => now()
                ]);
            });

            Log::info('Purchase marked as ready for pickup', [
                'purchase_id' => $purchase->id,
                'marked_by' => auth('admin')->id(),
                'marked_at' => now()->toDateTimeString()
            ]);

            return back()->with('success', 'Purchase marked as ready for pickup');

        } catch (ModelNotFoundException $e) {
            Log::error('Purchase not found for marking ready', [
                'purchase_id' => $id,
                'admin_id' => auth('admin')->id()
            ]);
            
            return back()->with('error', 'Purchase not found');

        } catch (\Exception $e) {
            Log::error('Failed to mark purchase as ready', [
                'purchase_id' => $id,
                'error' => $e->getMessage(),
                'admin_id' => auth('admin')->id(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $errorMessage = app()->environment('local') 
                ? 'Failed to mark purchase as ready: ' . $e->getMessage()
                : 'Failed to mark purchase as ready. Please try again.';
            
            return back()->with('error', $errorMessage);
        }
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
}
