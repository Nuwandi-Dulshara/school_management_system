<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeType extends Model
{
    use HasFactory;

    public const FREQUENCIES = [
        'monthly' => 'Monthly',
        'termly' => 'Termly',
        'yearly' => 'Yearly',
        'one_time' => 'One Time',
    ];

    public const STATUSES = [
        'active' => 'Active',
        'inactive' => 'Inactive',
    ];

    protected $fillable = ['name', 'description', 'amount', 'frequency', 'status'];

    protected function casts(): array
    {
        return ['amount' => 'decimal:2'];
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(FeeAssignment::class);
    }
}
