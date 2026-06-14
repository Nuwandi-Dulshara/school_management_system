<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->string('academic_year');
            $table->foreignId('school_class_id')->constrained()->restrictOnDelete();
            $table->foreignId('school_section_id')->constrained()->restrictOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->decimal('marks_obtained', 8, 2);
            $table->decimal('maximum_marks', 8, 2);
            $table->string('grade', 2);
            $table->text('remarks')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->unique(['exam_id', 'subject_id', 'student_id'], 'exam_subject_student_mark_unique');
            $table->index(['school_class_id', 'school_section_id', 'academic_year']);
            $table->index(['exam_id', 'status']);
        });

        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->string('academic_year');
            $table->foreignId('school_class_id')->constrained()->restrictOnDelete();
            $table->foreignId('school_section_id')->constrained()->restrictOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_marks', 10, 2)->default(0);
            $table->decimal('maximum_total', 10, 2)->default(0);
            $table->decimal('average_marks', 5, 2)->default(0);
            $table->string('final_grade', 2);
            $table->unsignedInteger('rank')->nullable();
            $table->string('result_status');
            $table->string('status')->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['exam_id', 'student_id']);
            $table->index(['exam_id', 'school_class_id', 'school_section_id', 'rank']);
            $table->index(['student_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
        Schema::dropIfExists('marks');
    }
};
