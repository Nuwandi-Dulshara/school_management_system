<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('admission_number')->unique();
            $table->string('full_name');
            $table->date('date_of_birth');
            $table->string('gender');
            $table->text('address');
            $table->string('contact_number')->nullable();
            $table->string('email')->nullable();
            $table->string('class');
            $table->string('section');
            $table->date('admission_date');
            $table->string('status')->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
