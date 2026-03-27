<?php

namespace App\Filament\Resources\Classrooms;

use App\Filament\Resources\Classrooms\Pages\CreateClassroom;
use App\Filament\Resources\Classrooms\Pages\EditClassroom;
use App\Filament\Resources\Classrooms\Pages\ListClassrooms;
use App\Filament\Resources\Classrooms\RelationManagers\StudentsRelationManager;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Competency;
use App\Models\Teacher;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ClassroomResource extends Resource
{
    protected static ?string $model = Classroom::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-squares-2x2';
    protected static string|\UnitEnum|null $navigationGroup = 'Akademik';
    protected static ?string $navigationLabel = 'Kelas & Rombel';
    protected static ?int $navigationSort = 4;
    protected static ?string $modelLabel = 'Kelas';
    protected static ?string $pluralModelLabel = 'Kelas & Rombel';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kelas')
                    ->description('Data kelas / rombongan belajar.')
                    ->icon('heroicon-o-squares-2x2')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Kelas')
                            ->placeholder('XII RPL 1')
                            ->helperText('Contoh: X TKJ 1, XI RPL 2, XII MM 1')
                            ->required()
                            ->maxLength(50)
                            ->columnSpanFull(),

                        Select::make('grade')
                            ->label('Tingkat')
                            ->options([
                                '10' => 'Kelas X',
                                '11' => 'Kelas XI',
                                '12' => 'Kelas XII',
                            ])
                            ->required()
                            ->helperText('Tingkat kelas.'),

                        Select::make('academic_year_id')
                            ->label('Tahun Ajaran')
                            ->options(
                                AcademicYear::orderBy('name', 'desc')
                                    ->pluck('name', 'id')
                            )
                            ->default(fn() => AcademicYear::where('is_active', true)->first()?->id)
                            ->searchable()
                            ->required()
                            ->helperText('Tahun ajaran kelas ini berlaku.'),

                        Select::make('competency_id')
                            ->label('Kompetensi Keahlian / Jurusan')
                            ->options(
                                Competency::where('is_active', true)
                                    ->with('major')
                                    ->get()
                                    ->filter(fn($c) => $c->major !== null)
                                    ->mapWithKeys(fn($c) => [
                                        $c->id => $c->major->name . ' — ' . $c->name,
                                    ])
                            )
                            ->searchable()
                            ->nullable()
                            ->helperText('Jurusan untuk kelas ini.'),

                        Select::make('homeroom_teacher_id')
                            ->label('Wali Kelas')
                            ->options(
                                Teacher::where('is_active', true)
                                    ->where('role', 'teacher')
                                    ->orderBy('full_name')
                                    ->pluck('full_name', 'id')
                            )
                            ->searchable()
                            ->nullable()
                            ->helperText('Guru yang menjadi wali kelas.'),

                        TextInput::make('room_number')
                            ->label('Ruang Kelas')
                            ->placeholder('Ruang 12')
                            ->helperText('Nomor atau nama ruangan fisik.')
                            ->maxLength(50),

                        TextInput::make('capacity')
                            ->label('Kapasitas')
                            ->placeholder('36')
                            ->helperText('Jumlah maksimal siswa dalam kelas.')
                            ->numeric()
                            ->default(36)
                            ->minValue(1)
                            ->maxValue(50),

                        Toggle::make('is_active')
                            ->label('Kelas Aktif')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('gray'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('5s')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Kelas')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('grade')
                    ->label('Tingkat')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn($state) => 'Kelas ' . $state),

                TextColumn::make('academicYear.name')
                    ->label('Tahun Ajaran')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('competency.name')
                    ->label('Jurusan')
                    ->placeholder('-')
                    ->badge()
                    ->color('success'),

                TextColumn::make('homeroomTeacher.full_name')
                    ->label('Wali Kelas')
                    ->placeholder('-')
                    ->searchable(),

                TextColumn::make('classroom_students_count')
                    ->label('Siswa')
                    ->counts('classroomStudents')
                    ->badge()
                    ->color('warning')
                    ->suffix(' siswa'),

                TextColumn::make('capacity')
                    ->label('Kapasitas')
                    ->suffix(' siswa')
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->defaultSort('name', 'asc')
            ->filters([
                SelectFilter::make('grade')
                    ->label('Tingkat')
                    ->options([
                        '10' => 'Kelas X',
                        '11' => 'Kelas XI',
                        '12' => 'Kelas XII',
                    ]),

                SelectFilter::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->options(
                        AcademicYear::orderBy('name', 'desc')
                            ->pluck('name', 'id')
                    ),

                SelectFilter::make('competency_id')
                    ->label('Jurusan')
                    ->options(
                        Competency::where('is_active', true)
                            ->pluck('name', 'id')
                    ),
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

    public static function getRelations(): array
    {
        return [
            StudentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListClassrooms::route('/'),
            'create' => CreateClassroom::route('/create'),
            'edit'   => EditClassroom::route('/{record}/edit'),
        ];
    }
}
