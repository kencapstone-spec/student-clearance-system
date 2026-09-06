<?php

namespace App\Http\Middleware;

use App\Models\AppSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
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

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $user,
            ],
            'studentClearance' => Inertia::always(function () use ($request) {
                $user = $request->user();

                if (! $user || $user->role !== 'student') {
                    return null;
                }

                $activeSemester = AppSetting::get('active_semester', '1st Semester');
                $activeSchoolYear = AppSetting::get('active_school_year', '2026-2027');

                $latestClearance = $user->clearanceRequests()
                    ->where('semester', $activeSemester)
                    ->where('school_year', $activeSchoolYear)
                    ->latest()
                    ->first(['id', 'status']);

                if (! $latestClearance) {
                    return null;
                }

                return [
                    'id' => $latestClearance->id,
                    'is_cleared' => $latestClearance->status === 'cleared',
                ];
            }),
            'notifications' => Inertia::always(function () use ($request) {
                $user = $request->user();

                if (! $user) {
                    return [
                        'items' => [],
                        'unread_count' => 0,
                    ];
                }

                return [
                    'items' => $user->notifications()
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
                        ->values(),
                    'unread_count' => $user->notifications()
                        ->whereNull('read_at')
                        ->count(),
                ];
            }),
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
