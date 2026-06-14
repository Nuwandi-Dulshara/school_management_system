<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeePayment extends Model
{
    use HasFactory;

    public const METHODS = [
        'cash' => 'Cash',
        'bank_transfer' => 'Bank Transfer',
        'card' => 'Card',
        'online' => 'Online',
    ];

    protected $fillable = [
        'fee_assignment_id',
        'student_id',
        'receipt_number',
        'payment_amount',
        'payment_date',
        'payment_method',
        'reference_number',
        'remarks',
        'received_by',
    ];

    protected function casts(): array
    {
        return [
            'payment_amount' => 'decimal:2',
            'payment_date' => 'date',
        ];
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(FeeAssignment::class, 'fee_assignment_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
