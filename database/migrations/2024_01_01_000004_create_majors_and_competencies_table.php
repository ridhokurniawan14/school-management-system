<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Bidang Keahlian (contoh: Teknologi Informasi dan Komunikasi)
        Schema::create('majors', function (Blueprint $table) {
            $table->id();
            $table->string('name');                         // "Teknologi Informasi dan Komunikasi"
            $table->string('code', 20)->unique();           // "TIK"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Konsentrasi Keahlian / Program Studi
        // (contoh: Rekayasa Perangkat Lunak, Teknik Komputer Jaringan)
        Schema::create('competencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('major_id')
                ->constrained('majors')
                ->cascadeOnDelete();
            $table->string('name');                         // "Rekayasa Perangkat Lunak"
            $table->string('code', 20)->unique();           // "RPL"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competencies');
        Schema::dropIfExists('majors');
    }
};
