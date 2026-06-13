<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\StudentDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentManagementController extends Controller
{
    private const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'transferred' => 'Transferred',
        'graduated' => 'Graduated',
    ];

    private const GENDERS = [
        'male' => 'Male',
        'female' => 'Female',
        'other' => 'Other',
    ];

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'class' => ['nullable', 'string', 'max:50'],
            'section' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', Rule::in(array_keys(self::STATUSES))],
        ]);

        $students = Student::query()
            ->with('guardian')
            ->when($validated['search'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('full_name', 'like', "%{$search}%")
                        ->orWhere('admission_number', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('contact_number', 'like', "%{$search}%");
                });
            })
            ->when($validated['class'] ?? null, fn ($query, string $class) => $query->where('class', $class))
            ->when($validated['section'] ?? null, fn ($query, string $section) => $query->where('section', $section))
            ->when($validated['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('super_admin.students.index', [
            'students' => $students,
            'classes' => Student::query()->select('class')->distinct()->orderBy('class')->pluck('class'),
            'sections' => Student::query()->select('section')->distinct()->orderBy('section')->pluck('section'),
            'statuses' => self::STATUSES,
        ]);
    }

    public function create(): View
    {
        return view('super_admin.students.create', $this->formOptions());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->studentRules());

        $student = DB::transaction(function () use ($validated) {
            $student = Student::create($this->studentData($validated));
            $student->guardian()->create($this->guardianData($validated));

            return $student;
        });

        return redirect()
            ->route('super_admin.students.show', $student)
            ->with('success', 'Student registered successfully.');
    }

    public function show(Student $student): View
    {
        $student->load(['guardian', 'documents']);

        return view('super_admin.students.show', [
            'student' => $student,
            'statuses' => self::STATUSES,
            'genders' => self::GENDERS,
        ]);
    }

    public function edit(Student $student): View
    {
        $student->load('guardian');

        return view('super_admin.students.edit', array_merge(
            $this->formOptions(),
            ['student' => $student],
        ));
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate($this->studentRules($student));

        DB::transaction(function () use ($student, $validated) {
            $student->update($this->studentData($validated));
            $student->guardian()->updateOrCreate([], $this->guardianData($validated));
        });

        return redirect()
            ->route('super_admin.students.show', $student)
            ->with('success', 'Student information updated successfully.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        $student->delete();

        return redirect()
            ->route('super_admin.students.index')
            ->with('success', 'Student record deleted successfully.');
    }

    public function status(Request $request): View
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(array_keys(self::STATUSES))],
        ]);

        $students = Student::query()
            ->when($validated['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderBy('full_name')
            ->paginate(15)
            ->withQueryString();

        $statusCounts = Student::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('super_admin.students.status', [
            'students' => $students,
            'statuses' => self::STATUSES,
            'statusCounts' => $statusCounts,
        ]);
    }

    public function updateStatus(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ]);

        $student->update($validated);

        return back()->with('success', 'Student status updated successfully.');
    }

    public function guardians(Student $student): View
    {
        $student->load('guardian');

        return view('super_admin.students.guardians', compact('student'));
    }

    public function updateGuardian(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate($this->guardianRules());
        $student->guardian()->updateOrCreate([], $validated);

        return back()->with('success', 'Parent or guardian details updated successfully.');
    }

    public function documents(Student $student): View
    {
        $student->load(['documents' => fn ($query) => $query->latest('uploaded_date')]);

        return view('super_admin.students.documents', compact('student'));
    }

    public function storeDocument(Request $request, Student $student): RedirectResponse
    {
        $validated = $request->validate([
            'document_name' => ['required', 'string', 'max:255'],
            'document_type' => ['required', 'string', 'max:100'],
            'uploaded_date' => ['required', 'date'],
        ]);

        $student->documents()->create($validated);

        return back()->with('success', 'Document record added successfully.');
    }

    public function destroyDocument(Student $student, StudentDocument $document): RedirectResponse
    {
        abort_unless($document->student_id === $student->id, 404);

        $document->delete();

        return back()->with('success', 'Document record removed successfully.');
    }

    private function studentRules(?Student $student = null): array
    {
        return array_merge([
            'full_name' => ['required', 'string', 'max:255'],
            'admission_number' => ['required', 'string', 'max:100', Rule::unique('students')->ignore($student)],
            'date_of_birth' => ['required', 'date', 'before:today'],
            'gender' => ['required', Rule::in(array_keys(self::GENDERS))],
            'address' => ['required', 'string', 'max:1000'],
            'contact_number' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'class' => ['required', 'string', 'max:50'],
            'section' => ['required', 'string', 'max:50'],
            'admission_date' => ['required', 'date'],
            'status' => ['required', Rule::in(array_keys(self::STATUSES))],
        ], [
            'guardian_name' => ['required', 'string', 'max:255'],
            'relationship' => ['required', 'string', 'max:100'],
            'guardian_contact_number' => ['required', 'string', 'max:30'],
            'guardian_email' => ['nullable', 'email', 'max:255'],
            'guardian_address' => ['required', 'string', 'max:1000'],
        ]);
    }

    private function guardianRules(): array
    {
        return [
            'guardian_name' => ['required', 'string', 'max:255'],
            'relationship' => ['required', 'string', 'max:100'],
            'contact_number' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
        ];
    }

    private function studentData(array $validated): array
    {
        return collect($validated)
            ->except([
                'guardian_name',
                'relationship',
                'guardian_contact_number',
                'guardian_email',
                'guardian_address',
            ])
            ->all();
    }

    private function guardianData(array $validated): array
    {
        return [
            'guardian_name' => $validated['guardian_name'],
            'relationship' => $validated['relationship'],
            'contact_number' => $validated['guardian_contact_number'],
            'email' => $validated['guardian_email'] ?? null,
            'address' => $validated['guardian_address'],
        ];
    }

    private function formOptions(): array
    {
        return [
            'statuses' => self::STATUSES,
            'genders' => self::GENDERS,
        ];
    }
}
