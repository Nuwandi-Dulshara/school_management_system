<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('profile.index', [
            'user' => $user,
            'teacher' => $user->role === 'teacher' ? $this->teacherFor($user->email) : null,
            'student' => $user->role === 'student' ? $this->studentFor($user->email) : null,
        ]);
    }

    public function edit(Request $request): View
    {
        return view('profile.edit', ['user' => $request->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $emailRules = ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)];
        if ($user->role === 'teacher') {
            $emailRules[] = Rule::unique('teachers')->ignore($this->teacherFor($user->email));
        } elseif ($user->role === 'student') {
            $emailRules[] = Rule::unique('students')->ignore($this->studentFor($user->email));
        }

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user)],
            'email' => $emailRules,
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        [$firstName, $lastName] = $this->splitName($validated['full_name']);
        $oldEmail = $user->email;
        $oldPicture = $user->profile_picture;
        $newPicture = $request->file('profile_picture')?->store('profile-pictures', 'public');

        DB::transaction(function () use ($user, $validated, $firstName, $lastName, $oldEmail, $newPicture) {
            $user->update([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'username' => $validated['username'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'profile_picture' => $newPicture ?? $user->profile_picture,
            ]);

            $linkedValues = [
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'contact_number' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
            ];

            if ($user->role === 'teacher') {
                Teacher::query()->where('email', $oldEmail)->update($linkedValues);
            } elseif ($user->role === 'student') {
                Student::query()->where('email', $oldEmail)->update($linkedValues);
            }
        });

        if ($newPicture && $oldPicture) {
            Storage::disk('public')->delete($oldPicture);
        }

        return redirect()->route('profile.index')->with('success', 'Profile updated successfully.');
    }

    public function changePassword(Request $request): View
    {
        return view('profile.change-password', ['user' => $request->user()]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $request->user()->password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect.']);
        }

        $request->user()->update(['password' => $validated['password']]);

        return redirect()->route('profile.index')->with('success', 'Password changed successfully.');
    }

    private function teacherFor(string $email): ?Teacher
    {
        return Teacher::query()
            ->with(['teachingAssignments.schoolClass', 'teachingAssignments.subject'])
            ->where('email', $email)
            ->first();
    }

    private function studentFor(string $email): ?Student
    {
        return Student::query()
            ->with(['schoolClass', 'schoolSection', 'classAssignments' => fn ($query) => $query->latest('assigned_at')])
            ->where('email', $email)
            ->first();
    }

    private function splitName(string $fullName): array
    {
        $parts = preg_split('/\s+/', trim($fullName), 2);

        return [$parts[0], $parts[1] ?? ''];
    }
}
