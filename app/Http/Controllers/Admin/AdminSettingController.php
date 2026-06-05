<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\Request;

class AdminSettingController extends Controller
{
    public function index()
    {
        return \Inertia\Inertia::render('Admin/Settings/Index', [
            'settings' => [
                'active_semester' => AppSetting::get('active_semester', '1st Semester'),
                'active_school_year' => AppSetting::get('active_school_year', '2026-2027'),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'active_semester' => ['required', 'string', 'in:1st Semester,2nd Semester,Summer'],
            'active_school_year' => ['required', 'string', 'max:255'],
        ]);

        AppSetting::set('active_semester', $validated['active_semester']);
        AppSetting::set('active_school_year', $validated['active_school_year']);

        return back()->with('success', 'Academic term updated successfully. The clearance cycle has been reset for the new term.');
    }
}
