<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_number',
        'full_name',
        'date_of_birth',
        'gender',
        'address',
        'contact_number',
        'email',
        'qualification',
        'experience',
        'main_subject',
        'joining_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'joining_date' => 'date',
            'experience' => 'integer',
        ];
    }

    public function assignedClasses(): HasMany
    {
        return $this->hasMany(TeacherAssignedClass::class);
    }

    public function assignedSubjects(): HasMany
    {
        return $this->hasMany(TeacherAssignedSubject::class);
    }
}
