<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $users = User::select(['id', 'name', 'email', 'created_at'])->get();
        return Inertia::render('Admin/User/Index', [
            'users' => $users,
        ]);
    }
}
