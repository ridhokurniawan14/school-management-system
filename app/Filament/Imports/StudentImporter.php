<?php

namespace App\Filament\Imports;

use App\Models\Competency;
use App\Models\Student;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;

class StudentImporter extends Importer
{
    protected static ?string $model = Student::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('nis')
                ->label('NIS')
                ->requiredMapping()
                ->examples(['2024001'])
                ->rules(['required', 'string', 'max:20', 'unique:students,nis']),

            ImportColumn::make('nisn')
                ->label('NISN')
                ->examples(['0012345678'])
                ->rules(['nullable', 'string', 'max:10']),

            ImportColumn::make('full_name')
                ->label('Nama Lengkap')
                ->requiredMapping()
                ->examples(['Ahmad Fauzi'])
                ->rules(['required', 'string', 'max:255']),

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
                ->examples(['2008-05-15'])
                ->rules(['nullable', 'date']),

            ImportColumn::make('blood_type')
                ->label('Golongan Darah')
                ->examples(['unknown'])
                ->rules(['nullable', 'in:A,B,AB,O,unknown']),

            ImportColumn::make('entry_year')
                ->label('Tahun Masuk')
                ->requiredMapping()
                ->examples(['2024'])
                ->rules(['nullable', 'numeric', 'min:2000']),

            ImportColumn::make('status')
                ->label('Status')
                ->examples(['active'])
                ->rules(['nullable', 'in:active,graduated,transferred,dropped']),

            ImportColumn::make('phone')
                ->label('No. HP')
                ->examples(['08123456789'])
                ->rules(['nullable', 'string', 'max:20']),

            ImportColumn::make('address')
                ->label('Alamat')
                ->examples(['Jl. Raya Giri No. 5, Banyuwangi'])
                ->rules(['nullable', 'string']),

            ImportColumn::make('province')
                ->label('Provinsi')
                ->examples(['Jawa Timur'])
                ->rules(['nullable', 'string']),

            ImportColumn::make('city')
                ->label('Kabupaten / Kota')
                ->examples(['Kabupaten Banyuwangi'])
                ->rules(['nullable', 'string']),

            ImportColumn::make('district')
                ->label('Kecamatan')
                ->examples(['Giri'])
                ->rules(['nullable', 'string']),
        ];
    }

    public static function getOptionsFormComponents(): array
    {
        return [
            \Filament\Forms\Components\Select::make('competency_id')
                ->label('Kompetensi Keahlian / Jurusan')
                ->options(
                    \App\Models\Competency::where('is_active', true)
                        ->with('major')
                        ->get()
                        ->filter(fn($c) => $c->major !== null)
                        ->mapWithKeys(fn($c) => [
                            $c->id => $c->major->name . ' — ' . $c->name,
                        ])
                )
                ->searchable()
                ->required()
                ->helperText('Pilih jurusan untuk semua siswa dalam file ini.'),
        ];
    }

    public function resolveRecord(): ?Student
    {
        $student = Student::firstOrNew([
            'nis' => $this->data['nis'],
        ]);

        // Inject competency_id dari dropdown pilihan sebelum upload
        if (!empty($this->options['competency_id'])) {
            $student->competency_id = $this->options['competency_id'];
        }

        return $student;
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Import data siswa selesai. ' . number_format($import->successful_rows) . ' data berhasil diimport.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . number_format($failedRowsCount) . ' data gagal diimport.';
        }

        return $body;
    }
}
