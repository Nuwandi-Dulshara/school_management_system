<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mark extends Model
{
    use HasFactory;

    public const STATUSES = [
        'draft' => 'Draft',
        'published' => 'Published',
    ];

    protected $fillable = [
        'exam_id',
        'academic_year',
        'school_class_id',
        'school_section_id',
        'subject_id',
        'student_id',
        'marks_obtained',
        'maximum_marks',
        'grade',
        'remarks',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'marks_obtained' => 'decimal:2',
            'maximum_marks' => 'decimal:2',
        ];
    }

    public static function gradeFor(float $marks, float $maximum): string
    {
        $percentage = $maximum > 0 ? ($marks / $maximum) * 100 : 0;

        return match (true) {
            $percentage >= 75 => 'A',
            $percentage >= 65 => 'B',
            $percentage >= 50 => 'C',
            $percentage >= 35 => 'S',
            default => 'F',
        };
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function schoolSection(): BelongsTo
    {
        return $this->belongsTo(SchoolSection::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
