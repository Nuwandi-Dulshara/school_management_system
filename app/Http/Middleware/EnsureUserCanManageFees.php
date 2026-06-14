<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserCanManageFees
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(in_array($request->user()?->role, ['super_admin', 'admin'], true), 403);

        return $next($request);
    }
}
