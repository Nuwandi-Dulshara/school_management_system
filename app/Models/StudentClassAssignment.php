<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentClassAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'school_class_id',
        'school_section_id',
        'academic_year',
        'status',
        'assigned_at',
        'transferred_at',
        'removed_at',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'datetime',
            'transferred_at' => 'datetime',
            'removed_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function schoolSection(): BelongsTo
    {
        return $this->belongsTo(SchoolSection::class);
    }
}
