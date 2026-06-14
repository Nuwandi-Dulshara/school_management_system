<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notices', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('message');
            $table->string('audience');
            $table->foreignId('school_class_id')->nullable()->constrained()->restrictOnDelete();
            $table->foreignId('school_section_id')->nullable()->constrained()->restrictOnDelete();
            $table->date('publish_date');
            $table->date('expiry_date')->nullable();
            $table->string('priority')->default('normal');
            $table->string('status')->default('draft');
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['status', 'publish_date', 'expiry_date']);
            $table->index(['audience', 'school_class_id', 'school_section_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notices');
    }
};
