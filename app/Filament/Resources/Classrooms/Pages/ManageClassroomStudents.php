<?php

namespace App\Filament\Resources\Classrooms\Pages;

use App\Filament\Resources\Classrooms\ClassroomResource;
use App\Models\Classroom;
use App\Models\ClassroomStudent;
use App\Models\Student;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Tables\Actions\Action as TableAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class ManageClassroomStudents extends Page implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = ClassroomResource::class;

    // ← hapus static
    protected string $view = 'filament.resources.classrooms.pages.manage-classroom-students';

    public Classroom $record;

    public function mount(Classroom $record): void
    {
        $this->record = $record;
    }

    public function getTitle(): string
    {
        return 'Siswa — '.$this->record->name;
    }

    public function table(Table $table): Table
    {
        return $table
            // ← pakai ClassroomStudent query langsung, bukan relationship
            ->query(
                ClassroomStudent::query()
                    ->where('classroom_id', $this->record->id)
                    ->with(['student', 'student.competency'])
            )
            ->columns([
                TextColumn::make('student.nis')
                    ->label('NIS')
                    ->searchable(),

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

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'active' => 'success',
                        'moved' => 'warning',
                        'dropped' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'active' => 'Aktif',
                        'moved' => 'Pindah',
                        'dropped' => 'Keluar',
                        default => $state,
                    }),
            ])
            ->headerActions([
                // ← pakai TableAction bukan Action biasa
                TableAction::make('add_student')
                    ->label('Tambah Siswa')
                    ->icon('heroicon-o-plus')
                    ->color('success')
                    ->form([
                        Select::make('student_ids')
                            ->label('Pilih Siswa')
                            ->options(function () {
                                // ← pakai whereNotIn + subquery, hindari whereDoesntHave
                                $existingStudentIds = ClassroomStudent::where(
                                    'academic_year_id',
                                    $this->record->academic_year_id
                                )->pluck('student_id')->toArray();

                                return Student::where('status', 'active')
                                    ->whereNotIn('id', $existingStudentIds)
                                    ->orderBy('full_name')
                                    ->get()
                                    ->mapWithKeys(fn ($s) => [
                                        $s->id => $s->nis.' — '.$s->full_name,
                                    ]);
                            })
                            ->multiple()
                            ->searchable()
                            ->required()
                            ->helperText('Hanya siswa aktif yang belum masuk kelas di tahun ajaran ini.'),
                    ])
                    ->action(function (array $data): void {
                        foreach ($data['student_ids'] as $studentId) {
                            ClassroomStudent::updateOrCreate([
                                'student_id' => $studentId,
                                'academic_year_id' => $this->record->academic_year_id,
                            ], [
                                'classroom_id' => $this->record->id,
                                'status' => 'active',
                            ]);
                        }

                        Notification::make()
                            ->success()
                            ->title('Siswa berhasil ditambahkan ke kelas.')
                            ->send();
                    }),
            ])
            ->actions([
                DeleteAction::make()
                    ->label('Keluarkan')
                    ->modalHeading('Keluarkan siswa dari kelas ini?'),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali ke Daftar Kelas')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
        ];
    }
}
