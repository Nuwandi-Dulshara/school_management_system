<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Result extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'academic_year',
        'school_class_id',
        'school_section_id',
        'student_id',
        'total_marks',
        'maximum_total',
        'average_marks',
        'final_grade',
        'rank',
        'result_status',
        'status',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'total_marks' => 'decimal:2',
            'maximum_total' => 'decimal:2',
            'average_marks' => 'decimal:2',
            'rank' => 'integer',
            'published_at' => 'datetime',
        ];
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

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
