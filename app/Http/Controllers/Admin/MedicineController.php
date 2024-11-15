<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\Medicine;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;

class MedicineController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        try {
            $query = Medicine::query();
            
            if ($request->has('search')) {
                $query->search($request->search);
            }
            
            $medicines = $query->latest()->paginate(10);
            
            return Inertia::render('Admin/Medicines/Index', [
                'medicines' => $medicines->items() ?? [],
                'filters' => $request->only(['search'])
            ]);
        } catch (\Exception $e) {
            return Inertia::render('Admin/Medicines/Index', [
                'medicines' => [],
                'filters' => $request->only(['search']),
                'error' => 'Failed to load medicines'
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('Admin/Medicines/Create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        Medicine::create([
            'name' => $request->name,
            'lprice' => $request->lprice,
            'mprice' => $request->mprice,
            'hprice' => $request->hprice,
            'quantity' => $request->quantity,
            'dosage' => $request->dosage,
            'expdate' => $request->expdate,
        ]);

        return redirect()->route('admin.medicines.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Medicine  $medicine
     * @return \Inertia\Response
     */
    public function edit(Medicine $medicine)
    {
        return Inertia::render('Admin/Medicines/Edit', ['medicines' => $medicine]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Medicine  $medicine
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Medicine $medicine)
    {
        $request->validate([
            'name' => ['required', 'string'],
            'lprice' => ['required', 'string'],
            'mprice' => ['required', 'string'],
            'hprice' => ['required', 'string'],
            'quantity' => ['required', 'string'],
            'dosage' => ['required', 'string'],
            'expdate' => ['required', 'date_format:Y-m-d'],
        ]);

        $medicine->update($request->all());

        return Redirect::route('admin.medicines.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Medicine  $medicine
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Medicine $medicine)
    {
        $medicine->delete();
        return Redirect::route('admin.medicines.index')->with('success', 'Medicine has been deleted.');
    }
}