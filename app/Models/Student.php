<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'admission_number',
        'full_name',
        'date_of_birth',
        'gender',
        'address',
        'contact_number',
        'email',
        'class',
        'section',
        'admission_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'admission_date' => 'date',
        ];
    }

    public function guardian(): HasOne
    {
        return $this->hasOne(StudentGuardian::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(StudentDocument::class);
    }
}
