<?php

namespace App\Services;

use App\Models\Exam;
use App\Models\Mark;
use App\Models\Result;
use Illuminate\Support\Facades\DB;

class ResultCalculator
{
    public function refresh(int $examId, int $studentId): ?Result
    {
        $marks = Mark::query()
            ->where('exam_id', $examId)
            ->where('student_id', $studentId)
            ->get();

        if ($marks->isEmpty()) {
            Result::query()->where('exam_id', $examId)->where('student_id', $studentId)->delete();
            $this->refreshRanks($examId);

            return null;
        }

        $exam = Exam::findOrFail($examId);
        $total = (float) $marks->sum('marks_obtained');
        $maximum = (float) $marks->sum('maximum_marks');
        $average = $maximum > 0 ? ($total / $maximum) * 100 : 0;
        $status = $marks->every(fn (Mark $mark) => $mark->status === 'published') ? 'published' : 'draft';

        $result = Result::updateOrCreate(
            ['exam_id' => $examId, 'student_id' => $studentId],
            [
                'academic_year' => $exam->academic_year,
                'school_class_id' => $exam->school_class_id,
                'school_section_id' => $exam->school_section_id,
                'total_marks' => $total,
                'maximum_total' => $maximum,
                'average_marks' => round($average, 2),
                'final_grade' => Mark::gradeFor($total, $maximum),
                'result_status' => $marks->contains(fn (Mark $mark) => $mark->grade === 'F') ? 'fail' : 'pass',
                'status' => $status,
                'published_at' => $status === 'published' ? now() : null,
            ]
        );

        $this->refreshRanks($examId);

        return $result->fresh();
    }

    public function refreshRanks(int $examId): void
    {
        $results = Result::query()
            ->where('exam_id', $examId)
            ->orderByDesc('total_marks')
            ->orderByDesc('average_marks')
            ->orderBy('student_id')
            ->get();

        DB::transaction(function () use ($results) {
            $rank = 0;
            $position = 0;
            $previousTotal = null;

            foreach ($results as $result) {
                $position++;
                $total = (float) $result->total_marks;

                if ($previousTotal === null || $total !== $previousTotal) {
                    $rank = $position;
                    $previousTotal = $total;
                }

                $result->update(['rank' => $rank]);
            }
        });
    }
}
