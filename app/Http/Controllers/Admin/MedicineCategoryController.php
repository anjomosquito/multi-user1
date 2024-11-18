<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MedicineCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MedicineCategoryController extends Controller
{
    public function index()
    {
        $categories = MedicineCategory::withCount('medicines')->get();
        return Inertia::render('Admin/Category/Index', [
            'categories' => $categories
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:medicine_categories',
            'description' => 'nullable|string|max:1000',
        ]);

        MedicineCategory::create($validated);

        return redirect()->back()->with('success', 'Category added successfully');
    }

    public function update(Request $request, MedicineCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:medicine_categories,name,' . $category->id,
            'description' => 'nullable|string|max:1000',
        ]);

        $category->update($validated);

        return redirect()->back()->with('success', 'Category updated successfully');
    }

    public function destroy(MedicineCategory $category)
    {
        $category->delete();
        return redirect()->back()->with('success', 'Category deleted successfully');
    }
}
