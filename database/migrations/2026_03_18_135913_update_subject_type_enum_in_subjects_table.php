<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE subjects MODIFY COLUMN subject_type ENUM(
        'umum',
        'kejuruan', 
        'pilihan',
        'mulok',
        'normative',
        'adaptive',
        'productive'
    ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE subjects MODIFY COLUMN subject_type ENUM(
        'normative',
        'adaptive',
        'productive',
        'mulok'
    ) NOT NULL");
    }
};
