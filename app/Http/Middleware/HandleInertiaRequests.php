<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tightenco\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Defines the props that are shared by default.
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            // Authentication
            'auth' => [
                'user' => $request->user(),
                'admin' => $request->user('admin'),
                'isAdmin' => $request->user('admin') !== null,
            ],

            // Flash Messages
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],

            // Ziggy Routes
            'ziggy' => function () use ($request) {
                return array_merge((new Ziggy)->toArray(), [
                    'location' => $request->url(),
                    'previous' => url()->previous(),
                ]);
            },

            // App Settings
            'app' => [
                'name' => config('app.name'),
                'env' => app()->environment(),
                'debug' => config('app.debug'),
            ],

            // CSRF Token
            'csrf_token' => csrf_token(),
        ]);
    }
}
