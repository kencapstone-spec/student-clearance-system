<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Office;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class AdminUserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Users/Index', [
            'users' => User::with(['course', 'office'])
                ->orderBy('role')
                ->orderBy('name')
                ->get(),

            'offices' => Office::orderBy('sort_order')
                ->get(['id', 'name', 'group']),

            'courses' => Course::orderBy('code')
                ->get(['id', 'code', 'name']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Users/Create', [
            'offices' => Office::orderBy('sort_order')
                ->get(['id', 'name', 'group']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'student_id' => ['required', 'string', 'max:255', 'unique:users,student_id'],
            'office_id' => ['required', 'exists:offices,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        User::create([
            'name' => $validated['name'],
            'student_id' => $validated['student_id'],
            'role' => 'staff',
            'office_id' => $validated['office_id'],
            'course_id' => null,
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Staff account created successfully.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'role' => [
                'required',
                Rule::in(['student', 'staff', 'admin', 'president']),
            ],

            'course_id' => [
                'nullable',
                'required_if:role,student',
                'exists:courses,id',
            ],

            'office_id' => [
                'nullable',
                'required_if:role,staff,admin,president',
                'exists:offices,id',
            ],

            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $role = $validated['role'];

        $updateData = [
            'name' => $validated['name'],
            'role' => $role,
            'course_id' => $role === 'student'
                ? $validated['course_id']
                : null,
            'office_id' => $role === 'student'
                ? null
                : $validated['office_id'],
        ];

        if (! empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User account updated successfully.');
    }

    public function toggleActive(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->id === $user->id) {
            return back()->withErrors([
                'user' => 'You cannot deactivate your own account.',
            ]);
        }

        if ($user->is_active) {
            $user->update([
                'is_active' => false,
                'deactivated_at' => now(),
            ]);

            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User account deactivated successfully.');
        }

        $user->update([
            'is_active' => true,
            'deactivated_at' => null,
        ]);

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User account activated successfully.');
    }
}