<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        if ($request->user()->isSuperAdmin() || $request->user()->hasPermission($permission)) {
            return $next($request);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => "Unauthorized access: You do not possess the required permission [{$permission}]."
            ], 403);
        }

        abort(403, "Unauthorized Action: You lack the '{$permission}' permission required to perform this action.");
    }
}
