<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // DROP tabel lama jika sudah ada (karena perlu redesign total)
        Schema::dropIfExists('academic_years');

        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name');                         // "2024/2025"
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(false);  // hanya 1 boleh true
            $table->enum('status', ['upcoming', 'active', 'completed'])->default('upcoming');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_years');
    }
};
