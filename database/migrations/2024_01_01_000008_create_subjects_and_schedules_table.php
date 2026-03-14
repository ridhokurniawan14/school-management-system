<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // "Pemrograman Web"
            $table->string('code', 20)->unique();            // "PWB"
            $table->enum('subject_type', ['normative', 'adaptive', 'productive', 'mulok']);
                  // normative  = Normatif (PKn, Agama, B.Indo, dll)
                  // adaptive   = Adaptif (Matematika, IPA, IPS, Bahasa Inggris, dll)
                  // productive = Produktif (mata pelajaran kejuruan, spesifik jurusan)
                  // mulok      = Muatan Lokal
            $table->foreignId('competency_id')
                  ->nullable()
                  ->constrained('competencies')
                  ->nullOnDelete();
                  // NULL = berlaku untuk semua jurusan (normative/adaptive)
                  // Filled = khusus jurusan tertentu (productive)
            $table->unsignedTinyInteger('credit_hours')->default(2); // jam per minggu
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_semester_id')
                  ->constrained('academic_semesters')
                  ->cascadeOnDelete();
            $table->foreignId('classroom_id')
                  ->constrained('classrooms')
                  ->cascadeOnDelete();
            $table->foreignId('subject_id')
                  ->constrained('subjects')
                  ->cascadeOnDelete();
            $table->foreignId('teacher_id')
                  ->constrained('teachers')
                  ->cascadeOnDelete();
            $table->enum('day', ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']);
            $table->time('start_time');
            $table->time('end_time');
            $table->string('room')->nullable();              // bisa beda dengan classroom (misal: Lab Komputer)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('subjects');
    }
};
