<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Laragear\TwoFactor\Facades\Auth2FA;


class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate credentials ONLY on first step
        if ($request->isNotFilled('2fa_code')) {
            $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required'],
            ]);
        }

        if ($request->filled('2fa_code') && !session()->has('_2fa_login')) {
            return redirect()->route('login')->withErrors([
                'email' => 'Session expired. Please login again.'
            ]);
        }


        if (! Auth2FA::attempt(
            $request->only('email', 'password'),
            $request->boolean('remember')
        )) {
            return back()->withErrors([
                'email' => __('auth.failed'),
            ]);
        }

        // Login successful (no 2FA OR OTP verified)
        $request->session()->regenerate();

        return redirect()->intended(route('masterapp.dashboard'));
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
}
