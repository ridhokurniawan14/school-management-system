<?php

namespace App\Filament\Resources\Classrooms\RelationManagers;

use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\Student;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class StudentsRelationManager extends RelationManager
{
    protected static string $relationship = 'classroomStudents';
    protected static ?string $title = 'Daftar Siswa';

    public function table(Table $table): Table
    {
        $classroom  = $this->getOwnerRecord();

        return $table
            ->recordTitleAttribute('student.full_name')
            ->heading(
                fn() => 'Daftar Siswa — ' .
                    ClassroomStudent::where('classroom_id', $this->getOwnerRecord()->id)->count() .
                    ' / ' .
                    ($this->getOwnerRecord()->capacity ?? 36) .
                    ' siswa'
            )
            ->columns([
                TextColumn::make('student.nis')
                    ->label('NIS')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('student.full_name')
                    ->label('Nama Siswa')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('student.competency.name')
                    ->label('Jurusan')
                    ->badge()
                    ->color('info')
                    ->placeholder('-'),

                TextColumn::make('student.gender')
                    ->label('L/P')
                    ->formatStateUsing(fn($state) => match ($state) {
                        'male'   => 'L',
                        'female' => 'P',
                        default  => '-',
                    })
                    ->toggleable(),

                TextColumn::make('student.phone')
                    ->label('No. HP')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'active'  => 'success',
                        'moved'   => 'warning',
                        'dropped' => 'danger',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match ($state) {
                        'active'  => 'Aktif',
                        'moved'   => 'Pindah',
                        'dropped' => 'Keluar',
                        default   => $state,
                    }),
            ])
            ->headerActions([
                // ── Import Siswa ke Kelas via CSV ─────────────────────────
                Action::make('import_to_class')
                    ->label('Import dari Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->form([
                        FileUpload::make('file')
                            ->label('File Excel / CSV')
                            ->helperText('Kolom wajib: NIS siswa. Pastikan tidak ada spasi di awal/akhir NIS.')
                            ->hint('Download Template')
                            ->hintActions([
                                Action::make('download_csv')
                                    ->label('CSV')
                                    ->icon('heroicon-o-arrow-down-tray')
                                    ->color('info')
                                    ->url(route('download.template.siswa.kelas'))
                                    ->openUrlInNewTab(),

                                Action::make('download_excel')
                                    ->label('Excel')
                                    ->icon('heroicon-o-arrow-down-tray')
                                    ->color('success')
                                    ->url(route('download.template.siswa.kelas.excel'))
                                    ->openUrlInNewTab(),
                            ])
                            ->acceptedFileTypes([
                                'text/csv',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            ])
                            ->disk('local')
                            ->directory('tmp/imports')
                            ->required(),
                    ])
                    ->action(function (array $data) use ($classroom): void {

                        // ✅ Fix path
                        $disk = Storage::disk('local');
                        $path = $disk->path($data['file']);

                        if (!file_exists($path)) {
                            Notification::make()
                                ->danger()
                                ->title('File tidak ditemukan')
                                ->body('Path: ' . $path)
                                ->send();
                            return;
                        }

                        // Baca file — support CSV dan Excel
                        if (str_ends_with($data['file'], '.csv')) {
                            $rows    = array_map('str_getcsv', file($path));
                            array_shift($rows);
                            $nisList = array_column($rows, 0);
                        } else {
                            $spreadsheet = IOFactory::load($path);
                            $sheet       = $spreadsheet->getActiveSheet()->toArray();
                            array_shift($sheet);
                            $nisList = array_column($sheet, 0);
                        }

                        $nisList = array_filter(array_map('trim', $nisList));

                        $existingIds = ClassroomStudent::where(
                            'academic_year_id',
                            $classroom->academic_year_id
                        )->pluck('student_id')->toArray();

                        $added = $skipped = $notFound = 0;

                        foreach ($nisList as $nis) {
                            $student = Student::where('nis', $nis)->first();

                            if (!$student) {
                                $notFound++;
                                continue;
                            }
                            if (in_array($student->id, $existingIds)) {
                                $skipped++;
                                continue;
                            }

                            ClassroomStudent::create([
                                'student_id'       => $student->id,
                                'classroom_id'     => $classroom->id,
                                'academic_year_id' => $classroom->academic_year_id,
                                'status'           => 'active',
                            ]);

                            if (empty($student->entry_year)) {
                                $startYear   = $classroom->academicYear?->start_date
                                    ? Carbon::parse($classroom->academicYear->start_date)->year
                                    : now()->year;
                                $gradeOffset = (int)$classroom->grade - 10;
                                $student->update(['entry_year' => $startYear - $gradeOffset]);
                            }

                            $added++;
                        }

                        // Hapus file tmp
                        $disk->delete($data['file']);

                        Notification::make()
                            ->success()
                            ->title("Import selesai: {$added} siswa ditambahkan")
                            ->body("Dilewati (sudah ada): {$skipped} | Tidak ditemukan: {$notFound}")
                            ->persistent()
                            ->send();
                    }),
                // ── Tambah Siswa ──────────────────────────────────────────
                Action::make('add_student')
                    ->label('Tambah Siswa')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->form([
                        Select::make('student_ids')
                            ->label('Pilih Siswa')
                            ->options(function () use ($classroom) {
                                $existingIds = ClassroomStudent::where(
                                    'academic_year_id',
                                    $classroom->academic_year_id
                                )->pluck('student_id')->toArray();

                                return Student::where('status', 'active')
                                    ->whereNotIn('id', $existingIds)
                                    ->orderBy('full_name')
                                    ->get()
                                    ->mapWithKeys(fn($s) => [
                                        $s->id => $s->nis . ' — ' . $s->full_name,
                                    ]);
                            })
                            ->multiple()
                            ->searchable()
                            ->required()
                            ->helperText('Hanya siswa aktif yang belum terdaftar di kelas manapun tahun ajaran ini.'),
                    ])
                    ->action(function (array $data) use ($classroom): void {
                        foreach ($data['student_ids'] as $studentId) {
                            ClassroomStudent::updateOrCreate([
                                'student_id'       => $studentId,
                                'academic_year_id' => $classroom->academic_year_id,
                            ], [
                                'classroom_id' => $classroom->id,
                                'status'       => 'active',
                            ]);

                            $student = Student::find($studentId);
                            if ($student && empty($student->entry_year)) {
                                $startYear   = $classroom->academicYear?->start_date
                                    ? Carbon::parse($classroom->academicYear->start_date)->year
                                    : now()->year;
                                $gradeOffset = (int)$classroom->grade - 10;
                                $student->update(['entry_year' => $startYear - $gradeOffset]);
                            }
                        }

                        Notification::make()
                            ->success()
                            ->title('Siswa berhasil ditambahkan ke kelas.')
                            ->send();
                    }), // ── Export Semua Siswa di Kelas Ini ──────────────────────
                ExportAction::make()
                    ->label('Export')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->exports([
                        ExcelExport::make()
                            ->withColumns(self::exportColumns())
                            ->withFilename('siswa-' . $classroom->name . '-' . now()->format('Y-m-d')),
                    ]),
            ])
            ->actions([
                DeleteAction::make()
                    ->label('Keluarkan')
                    ->modalHeading('Keluarkan siswa dari kelas ini?')
                    ->modalDescription('Siswa akan dikeluarkan dari kelas. Data siswa tidak dihapus.')
                    ->modalSubmitActionLabel('Ya, Keluarkan'),
            ])
            ->bulkActions([
                BulkActionGroup::make([

                    // ── Bulk Keluarkan ────────────────────────────────────
                    DeleteBulkAction::make()
                        ->label('Keluarkan Terpilih')
                        ->modalHeading('Keluarkan siswa terpilih?')
                        ->modalDescription('Siswa yang dicentang akan dikeluarkan dari kelas. Data siswa tidak dihapus.')
                        ->modalSubmitActionLabel('Ya, Keluarkan'),

                    // ── Bulk Naik Kelas ───────────────────────────────────
                    BulkAction::make('naik_kelas')
                        ->label('Naik Kelas')
                        ->visible(fn() => $this->getOwnerRecord()->grade != '12')
                        ->icon('heroicon-o-arrow-up-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Naik Kelas')
                        ->modalDescription('Siswa yang dicentang akan dipindahkan ke kelas berikutnya. Kelas XII akan diset status Lulus.')
                        ->modalSubmitActionLabel('Ya, Naik Kelas')
                        ->form([
                            Select::make('target_academic_year_id')
                                ->label('Tahun Ajaran Tujuan')
                                ->options(
                                    AcademicYear::orderBy('name', 'desc')
                                        ->pluck('name', 'id')
                                )
                                ->required()
                                ->helperText('Tahun ajaran baru tempat siswa akan dipindahkan.'),
                        ])
                        ->action(function (Collection $records, array $data) use ($classroom): void {
                            $nextGrade    = (int)$classroom->grade + 1;
                            $isGraduating = $classroom->grade == '12';

                            foreach ($records as $cs) {
                                $student = $cs->student;
                                if (!$student) continue;

                                if ($isGraduating) {
                                    $student->update(['status' => 'graduated']);
                                } else {
                                    $targetClassroom = Classroom::where('academic_year_id', $data['target_academic_year_id'])
                                        ->where('grade', (string)$nextGrade)
                                        ->where('competency_id', $classroom->competency_id)
                                        ->first();

                                    if (!$targetClassroom) {
                                        $targetClassroom = Classroom::create([
                                            'name'                => $classroom->name,
                                            'academic_year_id'    => $data['target_academic_year_id'],
                                            'grade'               => (string)$nextGrade,
                                            'competency_id'       => $classroom->competency_id,
                                            'capacity'            => $classroom->capacity,
                                            'homeroom_teacher_id' => $classroom->homeroom_teacher_id,
                                            'is_active'           => true,
                                        ]);
                                    }

                                    ClassroomStudent::updateOrCreate([
                                        'student_id'       => $student->id,
                                        'academic_year_id' => $data['target_academic_year_id'],
                                    ], [
                                        'classroom_id' => $targetClassroom->id,
                                        'status'       => 'active',
                                    ]);
                                }
                            }

                            Notification::make()
                                ->success()
                                ->title('Proses naik kelas berhasil!')
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    BulkAction::make('luluskan')
                        ->label('Luluskan')
                        ->icon('heroicon-o-academic-cap')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Luluskan Siswa Terpilih')
                        ->modalDescription('Siswa yang dicentang akan diset status LULUS dan menjadi Alumni. Tindakan ini tidak dapat dibatalkan.')
                        ->modalSubmitActionLabel('Ya, Luluskan')
                        ->visible(fn() => $this->getOwnerRecord()->grade == '12') // ← hanya muncul di kelas XII
                        ->form([
                            Select::make('graduation_year')
                                ->label('Tahun Lulus')
                                ->options(function () {
                                    $years = [];
                                    for ($y = now()->year; $y >= now()->year - 2; $y--) {
                                        $years[$y] = $y;
                                    }
                                    return $years;
                                })
                                ->default(now()->year)
                                ->required()
                                ->helperText('Tahun kelulusan siswa.'),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            foreach ($records as $cs) {
                                $student = $cs->student;
                                if (!$student) continue;

                                // Set status lulus
                                $student->update([
                                    'status'     => 'graduated',
                                    'entry_year' => $student->entry_year, // tetap
                                ]);

                                // Update status di classroom_students
                                $cs->update(['status' => 'moved']);
                            }

                            Notification::make()
                                ->success()
                                ->title(count($records) . ' siswa berhasil diluluskan!')
                                ->body('Status siswa telah diubah menjadi Lulus / Alumni.')
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    // ── Bulk Export Terpilih ──────────────────────────────
                    ExportBulkAction::make()
                        ->label('Export Terpilih')
                        ->exports([
                            ExcelExport::make()
                                ->withColumns(self::exportColumns())
                                ->withFilename('siswa-terpilih-' . now()->format('Y-m-d')),
                        ]),
                ]),
            ]);
    }

    // ── Kolom export lengkap ──────────────────────────────────────────────
    protected static function exportColumns(): array
    {
        return [
            Column::make('student.nis')->heading('NIS'),
            Column::make('student.nisn')->heading('NISN'),
            Column::make('student.full_name')->heading('Nama Lengkap'),
            Column::make('student.gender')
                ->heading('Jenis Kelamin')
                ->formatStateUsing(fn($state) => match ($state) {
                    'male'   => 'Laki-laki',
                    'female' => 'Perempuan',
                    default  => '-',
                }),
            Column::make('student.religion')
                ->heading('Agama')
                ->formatStateUsing(fn($state) => ucfirst($state ?? '-')),
            Column::make('student.birth_place')->heading('Tempat Lahir'),
            Column::make('student.birth_date')
                ->heading('Tanggal Lahir')
                ->formatStateUsing(fn($state) => $state
                    ? Carbon::parse($state)->format('d/m/Y') : '-'),
            Column::make('student.blood_type')->heading('Gol. Darah'),
            Column::make('student.competency.name')->heading('Jurusan'),
            Column::make('student.entry_year')->heading('Tahun Masuk'),
            Column::make('student.status')
                ->heading('Status Siswa')
                ->formatStateUsing(fn($state) => match ($state) {
                    'active'      => 'Aktif',
                    'graduated'   => 'Lulus',
                    'transferred' => 'Pindah',
                    'dropped'     => 'Keluar',
                    default       => $state,
                }),
            Column::make('student.phone')->heading('No. HP'),
            Column::make('student.email')->heading('Email'),
            Column::make('student.address')->heading('Alamat'),
            Column::make('student.province')->heading('Provinsi'),
            Column::make('student.city')->heading('Kota/Kab'),
            Column::make('student.district')->heading('Kecamatan'),
            Column::make('status')
                ->heading('Status di Kelas')
                ->formatStateUsing(fn($state) => match ($state) {
                    'active'  => 'Aktif',
                    'moved'   => 'Pindah',
                    'dropped' => 'Keluar',
                    default   => $state,
                }),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }
}
