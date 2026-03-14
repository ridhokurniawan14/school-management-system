<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('internships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                  ->constrained('students')
                  ->cascadeOnDelete();
            $table->foreignId('academic_semester_id')
                  ->constrained('academic_semesters')
                  ->cascadeOnDelete();

            // Data Perusahaan / DUDI (Dunia Usaha Dunia Industri)
            $table->string('company_name');
            $table->text('company_address')->nullable();
            $table->string('company_phone', 20)->nullable();
            $table->string('company_email')->nullable();
            $table->string('field_of_work')->nullable();     // bidang pekerjaan PKL

            // Pembimbing
            $table->string('supervisor_name')->nullable();   // pembimbing dari perusahaan
            $table->foreignId('teacher_supervisor_id')
                  ->nullable()
                  ->constrained('teachers')
                  ->nullOnDelete();                          // guru pembimbing dari sekolah

            // Periode PKL
            $table->date('start_date');
            $table->date('end_date');

            // Hasil PKL
            $table->enum('status', ['pending', 'ongoing', 'completed', 'failed'])->default('pending');
            $table->unsignedTinyInteger('final_score')->nullable(); // nilai akhir PKL (0-100)
            $table->string('certificate_number')->nullable(); // nomor sertifikat PKL
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('internships');
    }
};
