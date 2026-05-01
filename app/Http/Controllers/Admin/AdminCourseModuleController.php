<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Office;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminCourseModuleController extends Controller
{
    public function index(): Response
    {
        $courses = Course::query()
            ->with([
                'offices' => function ($query) {
                    $query->orderBy('sort_order');
                },
            ])
            ->orderBy('code')
            ->get()
            ->map(function (Course $course) {
                return [
                    'id' => $course->id,
                    'code' => $course->code,
                    'name' => $course->name,
                    'offices' => $course->offices->map(function ($office) {
                        return [
                            'id' => $office->id,
                            'name' => $office->name,
                            'group' => $office->group,
                            'sort_order' => $office->sort_order,
                            'is_final_approver' => $office->is_final_approver,
                        ];
                    }),
                ];
            });

        $offices = Office::query()
            ->where('is_final_approver', false)
            ->orderBy('sort_order')
            ->get()
            ->map(function (Office $office) {
                return [
                    'id' => $office->id,
                    'name' => $office->name,
                    'group' => $office->group,
                    'sort_order' => $office->sort_order,
                    'is_final_approver' => $office->is_final_approver,
                ];
            });

        return Inertia::render('Admin/CourseModules/Index', [
            'courses' => $courses,
            'offices' => $offices,
        ]);
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'office_ids' => ['required', 'array', 'min:1'],
            'office_ids.*' => [
                'integer',
                Rule::exists('offices', 'id')->where(function ($query) {
                    $query->where('is_final_approver', false);
                }),
            ],
        ]);

        $course->offices()->sync($validated['office_ids']);

        return back()->with('success', 'Course module office assignments updated successfully.');
    }
}