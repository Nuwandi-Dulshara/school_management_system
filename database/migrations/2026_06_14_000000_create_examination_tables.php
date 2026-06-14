<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->string('exam_name');
            $table->foreignId('exam_type_id')->constrained()->restrictOnDelete();
            $table->string('academic_year');
            $table->foreignId('school_class_id')->constrained()->restrictOnDelete();
            $table->foreignId('school_section_id')->constrained()->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->text('description')->nullable();
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->index(['school_class_id', 'school_section_id', 'academic_year']);
            $table->index(['status', 'start_date']);
        });

        Schema::create('exam_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->date('exam_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room')->nullable();
            $table->timestamps();

            $table->unique(['exam_id', 'subject_id']);
            $table->index(['exam_date', 'start_time']);
        });

        $now = now();
        DB::table('exam_types')->insert([
            ['name' => 'Term Test', 'description' => 'Regular school term examination.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Midterm', 'description' => 'Mid-year or mid-semester examination.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Final Examination', 'description' => 'End-of-year or end-of-semester examination.', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Practical Examination', 'description' => 'Practical or laboratory-based assessment.', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_schedules');
        Schema::dropIfExists('exams');
        Schema::dropIfExists('exam_types');
    }
};
