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
        Schema::create('subject_kkms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')
                ->constrained('subjects')
                ->cascadeOnDelete();
            $table->foreignId('classroom_id')
                ->constrained('classrooms')
                ->cascadeOnDelete();
            $table->foreignId('academic_year_id')
                ->constrained('academic_years')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('kkm')->default(75);
            $table->timestamps();

            $table->unique(['subject_id', 'classroom_id', 'academic_year_id'], 'unique_kkm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subject_kkms');
    }
};
