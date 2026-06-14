<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamSchedule;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ExamScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'exam_id' => ['nullable', 'integer', 'exists:exams,id'],
            'edit' => ['nullable', 'integer', 'exists:exam_schedules,id'],
        ]);

        $isManager = in_array($request->user()->role, ['super_admin', 'admin'], true);
        $visibleExamIds = $this->visibleExamIds($request);

        if (isset($validated['exam_id']) && ! $visibleExamIds->contains((int) $validated['exam_id'])) {
            abort(403);
        }

        $schedules = ExamSchedule::query()
            ->with(['exam.examType', 'exam.schoolClass', 'exam.schoolSection', 'subject'])
            ->whereIn('exam_id', $visibleExamIds)
            ->when($validated['exam_id'] ?? null, fn ($query, int $id) => $query->where('exam_id', $id))
            ->orderBy('exam_date')
            ->orderBy('start_time')
            ->paginate(20)
            ->withQueryString();

        $editSchedule = null;
        if ($isManager && isset($validated['edit'])) {
            $editSchedule = ExamSchedule::findOrFail($validated['edit']);
        }

        return view('examinations.schedules', [
            'schedules' => $schedules,
            'exams' => Exam::query()->with(['schoolClass', 'schoolSection'])->whereIn('id', $visibleExamIds)->orderByDesc('start_date')->get(),
            'subjects' => Subject::query()->where('status', 'active')->orderBy('name')->get(),
            'editSchedule' => $editSchedule,
            'isManager' => $isManager,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $this->validateScheduleDate($validated);
        ExamSchedule::create($validated);

        return redirect()->route('examinations.schedules.index', ['exam_id' => $validated['exam_id']])
            ->with('success', 'Exam schedule added successfully.');
    }

    public function update(Request $request, ExamSchedule $examSchedule): RedirectResponse
    {
        $validated = $request->validate($this->rules($examSchedule));
        $this->validateScheduleDate($validated);
        $examSchedule->update($validated);

        return redirect()->route('examinations.schedules.index', ['exam_id' => $validated['exam_id']])
            ->with('success', 'Exam schedule updated successfully.');
    }

    public function destroy(ExamSchedule $examSchedule): RedirectResponse
    {
        $examSchedule->delete();

        return back()->with('success', 'Exam schedule deleted successfully.');
    }

    private function rules(?ExamSchedule $schedule = null): array
    {
        return [
            'exam_id' => ['required', 'integer', 'exists:exams,id'],
            'subject_id' => [
                'required',
                'integer',
                'exists:subjects,id',
                Rule::unique('exam_schedules')
                    ->where(fn ($query) => $query->where('exam_id', request('exam_id')))
                    ->ignore($schedule),
            ],
            'exam_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'room' => ['nullable', 'string', 'max:100'],
        ];
    }

    private function validateScheduleDate(array $validated): void
    {
        $exam = Exam::findOrFail($validated['exam_id']);

        if ($validated['exam_date'] < $exam->start_date->toDateString()
            || $validated['exam_date'] > $exam->end_date->toDateString()) {
            throw ValidationException::withMessages([
                'exam_date' => 'The exam date must fall between the exam start and end dates.',
            ]);
        }
    }

    private function visibleExamIds(Request $request)
    {
        return app(ExaminationController::class)->queryVisibleTo($request)->pluck('id');
    }
}
