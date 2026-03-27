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
        Schema::table('subject_kkms', function (Blueprint $table) {
            $table->foreignId('academic_semester_id')
                ->after('academic_year_id')
                ->constrained('academic_semesters')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subject_kkms', function (Blueprint $table) {
            //
        });
    }
};
