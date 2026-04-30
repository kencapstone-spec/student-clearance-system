<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        $notifications = [];
        $unreadNotificationCount = 0;

        if ($user) {
            $notifications = $user->notifications()
                ->latest()
                ->take(10)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'link' => $notification->link,
                        'read_at' => $notification->read_at?->toDateTimeString(),
                        'created_at' => $notification->created_at?->toDateTimeString(),
                        'created_at_human' => $notification->created_at?->diffForHumans(),
                    ];
                })
                ->values();

            $unreadNotificationCount = $user->notifications()
                ->whereNull('read_at')
                ->count();
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
            ],
            'notifications' => [
                'items' => $notifications,
                'unread_count' => $unreadNotificationCount,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}