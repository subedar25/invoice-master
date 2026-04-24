<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSystemUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ($user->user_type ?? '') !== 'systemuser') {
            abort(403, 'Only system users can access this module.');
        }

        return $next($request);
    }
}

