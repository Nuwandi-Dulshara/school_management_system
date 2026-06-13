<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        // Find user by email or username
        $user = User::where('email', $validated['login'])
            ->orWhere('username', $validated['login'])
            ->first();

        // Check if user exists
        if (!$user) {
            return back()
                ->withInput($request->only('login'))
                ->with('error', 'Invalid login credentials.');
        }

        // Check if password is correct
        if (!Hash::check($validated['password'], $user->password)) {
            return back()
                ->withInput($request->only('login'))
                ->with('error', 'Invalid login credentials.');
        }

        // Log the user in
        Auth::login($user, $request->boolean('remember'));

        // Redirect to role-based dashboard
        $dashboardRoute = match($user->role) {
            'super_admin' => 'dashboard.super_admin',
            'admin' => 'dashboard.admin',
            'teacher' => 'dashboard.teacher',
            'student' => 'dashboard.student',
            default => null,
        };

        // If role is invalid, logout and redirect with error
        if ($dashboardRoute === null) {
            Auth::logout();
            return redirect()->route('login')->with('error', 'Invalid user role.');
        }

        return redirect()->route($dashboardRoute)->with('success', 'Welcome back!');
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();
        return redirect()->route('login')->with('success', 'You have been logged out successfully.');
    }
}
