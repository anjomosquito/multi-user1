<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Medicine;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;

class AdminInventoryController extends Controller
{
    public function index()
    {
        // You can pass data to the Vue component if needed, for example:
        $medicines = Medicine::all(); // Fetch inventory data from the database

        return Inertia::render('Admin/Inventory/Index', [
            'medicines' => $medicines // Pass inventory data to the Vue component
        ]);
    }
}