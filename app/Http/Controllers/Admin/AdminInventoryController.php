<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\MedicineCategory;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use League\Csv\Writer;

class AdminInventoryController extends Controller
{
    public function index()
    {
        // Update medicine statuses based on quantity and expiry date
        $this->updateMedicineStatuses();
        
        $medicines = Medicine::with('category')->get();
        $categories = MedicineCategory::all();
        $logs = InventoryLog::with(['medicine', 'admin'])
            ->latest()
            ->take(50)
            ->get();

        return Inertia::render('Admin/Inventory/Index', [
            'medicines' => $medicines,
            'categories' => $categories,
            'logs' => $logs
        ]);
    }

    private function updateMedicineStatuses()
    {
        $today = Carbon::now();
        
        // Get all medicines
        $medicines = Medicine::all();
        
        foreach ($medicines as $medicine) {
            $shouldDisable = false;
            $statusReason = '';
            
            // Check quantity
            if ($medicine->quantity <= 0) {
                $shouldDisable = true;
                $statusReason = 'Out of stock';
            }
            
            // Check expiry date
            if (!$shouldDisable && Carbon::parse($medicine->expdate)->lte($today)) {
                $shouldDisable = true;
                $statusReason = 'Expired';
            }
            
            // Update status if needed
            if ($shouldDisable && $medicine->status !== 'disabled') {
                $medicine->status = 'disabled';
                $medicine->status_reason = $statusReason;
                $medicine->save();
            } elseif (!$shouldDisable && $medicine->status === 'disabled' && 
                     ($medicine->status_reason === 'Out of stock' || $medicine->status_reason === 'Expired')) {
                $medicine->status = 'active';
                $medicine->status_reason = null;
                $medicine->save();
            }
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'lprice' => 'required|numeric|min:0',
            'mprice' => 'required|numeric|min:0',
            'hprice' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'dosage' => 'required|string',
            'expdate' => 'required|date',
            'category_id' => 'nullable|exists:medicine_categories,id'
        ]);

        // Set initial status based on quantity and expiry date
        $today = Carbon::now();
        $expDate = Carbon::parse($validated['expdate']);
        
        if ($validated['quantity'] <= 0) {
            $validated['status'] = 'disabled';
            $validated['status_reason'] = 'Out of stock';
        } elseif ($expDate->lte($today)) {
            $validated['status'] = 'disabled';
            $validated['status_reason'] = 'Expired';
        } else {
            $validated['status'] = 'active';
        }

        $medicine = Medicine::create($validated);
        if (isset($validated['category_id'])) {
            $medicine->category()->associate(MedicineCategory::find($validated['category_id']));
            $medicine->save();
        }

        // Log the addition
        InventoryLog::create([
            'medicine_id' => $medicine->id,
            'admin_id' => Auth::id(),
            'action_type' => 'add',
            'quantity_change' => $validated['quantity'],
            'new_quantity' => $validated['quantity'],
            'new_price' => $validated['lprice'],
            'notes' => 'Initial medicine addition'
        ]);

        return redirect()->back()->with('success', 'Medicine added successfully');
    }

    public function update(Request $request, Medicine $medicine)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'lprice' => 'required|numeric|min:0',
            'mprice' => 'required|numeric|min:0',
            'hprice' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'dosage' => 'required|string',
            'expdate' => 'required|date',
            'category_id' => 'nullable|exists:medicine_categories,id'
        ]);

        // Update status based on new quantity and expiry date
        $today = Carbon::now();
        $expDate = Carbon::parse($validated['expdate']);
        
        if ($validated['quantity'] <= 0) {
            $validated['status'] = 'disabled';
            $validated['status_reason'] = 'Out of stock';
        } elseif ($expDate->lte($today)) {
            $validated['status'] = 'disabled';
            $validated['status_reason'] = 'Expired';
        } else {
            $validated['status'] = 'active';
            $validated['status_reason'] = null;
        }

        $oldQuantity = $medicine->quantity;
        $oldPrice = $medicine->lprice;
        $oldStatus = $medicine->status;

        $medicine->update($validated);
        if (isset($validated['category_id'])) {
            $medicine->category()->associate(MedicineCategory::find($validated['category_id']));
            $medicine->save();
        } elseif ($medicine->category) {
            $medicine->category()->dissociate();
            $medicine->save();
        }

        // Log the update
        InventoryLog::create([
            'medicine_id' => $medicine->id,
            'admin_id' => Auth::id(),
            'action_type' => 'update',
            'quantity_change' => $validated['quantity'] - $oldQuantity,
            'old_quantity' => $oldQuantity,
            'new_quantity' => $validated['quantity'],
            'old_price' => $oldPrice,
            'new_price' => $validated['lprice'],
            'old_status' => $oldStatus,
            'new_status' => $medicine->status,
            'notes' => 'Medicine details updated'
        ]);

        return redirect()->back()->with('success', 'Medicine updated successfully');
    }

    public function destroy(Medicine $medicine)
    {
        // Log the deletion
        InventoryLog::create([
            'medicine_id' => $medicine->id,
            'admin_id' => Auth::id(),
            'action_type' => 'delete',
            'old_quantity' => $medicine->quantity,
            'old_price' => $medicine->lprice,
            'old_status' => $medicine->status,
            'notes' => 'Medicine deleted from inventory'
        ]);

        $medicine->delete();

        return redirect()->back()->with('success', 'Medicine deleted successfully');
    }

    public function toggleStatus(Medicine $medicine)
    {
        // Only allow manual status toggle if medicine is not out of stock or expired
        if ($medicine->quantity > 0 && Carbon::parse($medicine->expdate)->gt(Carbon::now())) {
            $medicine->status = $medicine->status === 'active' ? 'disabled' : 'active';
            $medicine->status_reason = $medicine->status === 'disabled' ? 'Manually disabled' : null;
            $medicine->save();
            return redirect()->back()->with('success', 'Medicine status updated successfully');
        }

        $reason = $medicine->quantity <= 0 ? 'out of stock' : 'expired';
        return redirect()->back()->with('error', "Cannot enable medicine that is {$reason}");
    }

    public function getInventoryReport(Request $request)
    {
        $query = InventoryLog::with(['medicine', 'admin']);

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        $logs = $query->latest()->get();

        return Inertia::render('Admin/Inventory/Report', [
            'logs' => $logs,
            'filters' => $request->only(['start_date', 'end_date', 'action_type'])
        ]);
    }

    public function downloadReport(Request $request)
    {
        $query = InventoryLog::with(['medicine', 'admin']);

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('action_type')) {
            $query->where('action_type', $request->action_type);
        }

        $logs = $query->latest()->get();

        $csv = Writer::createFromFileObject(new \SplTempFileObject());
        $csv->insertOne(['Date', 'Medicine', 'Action', 'Quantity Change', 'Old Quantity', 'New Quantity', 'Old Price', 'New Price', 'Status Change', 'Admin', 'Notes']);

        foreach ($logs as $log) {
            $csv->insertOne([
                $log->created_at->format('Y-m-d H:i:s'),
                $log->medicine->name,
                $log->action_type,
                $log->quantity_change,
                $log->old_quantity,
                $log->new_quantity,
                $log->old_price,
                $log->new_price,
                $log->old_status . ' → ' . $log->new_status,
                $log->admin->name,
                $log->notes
            ]);
        }

        return response($csv->getContent())
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="inventory-report.csv"');
    }
}