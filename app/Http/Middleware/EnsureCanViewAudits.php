<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCanViewAudits
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->can('list-auditlog')) {
            return $next($request);
        }

        abort(403, 'Unauthorized to view audits');
    }
}
