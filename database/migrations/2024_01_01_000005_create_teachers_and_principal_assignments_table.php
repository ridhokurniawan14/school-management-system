<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 30)->nullable()->unique();    // nullable: guru honorer tidak punya NIP
            $table->string('nuptk', 20)->nullable()->unique();  // Nomor Unik Pendidik dan Tenaga Kependidikan
            $table->string('full_name');
            $table->enum('gender', ['male', 'female']);
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->enum('religion', ['islam', 'kristen', 'katolik', 'hindu', 'budha', 'konghucu'])->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('photo')->nullable();                // path: storage/teachers/{id}/photo.jpg
            $table->text('address')->nullable();
            $table->enum('employment_status', ['pns', 'p3k', 'honorer', 'gty'])->default('honorer');
            $table->enum('role', ['teacher', 'staff', 'admin', 'vice_principal'])->default('teacher');
            // Catatan: role 'principal' tidak ada di sini, karena kepala sekolah
            // dikelola via principal_assignments (bisa berubah per periode)
            $table->boolean('is_active')->default(true);
            $table->date('join_date')->nullable();
            $table->timestamps();
        });

        // Riwayat Jabatan Kepala Sekolah & Wakasek
        // Solusi proper untuk masalah historis: siapa KS saat rapor X dicetak?
        Schema::create('principal_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')
                ->constrained('teachers')
                ->cascadeOnDelete();
            $table->enum('position', ['principal', 'vice_principal'])->default('principal');
            // principal     = Kepala Sekolah
            // vice_principal = Wakil Kepala Sekolah (bisa >1, bedakan via notes/bidang)
            $table->string('position_field')->nullable();
            // Untuk wakasek: "Kurikulum", "Kesiswaan", "Humas", "Sarana Prasarana"
            $table->date('start_date');
            $table->date('end_date')->nullable();               // NULL = masih aktif menjabat
            $table->string('decree_number')->nullable();        // Nomor SK Pengangkatan
            $table->date('decree_date')->nullable();            // Tanggal SK
            $table->string('signature')->nullable();
            // path TTD: storage/principals/{id}/signature.png
            // TTD ada di sini karena terikat per PEJABAT, bukan per sekolah
            // Saat cetak rapor → query KS aktif pada periode tersebut → ambil TTD-nya
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('principal_assignments');
        Schema::dropIfExists('teachers');
    }
};
