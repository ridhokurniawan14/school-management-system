<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subject_kkms', function (Blueprint $table) {
            // 1. Lepas relasi foreign key sebentar biar MySQL nggak ngunci index-nya
            $table->dropForeign(['subject_id']);

            // 2. Hapus index unique yang lama
            $table->dropUnique('unique_kkm');

            // 3. Buat index unique yang baru (patokannya pakai semester, bukan tahun ajaran)
            $table->unique(['subject_id', 'classroom_id', 'academic_semester_id'], 'unique_kkm_per_semester');

            // 4. Pasang kembali relasi foreign key-nya
            $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('subject_kkms', function (Blueprint $table) {
            $table->dropForeign(['subject_id']);
            $table->dropUnique('unique_kkm_per_semester');

            $table->unique(['subject_id', 'classroom_id', 'academic_year_id'], 'unique_kkm');
            $table->foreign('subject_id')->references('id')->on('subjects')->cascadeOnDelete();
        });
    }
};
