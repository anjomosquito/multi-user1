<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        $users = User::with(['chats' => function ($query) {
                $query->latest()->take(1);
            }])
            ->withCount(['chats as unread_count' => function ($query) {
                $query->where('is_read', false)
                    ->where('is_admin', false);
            }])
            ->orderByDesc('unread_count')
            ->orderByDesc(function ($query) {
                $query->select('created_at')
                    ->from('chats')
                    ->whereColumn('user_id', 'users.id')
                    ->latest()
                    ->limit(1);
            })
            ->get();

        return Inertia::render('Admin/Chat/Index', [
            'users' => $users,
            'totalUnread' => Chat::where('is_admin', false)
                ->where('is_read', false)
                ->count()
        ]);
    }

    public function show($userId)
    {
        $user = User::findOrFail($userId);
        $chats = Chat::where('user_id', $userId)
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read
        Chat::where('user_id', $userId)
            ->where('is_admin', false)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return Inertia::render('Admin/Chat/Show', [
            'user' => $user,
            'chats' => $chats
        ]);
    }

    public function store(Request $request, $userId)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        Chat::create([
            'user_id' => $userId,
            'admin_id' => Auth::guard('admin')->id(),
            'message' => $request->message,
            'is_admin' => true
        ]);

        return back();
    }
} 