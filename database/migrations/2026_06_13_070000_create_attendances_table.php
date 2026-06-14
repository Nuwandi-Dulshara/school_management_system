<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->restrictOnDelete();
            $table->foreignId('school_class_id')->constrained()->restrictOnDelete();
            $table->foreignId('school_section_id')->constrained()->restrictOnDelete();
            $table->date('attendance_date');
            $table->string('status', 10);
            $table->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(
                ['student_id', 'school_class_id', 'school_section_id', 'attendance_date'],
                'attendances_student_class_section_date_unique'
            );
            $table->index(['attendance_date', 'school_class_id', 'school_section_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
