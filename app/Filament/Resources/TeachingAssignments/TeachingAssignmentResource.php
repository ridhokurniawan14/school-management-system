<?php

namespace App\Filament\Resources\TeachingAssignments;

use App\Filament\Resources\TeachingAssignments\Pages\CreateTeachingAssignment;
use App\Filament\Resources\TeachingAssignments\Pages\EditTeachingAssignment;
use App\Filament\Resources\TeachingAssignments\Pages\ListTeachingAssignments;
use App\Models\AcademicSemester;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class TeachingAssignmentResource extends Resource
{
    protected static ?string $model = TeachingAssignment::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';

    protected static string|\UnitEnum|null $navigationGroup = 'Akademik';

    protected static ?string $navigationLabel = 'Pembagian Tugas';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'Tugas Mengajar';

    protected static ?string $pluralModelLabel = 'Pembagian Tugas Mengajar';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Tugas Mengajar')
                    ->description('Tentukan guru, mata pelajaran, kelas, dan jumlah jam.')
                    ->icon('heroicon-o-briefcase')
                    ->columns(2)
                    ->columnSpanFull() // 🔥 Tambahkan ini biar card form-nya mentok full ke kanan
                    ->schema([
                        Select::make('academic_year_id')
                            ->label('Tahun Ajaran')
                            ->options(AcademicYear::orderBy('name', 'desc')->pluck('name', 'id'))
                            ->default(fn () => AcademicYear::where('is_active', true)->first()?->id)
                            ->required()
                            ->searchable(),

                        Select::make('academic_semester_id')
                            ->label('Semester')
                            ->options(AcademicSemester::orderBy('semester')->pluck('name', 'id'))
                            ->required()
                            ->searchable(),

                        Select::make('teacher_id')
                            ->label('Guru Pengampu')
                            ->options(Teacher::where('role', 'teacher')->orderBy('full_name')->pluck('full_name', 'id'))
                            ->required()
                            ->searchable()
                            ->columnSpanFull(),

                        Select::make('subject_id')
                            ->label('Mata Pelajaran')
                            // 🔥 Modifikasi bagian options untuk memunculkan Kode — Nama Mapel
                            ->options(
                                Subject::orderBy('name')
                                    ->get()
                                    ->mapWithKeys(fn ($subject) => [
                                        $subject->id => $subject->code.' — '.$subject->name,
                                    ])
                            )
                            ->required()
                            ->searchable(),

                        Select::make('classroom_id')
                            ->label('Kelas')
                            ->options(Classroom::orderBy('name')->pluck('name', 'id'))
                            ->required()
                            ->searchable(),

                        TextInput::make('credit_hours')
                            ->label('Jam Pelajaran (Per Minggu)')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->maxValue(40)
                            ->helperText('Contoh: 9 (untuk 9 jam pelajaran per minggu)'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('academicYear.name')
                    ->label('Tahun Ajaran')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('academicSemester.name')
                    ->label('Semester')
                    ->sortable()
                    ->badge(),

                TextColumn::make('teacher.full_name')
                    ->label('Guru Pengampu')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('subject.name')
                    ->label('Mata Pelajaran')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('classroom.name')
                    ->label('Kelas')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('success'),

                TextColumn::make('credit_hours')
                    ->label('Jam/Mgg')
                    ->numeric()
                    ->sortable()
                    ->suffix(' jam'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->relationship('academicYear', 'name'),

                SelectFilter::make('classroom_id')
                    ->label('Kelas')
                    ->relationship('classroom', 'name'),

                SelectFilter::make('teacher_id')
                    ->label('Guru')
                    ->relationship('teacher', 'full_name'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTeachingAssignments::route('/'),
            'create' => CreateTeachingAssignment::route('/create'),
            'edit' => EditTeachingAssignment::route('/{record}/edit'),
        ];
    }
}
