<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_profiles', function (Blueprint $table) {
            $table->id();

            // Identitas Utama
            $table->string('name');                         // nama lengkap sekolah
            $table->string('short_name')->nullable();       // singkatan / nama panggilan
            $table->string('npsn', 20)->nullable()->unique(); // 8 digit, unik nasional
            $table->string('nss', 30)->nullable()->unique(); // Nomor Statistik Sekolah
            $table->enum('school_type', ['SMA', 'MA', 'SMK'])->default('SMK');
            $table->enum('accreditation', ['A', 'B', 'C', 'not_accredited'])->nullable();
            $table->enum('school_category', ['negeri', 'swasta'])->default('negeri');

            // Kontak & Lokasi
            $table->text('address')->nullable();
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();         // kecamatan
            $table->string('village')->nullable();          // kelurahan/desa
            $table->string('postal_code', 10)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();

            // Branding & Dokumen Sekolah
            // Catatan: logo, favicon, kop_surat adalah aset SEKOLAH (tidak berubah per periode)
            // TTD kepala sekolah ada di principal_assignments karena terikat per pejabat
            $table->string('logo')->nullable();             // path: storage/school/logo.png
            $table->string('favicon')->nullable();          // path: storage/school/favicon.png
            $table->string('letterhead')->nullable();       // kop surat: storage/school/letterhead.png

            // Info Akademik
            $table->year('established_year')->nullable();
            $table->enum('curriculum', ['merdeka', 'k13'])->default('merdeka');
            $table->unsignedTinyInteger('academic_year_start_month')->default(7); // Juli
            $table->string('timezone')->default('Asia/Jakarta');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_profiles');
    }
};
