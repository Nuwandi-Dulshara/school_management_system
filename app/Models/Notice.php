<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notice extends Model
{
    use HasFactory;

    public const AUDIENCES = [
        'all_users' => 'All Users',
        'super_admin' => 'Super Admin',
        'admin' => 'Admin / Office Staff',
        'teacher' => 'Teachers',
        'student' => 'Students',
        'parent' => 'Parents / Guardians',
        'class' => 'Selected Class',
        'section' => 'Selected Section',
    ];

    public const PRIORITIES = [
        'normal' => 'Normal',
        'important' => 'Important',
        'urgent' => 'Urgent',
    ];

    public const STATUSES = [
        'draft' => 'Draft',
        'published' => 'Published',
    ];

    protected $fillable = [
        'title',
        'message',
        'audience',
        'school_class_id',
        'school_section_id',
        'publish_date',
        'expiry_date',
        'priority',
        'status',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'publish_date' => 'date',
            'expiry_date' => 'date',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function schoolSection(): BelongsTo
    {
        return $this->belongsTo(SchoolSection::class);
    }

    public function scopeCurrentlyPublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereDate('publish_date', '<=', today())
            ->where(function (Builder $query) {
                $query->whereNull('expiry_date')->orWhereDate('expiry_date', '>=', today());
            });
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expiry_date?->isBefore(today()) ?? false;
    }
}
