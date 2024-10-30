<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Message;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Redirect;

class AdminChatController extends Controller
{
    public function index()
    {
        // You can pass data to the Vue component if needed, for example:
        

        return Inertia::render('Admin/Chat/Messaging', [
             // Pass inventory data to the Vue component
        ]);
    }
}
