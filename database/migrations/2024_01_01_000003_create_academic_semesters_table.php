<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_semesters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')
                  ->constrained('academic_years')
                  ->cascadeOnDelete();
            $table->string('name');                                         // "Semester Ganjil 2024/2025"
            $table->enum('semester', ['1', '2']);                           // 1 = Ganjil, 2 = Genap
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);                   // semester yang sedang berjalan
            $table->enum('status', ['upcoming', 'active', 'completed'])->default('upcoming');
            $table->timestamps();

            // Satu tahun ajaran hanya boleh punya semester 1 dan semester 2 (tidak duplikat)
            $table->unique(['academic_year_id', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_semesters');
    }
};
