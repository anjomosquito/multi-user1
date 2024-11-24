<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = Activity::with('causer')->latest();

        // Filter by log name if provided
        if ($request->has('log_name') && $request->log_name !== '') {
            $query->where('log_name', $request->log_name);
        }

        // Filter by date range if provided
        if ($request->has('start_date') && $request->start_date !== '') {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date !== '') {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Search in description
        if ($request->has('search') && $request->search !== '') {
            $query->where('description', 'like', "%{$request->search}%");
        }

        $activities = $query->paginate(15)->withQueryString();

        // Get unique log names for the filter dropdown
        $logNames = Activity::distinct()->pluck('log_name');

        return Inertia::render('Admin/ActivityLog/Index', [
            'activities' => $activities,
            'logNames' => $logNames,
            'filters' => $request->only(['search', 'log_name', 'start_date', 'end_date'])
        ]);
    }
}
