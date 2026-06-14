<?php

namespace App\Http\Controllers;

use App\Models\FeeAssignment;
use App\Models\FeePayment;
use App\Models\FeeType;
use App\Models\SchoolClass;
use App\Models\SchoolSection;
use App\Models\Student;
use App\Services\FeeBalanceService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FeesController extends Controller
{
    public function __construct(private readonly FeeBalanceService $balances) {}

    public function feeTypes(Request $request): View
    {
        $validated = $request->validate(['search' => ['nullable', 'string', 'max:100']]);
        $feeTypes = FeeType::query()
            ->withCount('assignments')
            ->when($validated['search'] ?? null, fn (Builder $query, string $search) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('fees.types.index', [
            'feeTypes' => $feeTypes,
            'frequencies' => FeeType::FREQUENCIES,
            'statuses' => FeeType::STATUSES,
        ]);
    }

    public function createFeeType(): View
    {
        return view('fees.types.create', [
            'frequencies' => FeeType::FREQUENCIES,
            'statuses' => FeeType::STATUSES,
        ]);
    }

    public function storeFeeType(Request $request): RedirectResponse
    {
        FeeType::create($request->validate($this->feeTypeRules()));

        return redirect()->route('fees.types.index')->with('success', 'Fee type created successfully.');
    }

    public function editFeeType(FeeType $feeType): View
    {
        return view('fees.types.edit', [
            'feeType' => $feeType,
            'frequencies' => FeeType::FREQUENCIES,
            'statuses' => FeeType::STATUSES,
        ]);
    }

    public function updateFeeType(Request $request, FeeType $feeType): RedirectResponse
    {
        $feeType->update($request->validate($this->feeTypeRules($feeType)));

        return redirect()->route('fees.types.index')->with('success', 'Fee type updated successfully.');
    }

    public function destroyFeeType(FeeType $feeType): RedirectResponse
    {
        if ($feeType->assignments()->exists()) {
            return back()->with('error', 'Fee types with student assignments cannot be deleted.');
        }

        $feeType->delete();

        return back()->with('success', 'Fee type deleted successfully.');
    }

    public function assign(Request $request): View
    {
        $validated = $request->validate([
            'edit' => ['nullable', 'integer', 'exists:fee_assignments,id'],
        ]);

        return view('fees.assign', $this->pageData() + [
            'editAssignment' => isset($validated['edit']) ? FeeAssignment::with(['student', 'feeType'])->findOrFail($validated['edit']) : null,
        ]);
    }

    public function storeAssignment(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->assignmentRules());
        $section = $this->validateSection($validated);
        $feeType = FeeType::findOrFail($validated['fee_type_id']);

        if ($feeType->status !== 'active') {
            throw ValidationException::withMessages(['fee_type_id' => 'Only active fee types can be assigned.']);
        }

        $students = isset($validated['student_id'])
            ? Student::query()->whereKey($validated['student_id'])->get()
            : $this->studentsForClassSectionYear(
                $validated['school_class_id'],
                $validated['school_section_id'],
                $validated['academic_year']
            )->get();

        if ($students->isEmpty()) {
            throw ValidationException::withMessages(['student_id' => 'No eligible students were found for this class and section.']);
        }

        foreach ($students as $student) {
            if (! $this->studentMatchesAssignment($student, $validated)) {
                throw ValidationException::withMessages(['student_id' => 'The selected student does not belong to this class and section for the academic year.']);
            }
        }

        $created = 0;
        DB::transaction(function () use ($validated, $students, &$created) {
            foreach ($students as $student) {
                $assignment = FeeAssignment::firstOrCreate(
                    [
                        'academic_year' => $validated['academic_year'],
                        'fee_type_id' => $validated['fee_type_id'],
                        'student_id' => $student->id,
                    ],
                    [
                        'school_class_id' => $validated['school_class_id'],
                        'school_section_id' => $validated['school_section_id'],
                        'assigned_amount' => $validated['assigned_amount'],
                        'paid_amount' => 0,
                        'balance_amount' => $validated['assigned_amount'],
                        'due_date' => $validated['due_date'],
                        'payment_status' => today()->gt(Carbon::parse($validated['due_date'])) ? 'overdue' : 'unpaid',
                        'status' => $validated['status'],
                    ]
                );

                if ($assignment->wasRecentlyCreated) {
                    $created++;
                }
            }
        });

        $message = $created > 0
            ? "{$created} student fee assignment(s) created successfully."
            : 'No new assignments were created because matching fees already exist.';

        return redirect()->route('fees.assignments.index')->with($created > 0 ? 'success' : 'error', $message);
    }

    public function updateAssignment(Request $request, FeeAssignment $feeAssignment): RedirectResponse
    {
        $validated = $request->validate([
            'assigned_amount' => ['required', 'numeric', 'gt:0'],
            'due_date' => ['required', 'date'],
            'status' => ['required', Rule::in(array_keys(FeeAssignment::STATUSES))],
        ]);

        if ((float) $validated['assigned_amount'] < (float) $feeAssignment->paid_amount) {
            throw ValidationException::withMessages(['assigned_amount' => 'Assigned amount cannot be less than the amount already paid.']);
        }

        $feeAssignment->update($validated);
        $this->balances->refresh($feeAssignment);

        return redirect()->route('fees.assignments.index')->with('success', 'Fee assignment updated successfully.');
    }

    public function assignments(Request $request): View
    {
        $this->balances->refreshOpenAssignments();
        $filters = $request->validate($this->assignmentFilterRules());
        $assignments = $this->visibleAssignments($request)
            ->with(['student', 'schoolClass', 'schoolSection', 'feeType'])
            ->when($filters['class_id'] ?? null, fn (Builder $query, int $id) => $query->where('school_class_id', $id))
            ->when($filters['section_id'] ?? null, fn (Builder $query, int $id) => $query->where('school_section_id', $id))
            ->when($filters['student_id'] ?? null, fn (Builder $query, int $id) => $query->where('student_id', $id))
            ->when($filters['fee_type_id'] ?? null, fn (Builder $query, int $id) => $query->where('fee_type_id', $id))
            ->when($filters['academic_year'] ?? null, fn (Builder $query, string $year) => $query->where('academic_year', $year))
            ->when($filters['payment_status'] ?? null, fn (Builder $query, string $status) => $query->where('payment_status', $status))
            ->latest('due_date')
            ->paginate(20)
            ->withQueryString();

        return view('fees.assignments.index', $this->pageData() + compact('assignments'));
    }

    public function showAssignment(Request $request, FeeAssignment $feeAssignment): View
    {
        $this->authorizeAssignment($request, $feeAssignment);
        $feeAssignment->load(['student', 'schoolClass', 'schoolSection', 'feeType', 'payments.receiver']);

        return view('fees.assignments.show', [
            'assignment' => $feeAssignment,
            'paymentStatuses' => FeeAssignment::PAYMENT_STATUSES,
            'isManager' => $this->isManager($request),
        ]);
    }

    public function destroyAssignment(FeeAssignment $feeAssignment): RedirectResponse
    {
        if ($feeAssignment->payments()->exists()) {
            return back()->with('error', 'Fee assignments with payments cannot be deleted.');
        }

        $feeAssignment->delete();

        return back()->with('success', 'Fee assignment deleted successfully.');
    }

    public function paymentForm(FeeAssignment $feeAssignment): View
    {
        abort_if($feeAssignment->status !== 'active' || (float) $feeAssignment->balance_amount <= 0, 404);
        $feeAssignment->load(['student', 'schoolClass', 'schoolSection', 'feeType']);

        return view('fees.payments.create', [
            'assignment' => $feeAssignment,
            'methods' => FeePayment::METHODS,
        ]);
    }

    public function storePayment(Request $request, FeeAssignment $feeAssignment): RedirectResponse
    {
        $validated = $request->validate($this->paymentRules());
        $feeAssignment = $this->balances->refresh($feeAssignment);

        if ((float) $validated['payment_amount'] > (float) $feeAssignment->balance_amount) {
            throw ValidationException::withMessages(['payment_amount' => 'Payment amount cannot exceed the outstanding balance.']);
        }

        $payment = DB::transaction(function () use ($validated, $feeAssignment, $request) {
            $payment = FeePayment::create($validated + [
                'fee_assignment_id' => $feeAssignment->id,
                'student_id' => $feeAssignment->student_id,
                'receipt_number' => $this->receiptNumber(),
                'received_by' => $request->user()->id,
            ]);
            $this->balances->refresh($feeAssignment);

            return $payment;
        });

        return redirect()->route('fees.receipt', $payment)->with('success', 'Payment recorded successfully.');
    }

    public function paymentHistory(Request $request): View
    {
        $filters = $request->validate([
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
            'class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'fee_type_id' => ['nullable', 'integer', 'exists:fee_types,id'],
            'payment_date' => ['nullable', 'date'],
            'payment_method' => ['nullable', Rule::in(array_keys(FeePayment::METHODS))],
        ]);

        $payments = $this->visiblePayments($request)
            ->with(['student', 'assignment.schoolClass', 'assignment.feeType', 'receiver'])
            ->when($filters['student_id'] ?? null, fn (Builder $query, int $id) => $query->where('student_id', $id))
            ->when($filters['class_id'] ?? null, fn (Builder $query, int $id) => $query->whereHas('assignment', fn (Builder $query) => $query->where('school_class_id', $id)))
            ->when($filters['fee_type_id'] ?? null, fn (Builder $query, int $id) => $query->whereHas('assignment', fn (Builder $query) => $query->where('fee_type_id', $id)))
            ->when($filters['payment_date'] ?? null, fn (Builder $query, string $date) => $query->whereDate('payment_date', $date))
            ->when($filters['payment_method'] ?? null, fn (Builder $query, string $method) => $query->where('payment_method', $method))
            ->latest('payment_date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('fees.payments.index', $this->pageData() + [
            'payments' => $payments,
            'methods' => FeePayment::METHODS,
            'isManager' => $this->isManager($request),
        ]);
    }

    public function editPayment(FeePayment $feePayment): View
    {
        $feePayment->load(['assignment.feeType', 'student']);

        return view('fees.payments.edit', [
            'payment' => $feePayment,
            'methods' => FeePayment::METHODS,
        ]);
    }

    public function updatePayment(Request $request, FeePayment $feePayment): RedirectResponse
    {
        $validated = $request->validate($this->paymentRules());
        $assignment = $this->balances->refresh($feePayment->assignment);
        $available = (float) $assignment->balance_amount + (float) $feePayment->payment_amount;

        if ((float) $validated['payment_amount'] > $available) {
            throw ValidationException::withMessages(['payment_amount' => 'Payment amount cannot exceed the available assignment balance.']);
        }

        $feePayment->update($validated);
        $this->balances->refresh($assignment);

        return redirect()->route('fees.receipt', $feePayment)->with('success', 'Payment updated successfully.');
    }

    public function destroyPayment(FeePayment $feePayment): RedirectResponse
    {
        $assignment = $feePayment->assignment;
        $feePayment->delete();
        $this->balances->refresh($assignment);

        return redirect()->route('fees.payments.index')->with('success', 'Payment deleted and fee balance recalculated.');
    }

    public function pending(Request $request): View
    {
        $this->balances->refreshOpenAssignments();
        $filters = $request->validate([
            'class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'section_id' => ['nullable', 'integer', 'exists:school_sections,id'],
            'fee_type_id' => ['nullable', 'integer', 'exists:fee_types,id'],
            'due_date' => ['nullable', 'date'],
            'payment_status' => ['nullable', Rule::in(['unpaid', 'partially_paid', 'overdue'])],
        ]);

        $assignments = FeeAssignment::query()
            ->with(['student', 'schoolClass', 'schoolSection', 'feeType'])
            ->whereIn('payment_status', ['unpaid', 'partially_paid', 'overdue'])
            ->when($filters['class_id'] ?? null, fn (Builder $query, int $id) => $query->where('school_class_id', $id))
            ->when($filters['section_id'] ?? null, fn (Builder $query, int $id) => $query->where('school_section_id', $id))
            ->when($filters['fee_type_id'] ?? null, fn (Builder $query, int $id) => $query->where('fee_type_id', $id))
            ->when($filters['due_date'] ?? null, fn (Builder $query, string $date) => $query->whereDate('due_date', $date))
            ->when($filters['payment_status'] ?? null, fn (Builder $query, string $status) => $query->where('payment_status', $status))
            ->orderBy('due_date')
            ->paginate(20)
            ->withQueryString();

        return view('fees.pending', $this->pageData() + compact('assignments'));
    }

    public function receipt(Request $request, FeePayment $feePayment): View
    {
        $this->authorizePayment($request, $feePayment);
        $feePayment->load(['student', 'assignment.feeType', 'assignment.schoolClass', 'assignment.schoolSection', 'receiver']);

        return view('fees.receipt', [
            'payment' => $feePayment,
            'methods' => FeePayment::METHODS,
        ]);
    }

    private function feeTypeRules(?FeeType $feeType = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('fee_types')->ignore($feeType)],
            'description' => ['nullable', 'string', 'max:1000'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'frequency' => ['required', Rule::in(array_keys(FeeType::FREQUENCIES))],
            'status' => ['required', Rule::in(array_keys(FeeType::STATUSES))],
        ];
    }

    private function assignmentRules(): array
    {
        return [
            'academic_year' => ['required', 'regex:/^\d{4}\/\d{4}$/'],
            'fee_type_id' => ['required', 'integer', 'exists:fee_types,id'],
            'school_class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'school_section_id' => ['required', 'integer', 'exists:school_sections,id'],
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
            'assigned_amount' => ['required', 'numeric', 'gt:0'],
            'due_date' => ['required', 'date'],
            'status' => ['required', Rule::in(array_keys(FeeAssignment::STATUSES))],
        ];
    }

    private function paymentRules(): array
    {
        return [
            'payment_amount' => ['required', 'numeric', 'gt:0'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', Rule::in(array_keys(FeePayment::METHODS))],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function assignmentFilterRules(): array
    {
        return [
            'class_id' => ['nullable', 'integer', 'exists:school_classes,id'],
            'section_id' => ['nullable', 'integer', 'exists:school_sections,id'],
            'student_id' => ['nullable', 'integer', 'exists:students,id'],
            'fee_type_id' => ['nullable', 'integer', 'exists:fee_types,id'],
            'academic_year' => ['nullable', 'regex:/^\d{4}\/\d{4}$/'],
            'payment_status' => ['nullable', Rule::in(array_keys(FeeAssignment::PAYMENT_STATUSES))],
        ];
    }

    private function validateSection(array $validated): SchoolSection
    {
        $section = SchoolSection::query()
            ->whereKey($validated['school_section_id'])
            ->where('school_class_id', $validated['school_class_id'])
            ->first();

        if (! $section) {
            throw ValidationException::withMessages(['school_section_id' => 'The selected section does not belong to the selected class.']);
        }

        return $section;
    }

    private function studentsForClassSectionYear(int $classId, int $sectionId, string $academicYear): Builder
    {
        return Student::query()
            ->where('status', 'active')
            ->where(function (Builder $query) use ($classId, $sectionId, $academicYear) {
                $query->where(function (Builder $query) use ($classId, $sectionId) {
                    $query->where('school_class_id', $classId)->where('school_section_id', $sectionId);
                })->orWhereHas('classAssignments', function (Builder $query) use ($classId, $sectionId, $academicYear) {
                    $query->where('school_class_id', $classId)
                        ->where('school_section_id', $sectionId)
                        ->where('academic_year', $academicYear)
                        ->where('status', 'assigned');
                });
            });
    }

    private function studentMatchesAssignment(Student $student, array $validated): bool
    {
        return ($student->school_class_id === (int) $validated['school_class_id']
            && $student->school_section_id === (int) $validated['school_section_id'])
            || $student->classAssignments()
                ->where('school_class_id', $validated['school_class_id'])
                ->where('school_section_id', $validated['school_section_id'])
                ->where('academic_year', $validated['academic_year'])
                ->where('status', 'assigned')
                ->exists();
    }

    private function pageData(): array
    {
        $year = (int) now()->format('Y');

        return [
            'classes' => SchoolClass::query()->with('sections')->orderBy('name')->get(),
            'students' => Student::query()->where('status', 'active')->orderBy('full_name')->get(),
            'feeTypes' => FeeType::query()->orderBy('name')->get(),
            'academicYears' => collect(range($year - 2, $year + 2))
                ->map(fn (int $start) => $start.'/'.($start + 1))
                ->merge(FeeAssignment::query()->distinct()->pluck('academic_year'))
                ->unique()->sortDesc()->values(),
            'paymentStatuses' => FeeAssignment::PAYMENT_STATUSES,
            'assignmentStatuses' => FeeAssignment::STATUSES,
        ];
    }

    private function visibleAssignments(Request $request): Builder
    {
        $query = FeeAssignment::query();
        if ($this->isManager($request)) {
            return $query;
        }

        $student = Student::query()->where('email', $request->user()->email)->first();

        return $student ? $query->where('student_id', $student->id) : $query->whereRaw('1 = 0');
    }

    private function visiblePayments(Request $request): Builder
    {
        $query = FeePayment::query();
        if ($this->isManager($request)) {
            return $query;
        }

        $student = Student::query()->where('email', $request->user()->email)->first();

        return $student ? $query->where('student_id', $student->id) : $query->whereRaw('1 = 0');
    }

    private function authorizeAssignment(Request $request, FeeAssignment $assignment): void
    {
        abort_unless($this->visibleAssignments($request)->whereKey($assignment->id)->exists(), 403);
    }

    private function authorizePayment(Request $request, FeePayment $payment): void
    {
        abort_unless($this->visiblePayments($request)->whereKey($payment->id)->exists(), 403);
    }

    private function isManager(Request $request): bool
    {
        return in_array($request->user()->role, ['super_admin', 'admin'], true);
    }

    private function receiptNumber(): string
    {
        do {
            $number = 'FEE-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (FeePayment::where('receipt_number', $number)->exists());

        return $number;
    }
}
