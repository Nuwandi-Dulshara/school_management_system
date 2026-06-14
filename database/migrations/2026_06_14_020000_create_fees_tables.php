<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('frequency');
            $table->string('status')->default('active');
            $table->timestamps();
        });

        Schema::create('fee_assignments', function (Blueprint $table) {
            $table->id();
            $table->string('academic_year');
            $table->foreignId('fee_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('school_class_id')->constrained()->restrictOnDelete();
            $table->foreignId('school_section_id')->constrained()->restrictOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->decimal('assigned_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2)->default(0);
            $table->decimal('balance_amount', 10, 2);
            $table->date('due_date');
            $table->string('payment_status')->default('unpaid');
            $table->string('status')->default('active');
            $table->timestamps();

            $table->unique(['academic_year', 'fee_type_id', 'student_id'], 'student_fee_year_unique');
            $table->index(['school_class_id', 'school_section_id', 'academic_year']);
            $table->index(['payment_status', 'due_date']);
        });

        Schema::create('fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fee_assignment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('receipt_number')->unique();
            $table->decimal('payment_amount', 10, 2);
            $table->date('payment_date');
            $table->string('payment_method');
            $table->string('reference_number')->nullable();
            $table->text('remarks')->nullable();
            $table->foreignId('received_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['student_id', 'payment_date']);
            $table->index(['payment_method', 'payment_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fee_payments');
        Schema::dropIfExists('fee_assignments');
        Schema::dropIfExists('fee_types');
    }
};
