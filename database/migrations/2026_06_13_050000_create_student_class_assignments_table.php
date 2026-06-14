<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_class_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained()->restrictOnDelete();
            $table->foreignId('school_section_id')->constrained()->restrictOnDelete();
            $table->string('academic_year', 9);
            $table->string('status')->default('assigned');
            $table->timestamp('assigned_at')->nullable();
            $table->timestamp('transferred_at')->nullable();
            $table->timestamp('removed_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['student_id', 'school_class_id', 'school_section_id', 'academic_year'],
                'student_class_year_unique'
            );
            $table->index(['academic_year', 'school_class_id', 'school_section_id']);
        });

        $year = (int) now()->format('Y');
        $academicYear = $year.'/'.($year + 1);
        $now = now();

        DB::table('students')
            ->whereNotNull('school_class_id')
            ->whereNotNull('school_section_id')
            ->orderBy('id')
            ->each(function (object $student) use ($academicYear, $now) {
                DB::table('student_class_assignments')->insert([
                    'student_id' => $student->id,
                    'school_class_id' => $student->school_class_id,
                    'school_section_id' => $student->school_section_id,
                    'academic_year' => $academicYear,
                    'status' => 'assigned',
                    'assigned_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_class_assignments');
    }
};
