<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Super Admin always allowed
            if ($user->hasRole('Super Admin')) {
                return $next($request);
            }

            // Block inactive users
            if (! $user->active) {
                Auth::logout();

                return redirect()->route('login.view')
                    ->withErrors(['email' => 'Your account is inactive. Contact administrator.']);
            }
        }
        return $next($request);
    }
}
