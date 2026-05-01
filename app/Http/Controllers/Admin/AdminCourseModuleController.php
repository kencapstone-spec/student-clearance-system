<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
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

        return Inertia::render('Admin/CourseModules/Index', [
            'courses' => $courses,
        ]);
    }
}