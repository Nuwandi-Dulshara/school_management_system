<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeAssignment extends Model
{
    use HasFactory;

    public const PAYMENT_STATUSES = [
        'unpaid' => 'Unpaid',
        'partially_paid' => 'Partially Paid',
        'paid' => 'Paid',
        'overdue' => 'Overdue',
    ];

    public const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];

    protected $fillable = [
        'academic_year',
        'fee_type_id',
        'school_class_id',
        'school_section_id',
        'student_id',
        'assigned_amount',
        'paid_amount',
        'balance_amount',
        'due_date',
        'payment_status',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'assigned_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance_amount' => 'decimal:2',
            'due_date' => 'date',
        ];
    }

    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class);
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

    public function payments(): HasMany
    {
        return $this->hasMany(FeePayment::class);
    }
}
