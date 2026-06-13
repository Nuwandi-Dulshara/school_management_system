<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    private const ROLES = [
        'super_admin' => 'Super Admin',
        'admin' => 'Admin / Office Staff',
        'teacher' => 'Teacher',
        'student' => 'Student',
    ];

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::in(array_keys(self::ROLES))],
        ]);

        $users = User::query()
            ->when($validated['search'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('username', 'like', "%{$search}%");
                });
            })
            ->when($validated['role'] ?? null, fn ($query, string $role) => $query->where('role', $role))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('super_admin.users.index', [
            'users' => $users,
            'roles' => self::ROLES,
        ]);
    }

    public function create(): View
    {
        return view('super_admin.users.create', ['roles' => self::ROLES]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->userRules());

        User::create($validated);

        return redirect()
            ->route('super_admin.users.index')
            ->with('success', 'User account created successfully.');
    }

    public function show(User $user): View
    {
        return view('super_admin.users.show', [
            'managedUser' => $user,
            'roles' => self::ROLES,
        ]);
    }

    public function edit(User $user): View
    {
        return view('super_admin.users.edit', [
            'managedUser' => $user,
            'roles' => self::ROLES,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate($this->userRules($user));

        $user->update($validated);

        return redirect()
            ->route('super_admin.users.show', $user)
            ->with('success', 'User details updated successfully.');
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        $user->update([
            'status' => $user->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', "User account marked as {$user->status}.");
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update(['password' => $validated['password']]);

        return back()->with('success', 'User password reset successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()
            ->route('super_admin.users.index')
            ->with('success', 'User account deleted successfully.');
    }

    public function roles(): View
    {
        $roles = [
            [
                'name' => self::ROLES['super_admin'],
                'key' => 'super_admin',
                'description' => 'Full system control',
                'icon' => 'bi-shield-lock-fill',
            ],
            [
                'name' => self::ROLES['admin'],
                'key' => 'admin',
                'description' => 'Handles students, teachers, classes, fees, attendance, and reports',
                'icon' => 'bi-person-workspace',
            ],
            [
                'name' => self::ROLES['teacher'],
                'key' => 'teacher',
                'description' => 'Manages assigned classes, attendance, marks, and student performance',
                'icon' => 'bi-person-video3',
            ],
            [
                'name' => self::ROLES['student'],
                'key' => 'student',
                'description' => 'Views profile, attendance, exam marks, notices, and fee status',
                'icon' => 'bi-backpack-fill',
            ],
        ];

        return view('super_admin.roles.index', compact('roles'));
    }

    private function userRules(?User $user = null): array
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user)],
            'role' => ['required', Rule::in(array_keys(self::ROLES))],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];

        if ($user === null) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        return $rules;
    }
}
