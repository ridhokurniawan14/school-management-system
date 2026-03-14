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
            $table->string('nis', 20)->unique();             // Nomor Induk Siswa (lokal)
            $table->string('nisn', 10)->nullable()->unique(); // Nomor Induk Siswa Nasional
            $table->string('full_name');
            $table->enum('gender', ['male', 'female']);
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('religion', ['islam', 'kristen', 'katolik', 'hindu', 'budha', 'konghucu'])->nullable();
            $table->enum('blood_type', ['A', 'B', 'AB', 'O', 'unknown'])->default('unknown');
            $table->text('address')->nullable();
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable()->unique();
            $table->string('photo')->nullable();             // path: storage/students/{id}/photo.jpg
            $table->enum('status', ['active', 'graduated', 'transferred', 'dropped'])->default('active');
            $table->year('entry_year');                      // tahun masuk
            $table->foreignId('competency_id')
                  ->nullable()
                  ->constrained('competencies')
                  ->nullOnDelete();                          // jurusan siswa
            $table->timestamps();
        });

        Schema::create('student_parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                  ->constrained('students')
                  ->cascadeOnDelete();
            $table->enum('relationship', ['father', 'mother', 'guardian']); // ayah, ibu, wali
            $table->string('full_name');
            $table->string('nik', 16)->nullable();           // Nomor Induk Kependudukan
            $table->string('occupation')->nullable();         // pekerjaan
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_emergency_contact')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_parents');
        Schema::dropIfExists('students');
    }
};
