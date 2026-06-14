<?php

namespace App\Services;

use App\Models\Notice;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class NoticeVisibilityService
{
    public function visiblePublishedTo(User $user): Builder
    {
        $query = Notice::query()->currentlyPublished();

        if (in_array($user->role, ['super_admin', 'admin'], true)) {
            return $query;
        }

        if ($user->role === 'student') {
            $student = Student::query()->where('email', $user->email)->first();

            return $query->where(function (Builder $query) use ($student) {
                $query->whereIn('audience', ['all_users', 'student']);

                if ($student) {
                    $query->orWhere(function (Builder $query) use ($student) {
                        $query->where('audience', 'class')
                            ->where('school_class_id', $student->school_class_id);
                    })->orWhere(function (Builder $query) use ($student) {
                        $query->where('audience', 'section')
                            ->where('school_class_id', $student->school_class_id)
                            ->where('school_section_id', $student->school_section_id);
                    });
                }
            });
        }

        if ($user->role === 'teacher') {
            $teacher = Teacher::query()->where('email', $user->email)->first();
            $classIds = $teacher?->teachingAssignments()
                ->where('status', 'active')
                ->pluck('school_class_id')
                ->unique()
                ->all() ?? [];

            return $query->where(function (Builder $query) use ($classIds) {
                $query->whereIn('audience', ['all_users', 'teacher'])
                    ->orWhere(function (Builder $query) use ($classIds) {
                        $query->whereIn('audience', ['class', 'section'])
                            ->whereIn('school_class_id', $classIds);
                    });
            });
        }

        return $query->whereRaw('1 = 0');
    }

    public function canView(User $user, Notice $notice): bool
    {
        if (in_array($user->role, ['super_admin', 'admin'], true)) {
            return true;
        }

        return $this->visiblePublishedTo($user)->whereKey($notice->id)->exists();
    }
}
