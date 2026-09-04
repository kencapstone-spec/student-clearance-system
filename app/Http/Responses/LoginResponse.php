<?php

namespace App\Http\Responses;

use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  Request  $request
     */
    public function toResponse($request): Response
    {
        $user = $request->user();

        $defaultRoute = match ($user?->role) {
            'admin' => route('admin.dashboard'),
            'president' => route('president.final-approvals.index'),
            'staff' => route('staff.pending-requests.index'),
            default => route('dashboard'),
        };

        if ($request->wantsJson()) {
            return response()->json(['two_factor' => false]);
        }

        // Validate intended URL against user role to prevent 403 Forbidden redirects
        $intendedUrl = $request->session()->get('url.intended');

        if ($intendedUrl && $user) {
            $path = parse_url($intendedUrl, PHP_URL_PATH) ?? '';

            $isUnauthorizedIntended = match ($user->role) {
                'student' => str_starts_with($path, '/admin') || str_starts_with($path, '/staff') || str_starts_with($path, '/president'),
                'staff' => str_starts_with($path, '/admin') || str_starts_with($path, '/president') || str_starts_with($path, '/student'),
                'president' => str_starts_with($path, '/admin') || str_starts_with($path, '/staff') || str_starts_with($path, '/student'),
                default => false,
            };

            if ($isUnauthorizedIntended) {
                $request->session()->forget('url.intended');

                return redirect()->to($defaultRoute);
            }
        }

        return redirect()->intended($defaultRoute);
    }
}
