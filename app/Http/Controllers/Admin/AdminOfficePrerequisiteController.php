<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Office;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminOfficePrerequisiteController extends Controller
{
    public function index()
    {
        $offices = Office::with('prerequisites')
            ->where('is_final_approver', false)
            ->orderBy('sort_order', 'asc')
            ->get()
            ->sortBy(function ($office) {
                return $office->prerequisites->count();
            })
            ->values();

        return Inertia::render('Admin/OfficePrerequisites/Index', [
            'offices' => $offices,
        ]);
    }

    public function update(Request $request, Office $office)
    {
        $validated = $request->validate([
            'prerequisite_office_ids' => ['array'],
            'prerequisite_office_ids.*' => ['exists:offices,id'],
        ]);

        $office->prerequisites()->sync($validated['prerequisite_office_ids'] ?? []);

        return back()->with('success', 'Office prerequisites updated successfully.');
    }
}
