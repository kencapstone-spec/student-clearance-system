<?php

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function open(Request $request, UserNotification $notification): RedirectResponse
    {
        if ($notification->user_id !== $request->user()->id) {
            abort(403);
        }

        if (! $notification->read_at) {
            $notification->update([
                'read_at' => now(),
            ]);
        }

        return redirect($notification->link ?: route('dashboard'));
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()
            ->notifications()
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        return back()->with('success', 'All notifications marked as read.');
    }
}