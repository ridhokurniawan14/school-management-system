<?php

// FILE: app/Filament/Imports/TeacherImporter.php

namespace App\Filament\Imports;

use App\Models\Teacher;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class TeacherImporter extends Importer
{
    protected static ?string $model = Teacher::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('full_name')
                ->label('Nama Lengkap')
                ->requiredMapping()
                ->examples(['Ridho Kurniawan, M.Kom'])
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
                ->examples(['male'])
                ->rules(['required', 'in:male,female']),

            ImportColumn::make('religion')
                ->label('Agama')
                ->examples(['islam'])
                ->rules(['nullable', 'in:islam,kristen,katolik,hindu,budha,konghucu']),

            ImportColumn::make('birth_place')
                ->label('Tempat Lahir')
                ->examples(['Banyuwangi'])
                ->rules(['nullable', 'string']),

            ImportColumn::make('birth_date')
                ->label('Tanggal Lahir')
                ->examples(['1978-05-15'])
                ->rules(['nullable', 'date']),

            ImportColumn::make('employment_status')
                ->label('Status Kepegawaian')
                ->examples(['pns'])
                ->rules(['nullable', 'in:pns,p3k,honorer,gty']),

            ImportColumn::make('phone')
                ->label('No. HP')
                ->examples(['081234567890'])
                ->rules(['nullable', 'string', 'max:20']),

            ImportColumn::make('email')
                ->label('Email')
                ->requiredMapping()
                ->examples(['ridhokurniawan@smkpgri1giri.sch.id'])
                ->rules(['required', 'email', 'unique:teachers,email']),

            ImportColumn::make('address')
                ->label('Alamat')
                ->examples(['Jl. Raya Giri No. 10, Banyuwangi'])
                ->rules(['nullable', 'string']),

            ImportColumn::make('join_date')
                ->label('Tanggal Mulai Bertugas')
                ->examples(['2003-12-01'])
                ->rules(['nullable', 'date']),
        ];
    }

    public function resolveRecord(): ?Teacher
    {
        return Teacher::firstOrNew([
            'email' => $this->data['email'],
        ]);
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import data guru selesai. '.number_format($import->successful_rows).' data berhasil diimport.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' data gagal diimport.';
        }

        return $body;
    }
}
