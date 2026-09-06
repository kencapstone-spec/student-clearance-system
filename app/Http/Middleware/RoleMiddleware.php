<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * This middleware checks if the logged-in user's role
     * is allowed to access the route.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        if (! in_array($user->role, $roles)) {
            if ($request->wantsJson() && ! $request->header('X-Inertia')) {
                return response()->json(['message' => 'Unauthorized access.'], 403);
            }

            $redirectRoute = match ($user->role) {
                'admin' => 'admin.dashboard',
                'president' => 'president.final-approvals.index',
                'staff' => 'staff.pending-requests.index',
                default => 'dashboard',
            };

            return redirect()->route($redirectRoute)->with('error', 'You are not authorized to access that area.');
        }

        return $next($request);
    }
}
