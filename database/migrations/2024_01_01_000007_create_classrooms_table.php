<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')
                  ->constrained('academic_years')
                  ->cascadeOnDelete();
            $table->foreignId('competency_id')
                  ->nullable()
                  ->constrained('competencies')
                  ->nullOnDelete();
            $table->string('name');                          // "XII RPL 1"
            $table->enum('grade', ['10', '11', '12']);       // tingkat kelas
            $table->string('room_number')->nullable();        // ruang fisik: "Ruang 12", "Lab RPL"
            $table->unsignedSmallInteger('capacity')->default(36);
            $table->foreignId('homeroom_teacher_id')
                  ->nullable()
                  ->constrained('teachers')
                  ->nullOnDelete();                          // wali kelas
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Nama kelas harus unik per tahun ajaran
            $table->unique(['academic_year_id', 'name']);
        });

        // Pivot tabel: siswa yang terdaftar di kelas per tahun ajaran
        Schema::create('classroom_students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')
                  ->constrained('classrooms')
                  ->cascadeOnDelete();
            $table->foreignId('student_id')
                  ->constrained('students')
                  ->cascadeOnDelete();
            $table->foreignId('academic_year_id')
                  ->constrained('academic_years')
                  ->cascadeOnDelete();
            $table->enum('status', ['active', 'moved', 'dropped'])->default('active');
            $table->timestamps();

            // Satu siswa hanya boleh ada di 1 kelas per tahun ajaran
            $table->unique(['student_id', 'academic_year_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_students');
        Schema::dropIfExists('classrooms');
    }
};
