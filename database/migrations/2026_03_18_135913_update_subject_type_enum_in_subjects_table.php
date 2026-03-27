<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
