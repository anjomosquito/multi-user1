<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all(['id', 'name', 'email']);
        return Inertia::render('Admin/User/Index', [
            'users' => $users,
        ]);
    }
}
