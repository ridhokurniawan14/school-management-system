<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('student_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                ->unique()
                ->constrained('students')
                ->cascadeOnDelete();

            // Identitas
            $table->string('nik', 16)->nullable();
            $table->string('no_kk', 16)->nullable();
            $table->unsignedTinyInteger('child_order')->nullable(); // anak ke berapa
            $table->unsignedTinyInteger('siblings_count')->nullable(); // jml saudara kandung

            // Fisik
            $table->decimal('weight', 5, 2)->nullable(); // berat badan (kg)
            $table->decimal('height', 5, 2)->nullable(); // tinggi badan (cm)
            $table->decimal('head_circumference', 5, 2)->nullable(); // lingkar kepala (cm)

            // Sekolah & Transportasi
            $table->string('previous_school')->nullable(); // sekolah asal
            $table->string('transportation')->nullable(); // alat transportasi
            $table->decimal('distance_to_school', 6, 2)->nullable(); // jarak rumah ke sekolah (km)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_details');
    }
};
