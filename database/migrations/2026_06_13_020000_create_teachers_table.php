<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('employee_number')->unique();
            $table->string('full_name');
            $table->date('date_of_birth');
            $table->string('gender');
            $table->text('address');
            $table->string('contact_number');
            $table->string('email')->unique();
            $table->string('qualification');
            $table->unsignedInteger('experience')->default(0);
            $table->string('main_subject');
            $table->date('joining_date');
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
