<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::with('admin')
            ->published()
            ->recent()
            ->paginate(10);

        return Inertia::render('Announcements/Index', [
            'announcements' => $announcements
        ]);
    }

    public function show(Announcement $announcement)
    {
        if ($announcement->status !== 'published') {
            abort(404);
        }

        return Inertia::render('Announcements/Show', [
            'announcement' => $announcement->load('admin')
        ]);
    }
}
