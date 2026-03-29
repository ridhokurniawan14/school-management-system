<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Template tagihan per tahun ajaran
        // Contoh: SPP bulan Agustus 2024, Seragam, Buku, dll
        Schema::create('payment_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')
                ->constrained('academic_years')
                ->cascadeOnDelete();
            $table->string('name');                          // "SPP Agustus 2024", "Seragam", "Buku Paket"
            $table->enum('payment_type', ['spp', 'registration', 'uniform', 'book', 'exam', 'other']);
            $table->unsignedBigInteger('amount');            // nominal tagihan (dalam rupiah)
            $table->date('due_date');                        // batas waktu pembayaran
            $table->boolean('is_mandatory')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Record pembayaran aktual dari siswa
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')
                ->constrained('students')
                ->cascadeOnDelete();
            $table->foreignId('payment_bill_id')
                ->constrained('payment_bills')
                ->cascadeOnDelete();
            $table->foreignId('academic_year_id')
                ->constrained('academic_years')
                ->cascadeOnDelete();
            $table->string('receipt_number')->nullable()->unique(); // nomor kuitansi
            $table->unsignedBigInteger('amount');            // nominal yang harus dibayar (bisa beda jika ada diskon)
            $table->unsignedBigInteger('paid_amount')->default(0); // nominal yang sudah dibayar
            $table->date('due_date');                        // snapshot due_date dari bill
            $table->date('paid_date')->nullable();           // tanggal bayar
            $table->enum('payment_method', ['cash', 'transfer', 'virtual_account', 'other'])->nullable();
            $table->enum('status', ['pending', 'partial', 'paid', 'overdue'])->default('pending');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('recorded_by')->nullable(); // FK users (TU / admin)
            $table->timestamps();

            // Satu siswa hanya punya 1 record per tagihan
            $table->unique(['student_id', 'payment_bill_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_bills');
    }
};
