<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ChatController extends Controller
{
    public function index()
    {
        $chats = Chat::where('user_id', auth()->id())
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark admin messages as read
        Chat::where('user_id', auth()->id())
            ->where('is_admin', true)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return Inertia::render('Chat/Index', [
            'chats' => $chats,
            'unreadCount' => Chat::where('user_id', auth()->id())
                ->where('is_admin', true)
                ->where('is_read', false)
                ->count(),
            'hasActiveChat' => $chats->count() > 0
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        Chat::create([
            'user_id' => auth()->id(),
            'message' => $request->message,
            'is_admin' => false,
            'is_read' => false
        ]);

        return back();
    }

    public function getUnreadCount()
    {
        $count = Chat::where('user_id', auth()->id())
            ->where('is_admin', true)
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    public function destroy($id)
    {
        $chat = Chat::where('user_id', auth()->id())->findOrFail($id);

        // Delete the message
        $chat->delete();

        return response()->json(['message' => 'Message deleted successfully.']);
    }

}