<?php

namespace App\Http\Controllers\Account\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        // Указываем Inertia использовать другой Blade-шаблон
        Inertia::setRootView('guest');

        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('account.password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = $request->user();
        if ($user->isAdmin() || $user->isManager() || $user->isSeo()) {
            return Inertia::location(route('admin.index'));
        }

        if ($user->isCustomer()) {
            // A full page load, otherwise the account would keep the "guest" Blade shell of the login page
            return Inertia::location($request->session()->pull('url.intended', route('account.index')));
        }

        return Inertia::location(route('home'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroyFromAccount(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return Inertia::location(route('home'));
    }
}
