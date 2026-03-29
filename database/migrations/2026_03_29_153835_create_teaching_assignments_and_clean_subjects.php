<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Buang tabel relasi guru-mapel yang lama (karena akan diganti)
        Schema::dropIfExists('teacher_subjects');

        // 2. Buang kolom jam pelajaran dari tabel master mapel
        Schema::table('subjects', function (Blueprint $table) {
            $table->dropColumn('credit_hours');
        });

        // 3. Buat tabel Opsi B (Pembagian Tugas Mengajar) yang jauh lebih detail
        Schema::create('teaching_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('academic_semester_id')->constrained('academic_semesters')->cascadeOnDelete();
            $table->tinyInteger('credit_hours')->unsigned()->comment('Jam mengajar spesifik untuk guru, mapel, & kelas ini');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Rollback jika terjadi kesalahan
        Schema::dropIfExists('teaching_assignments');

        Schema::table('subjects', function (Blueprint $table) {
            $table->tinyInteger('credit_hours')->unsigned()->default(2);
        });

        Schema::create('teacher_subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('teachers')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->timestamps();
        });
    }
};
