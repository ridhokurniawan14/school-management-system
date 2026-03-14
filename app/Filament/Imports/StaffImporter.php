<?php

// FILE: app/Filament/Imports/StaffImporter.php

namespace App\Filament\Imports;

use App\Models\Teacher;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class StaffImporter extends Importer
{
    protected static ?string $model = Teacher::class;

    public static function getColumns(): array
    {
        return [
            // Kolom role tidak ada — otomatis di-set 'staff' di resolveRecord()

            ImportColumn::make('full_name')
                ->label('Nama Lengkap')
                ->requiredMapping()
                ->examples(['Siti Aminah'])
                ->rules(['required', 'string', 'max:255']),

            ImportColumn::make('nip')
                ->label('NIP')
                ->examples(['197805152003121001'])
                ->rules(['nullable', 'string', 'max:30']),

            ImportColumn::make('nuptk')
                ->label('NUPTK')
                ->examples(['3847756657300012'])
                ->rules(['nullable', 'string', 'max:20']),

            ImportColumn::make('gender')
                ->label('Jenis Kelamin')
                ->requiredMapping()
                ->examples(['female'])
                // Nilai yang diterima: male / female
                ->rules(['required', 'in:male,female']),

            ImportColumn::make('religion')
                ->label('Agama')
                ->examples(['islam'])
                // Nilai yang diterima: islam / kristen / katolik / hindu / budha / konghucu
                ->rules(['nullable', 'in:islam,kristen,katolik,hindu,budha,konghucu']),

            ImportColumn::make('birth_place')
                ->label('Tempat Lahir')
                ->examples(['Banyuwangi'])
                ->rules(['nullable', 'string']),

            ImportColumn::make('birth_date')
                ->label('Tanggal Lahir')
                ->examples(['1990-03-20'])
                // Format: YYYY-MM-DD
                ->rules(['nullable', 'date']),

            ImportColumn::make('employment_status')
                ->label('Status Kepegawaian')
                ->examples(['honorer'])
                // Nilai yang diterima: pns / p3k / honorer / gty
                ->rules(['nullable', 'in:pns,p3k,honorer,gty']),

            ImportColumn::make('join_date')
                ->label('Tanggal Mulai Bertugas')
                ->examples(['2015-07-01'])
                // Format: YYYY-MM-DD
                ->rules(['nullable', 'date']),

            ImportColumn::make('phone')
                ->label('No. HP')
                ->examples(['085678901234'])
                ->rules(['nullable', 'string', 'max:20']),

            ImportColumn::make('email')
                ->label('Email')
                ->examples(['siti.aminah@smkpgri1giri.sch.id'])
                ->rules(['nullable', 'email', 'unique:teachers,email']),

            ImportColumn::make('address')
                ->label('Alamat')
                ->examples(['Jl. Raya Giri No. 5, Banyuwangi'])
                ->rules(['nullable', 'string']),
        ];
    }

    public function resolveRecord(): ?Teacher
    {
        $record = Teacher::firstOrNew([
            'email' => $this->data['email'] ?? null,
        ]);

        // Role selalu staff — tidak perlu ada di file Excel
        $record->role = 'staff';

        return $record;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import data staff selesai. ' . number_format($import->successful_rows) . ' data berhasil diimport.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' data gagal diimport.';
        }

        return $body;
    }
}
