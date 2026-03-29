<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_semester_id')
                ->constrained('academic_semesters')
                ->cascadeOnDelete();
            $table->foreignId('classroom_id')
                ->constrained('classrooms')
                ->cascadeOnDelete();
            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();
            $table->foreignId('schedule_id')
                ->nullable()
                ->constrained('schedules')
                ->nullOnDelete();
            // NULL = absensi harian (pagi/pulang)
            // Filled = absensi per mata pelajaran
            $table->date('date');
            $table->enum('status', ['present', 'sick', 'permitted', 'absent']);
            // present  = Hadir
            // sick     = Sakit (dengan surat)
            // permitted = Izin
            // absent   = Alpa (tanpa keterangan)
            $table->time('check_in_time')->nullable();       // untuk fingerprint / QR scan
            $table->text('notes')->nullable();               // keterangan tambahan
            $table->unsignedBigInteger('recorded_by')->nullable(); // FK users (guru / TU)
            $table->timestamps();

            // Satu siswa hanya boleh punya 1 record absensi per jadwal per hari
            $table->unique(['student_id', 'schedule_id', 'date'], 'unique_attendance_per_schedule');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
