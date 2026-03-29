<?php

namespace App\Filament\Resources\Subjects;

use App\Filament\Resources\Subjects\Pages\CreateSubject;
use App\Filament\Resources\Subjects\Pages\EditSubject;
use App\Filament\Resources\Subjects\Pages\ListSubjects;
use App\Models\AcademicSemester;
use App\Models\AcademicYear;
use App\Models\Classroom;
use App\Models\Competency;
use App\Models\Subject;
use App\Models\SubjectKkm;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SubjectResource extends Resource
{
    protected static ?string $model = Subject::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-book-open';

    protected static string|\UnitEnum|null $navigationGroup = 'Akademik';

    protected static ?string $navigationLabel = 'Mata Pelajaran';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Mata Pelajaran';

    protected static ?string $pluralModelLabel = 'Mata Pelajaran';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('subject_tabs')
                    ->tabs([

                        // ── Tab 1: Info Mapel ─────────────────────────────
                        Tab::make('Info Mata Pelajaran')
                            ->icon('heroicon-o-book-open')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Nama Mata Pelajaran')
                                            ->placeholder('Pemrograman Web dan Perangkat Bergerak')
                                            ->helperText('Nama lengkap mata pelajaran.')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpanFull(),

                                        TextInput::make('code')
                                            ->label('Kode')
                                            ->placeholder('PWB')
                                            ->helperText('Kode singkat mata pelajaran.')
                                            ->required()
                                            ->maxLength(20)
                                            ->unique(ignoreRecord: true),

                                        Select::make('subject_type')
                                            ->label('Kelompok Mata Pelajaran')
                                            ->options([
                                                '── Kurikulum Merdeka ──' => [
                                                    'umum' => 'Umum',
                                                    'kejuruan' => 'Kejuruan',
                                                    'pilihan' => 'Pilihan',
                                                ],
                                                '── Kurikulum K13 ──' => [
                                                    'normative' => 'Normatif',
                                                    'adaptive' => 'Adaptif',
                                                    'productive' => 'Produktif',
                                                ],
                                                '── Umum ──' => [
                                                    'mulok' => 'Muatan Lokal',
                                                ],
                                            ])
                                            ->required()
                                            ->helperText('Kurikulum Merdeka: Umum, Kejuruan, Pilihan. K13: Normatif, Adaptif, Produktif.')
                                            ->live(),

                                        Select::make('competency_id')
                                            ->label('Kompetensi Keahlian')
                                            ->options(
                                                Competency::where('is_active', true)
                                                    ->with('major')
                                                    ->get()
                                                    ->filter(fn ($c) => $c->major !== null)
                                                    ->mapWithKeys(fn ($c) => [
                                                        $c->id => $c->major->name.' — '.$c->name,
                                                    ])
                                            )
                                            ->searchable()
                                            ->placeholder('Kosongkan jika berlaku untuk semua jurusan')
                                            ->helperText('Isi hanya untuk mapel Produktif yang spesifik jurusan.')
                                            ->visible(fn ($get) => in_array($get('subject_type'), ['productive', 'kejuruan'])),

                                        Toggle::make('is_active')
                                            ->label('Aktif')
                                            ->default(true)
                                            ->onColor('success')
                                            ->offColor('gray'),
                                    ]),
                            ]),

                        // ── Tab 2: KKM ────────────────────────────────────
                        Tab::make('KKM')
                            ->icon('heroicon-o-chart-bar')
                            ->schema([

                                // ── Form Set KKM Massal ───────────────────
                                Section::make('Set KKM Massal')
                                    ->description('Set KKM sekaligus untuk banyak kelas dalam 1 semester.')
                                    ->icon('heroicon-o-pencil-square')
                                    ->collapsible()
                                    ->columns(2)
                                    ->schema([
                                        Select::make('kkm_academic_year_id')
                                            ->label('Tahun Ajaran')
                                            ->options(
                                                AcademicYear::orderBy('name', 'desc')
                                                    ->pluck('name', 'id')
                                            )
                                            ->default(fn () => AcademicYear::where('is_active', true)->first()?->id)
                                            ->searchable()
                                            ->live()
                                            ->dehydrated(false),

                                        Select::make('kkm_semester_id')
                                            ->label('Semester')
                                            ->options(
                                                fn ($get) => AcademicSemester::where(
                                                    'academic_year_id',
                                                    $get('kkm_academic_year_id')
                                                )
                                                    ->orderBy('semester')
                                                    ->pluck('name', 'id')
                                            )
                                            ->searchable()
                                            ->live()
                                            ->dehydrated(false),

                                        Select::make('kkm_classroom_ids')
                                            ->label('Kelas')
                                            ->options(
                                                fn ($get) => Classroom::where('is_active', true)
                                                    ->when(
                                                        $get('kkm_academic_year_id'),
                                                        fn ($q, $v) => $q->where('academic_year_id', $v)
                                                    )
                                                    ->orderBy('name')
                                                    ->pluck('name', 'id')
                                            )
                                            ->multiple()
                                            ->searchable()
                                            ->helperText('Pilih satu atau lebih kelas.')
                                            ->dehydrated(false)
                                            ->columnSpanFull(),

                                        TextInput::make('kkm_value')
                                            ->label('Nilai KKM')
                                            ->placeholder('75')
                                            ->numeric()
                                            ->default(75)
                                            ->minValue(0)
                                            ->maxValue(100)
                                            ->dehydrated(false),

                                        // ── Tombol Simpan KKM ────────────
                                        Actions::make([
                                            Action::make('simpan_kkm')
                                                ->label('Simpan KKM')
                                                ->icon('heroicon-o-check-circle')
                                                ->color('success')
                                                ->size('lg')
                                                ->action(function (callable $get, callable $set, $record) {
                                                    if (! $record) {
                                                        Notification::make()->warning()->title('Simpan mata pelajaran terlebih dahulu')->send();

                                                        return;
                                                    }

                                                    $classroomIds = $get('kkm_classroom_ids') ?? [];
                                                    $semesterId = $get('kkm_semester_id');
                                                    $yearId = $get('kkm_academic_year_id');
                                                    $kkmValue = $get('kkm_value') ?? 75;

                                                    if (empty($classroomIds) || ! $semesterId) {
                                                        Notification::make()
                                                            ->warning()
                                                            ->title('Pilih kelas dan semester terlebih dahulu')
                                                            ->send();

                                                        return;
                                                    }

                                                    $saved = 0;
                                                    foreach ($classroomIds as $classroomId) {
                                                        SubjectKkm::updateOrCreate([
                                                            'subject_id' => $record->id,
                                                            'classroom_id' => $classroomId,
                                                            'academic_semester_id' => $semesterId,
                                                        ], [
                                                            'academic_year_id' => $yearId,
                                                            'kkm' => $kkmValue,
                                                        ]);
                                                        $saved++;
                                                    }

                                                    $allKkms = $record->kkms()->with('classroom')->get();

                                                    foreach (['10', '11', '12'] as $grade) {
                                                        $set('kkms_list_'.$grade, $allKkms->filter(fn ($k) => $k->classroom?->grade == $grade)->map(fn ($k) => [
                                                            'id' => $k->id,
                                                            'academic_year_id' => $k->academic_year_id,
                                                            'academic_semester_id' => $k->academic_semester_id,
                                                            'classroom_id' => $k->classroom_id,
                                                            'kkm' => $k->kkm,
                                                        ])->values()->toArray());
                                                    }

                                                    Notification::make()
                                                        ->success()
                                                        ->title("KKM berhasil disimpan untuk {$saved} kelas")
                                                        ->send();
                                                }),
                                        ])->columnSpanFull(),
                                    ]),

                                // ── Duplikasi KKM ────────────────────────
                                Section::make('Duplikasi KKM')
                                    ->description('Salin semua KKM dari tahun ajaran lama ke tahun ajaran baru.')
                                    ->icon('heroicon-o-document-duplicate')
                                    ->collapsible()
                                    ->collapsed() // default collapsed
                                    ->columns(2)
                                    ->schema([
                                        Select::make('kkm_source_year_id')
                                            ->label('Dari Tahun Ajaran')
                                            ->options(
                                                AcademicYear::orderBy('name', 'desc')
                                                    ->pluck('name', 'id')
                                            )
                                            ->searchable()
                                            ->dehydrated(false)
                                            ->helperText('Tahun ajaran sumber KKM.'),

                                        Select::make('kkm_target_year_id')
                                            ->label('Ke Tahun Ajaran')
                                            ->options(
                                                AcademicYear::orderBy('name', 'desc')
                                                    ->pluck('name', 'id')
                                            )
                                            ->searchable()
                                            ->dehydrated(false)
                                            ->helperText('Tahun ajaran tujuan KKM.'),

                                        Actions::make([
                                            Action::make('duplikasi_kkm')
                                                ->label('Duplikasi KKM')
                                                ->icon('heroicon-o-document-duplicate')
                                                ->color('info')
                                                ->size('lg')
                                                ->requiresConfirmation()
                                                ->modalHeading('Duplikasi KKM')
                                                ->modalDescription('KKM dari tahun sumber akan disalin ke tahun tujuan. KKM yang sudah ada tidak akan ditimpa.')
                                                ->action(function (callable $get, callable $set, $record) { // 🔥 Tambahkan $set di sini
                                                    if (! $record) {
                                                        Notification::make()->warning()->title('Simpan mata pelajaran terlebih dahulu')->send();

                                                        return;
                                                    }

                                                    $sourceYearId = $get('kkm_source_year_id');
                                                    $targetYearId = $get('kkm_target_year_id');

                                                    if (! $sourceYearId || ! $targetYearId) {
                                                        Notification::make()->warning()->title('Pilih tahun ajaran sumber dan tujuan')->send();

                                                        return;
                                                    }

                                                    if ($sourceYearId === $targetYearId) {
                                                        Notification::make()->warning()->title('Tahun ajaran sumber dan tujuan tidak boleh sama')->send();

                                                        return;
                                                    }

                                                    $targetSemesters = AcademicSemester::where('academic_year_id', $targetYearId)
                                                        ->pluck('id', 'semester');

                                                    $sourceKkms = SubjectKkm::where('subject_id', $record->id)
                                                        ->where('academic_year_id', $sourceYearId)
                                                        ->with(['academicSemester', 'classroom'])
                                                        ->get();

                                                    $copied = 0;
                                                    $skipped = 0;

                                                    foreach ($sourceKkms as $kkm) {
                                                        $targetSemesterId = $targetSemesters[$kkm->academicSemester?->semester] ?? null;
                                                        if (! $targetSemesterId) {
                                                            $skipped++;

                                                            continue;
                                                        }

                                                        $targetClassroom = Classroom::where('academic_year_id', $targetYearId)
                                                            ->where('name', $kkm->classroom?->name)
                                                            ->first();
                                                        if (! $targetClassroom) {
                                                            $skipped++;

                                                            continue;
                                                        }

                                                        $exists = SubjectKkm::where([
                                                            'subject_id' => $record->id,
                                                            'classroom_id' => $targetClassroom->id,
                                                            'academic_semester_id' => $targetSemesterId,
                                                        ])->exists();

                                                        if (! $exists) {
                                                            SubjectKkm::create([
                                                                'subject_id' => $record->id,
                                                                'classroom_id' => $targetClassroom->id,
                                                                'academic_year_id' => $targetYearId,
                                                                'academic_semester_id' => $targetSemesterId,
                                                                'kkm' => $kkm->kkm,
                                                            ]);
                                                            $copied++;
                                                        } else {
                                                            $skipped++;
                                                        }
                                                    }

                                                    $allKkms = $record->kkms()->with('classroom')->get();

                                                    foreach (['10', '11', '12'] as $grade) {
                                                        $set('kkms_list_'.$grade, $allKkms->filter(fn ($k) => $k->classroom?->grade == $grade)->map(fn ($k) => [
                                                            'id' => $k->id,
                                                            'academic_year_id' => $k->academic_year_id,
                                                            'academic_semester_id' => $k->academic_semester_id,
                                                            'classroom_id' => $k->classroom_id,
                                                            'kkm' => $k->kkm,
                                                        ])->values()->toArray());
                                                    }

                                                    Notification::make()
                                                        ->success()
                                                        ->title("Duplikasi selesai: {$copied} KKM disalin")
                                                        ->body("Dilewati: {$skipped}")
                                                        ->send();
                                                }),
                                        ])->columnSpanFull(),
                                    ]),

                                // ── Daftar KKM ───────────────────────────
                                Section::make('Daftar KKM')
                                    ->description('KKM yang sudah tersimpan. Klik nilai KKM untuk mengedit langsung.')
                                    ->icon('heroicon-o-table-cells')
                                    ->collapsible()
                                    ->collapsed()
                                    ->schema([
                                        Tabs::make('KKM Per Tingkat')
                                            ->tabs([
                                                Tab::make('Kelas X (10)')
                                                    ->schema([self::getKkmRepeater('10')]),

                                                Tab::make('Kelas XI (11)')
                                                    ->schema([self::getKkmRepeater('11')]),

                                                Tab::make('Kelas XII (12)')
                                                    ->schema([self::getKkmRepeater('12')]),
                                            ]),
                                    ]),
                            ]),

                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Mata Pelajaran')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('code')
                    ->label('Kode')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('subject_type')
                    ->label('Kelompok')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'umum' => 'info',
                        'kejuruan' => 'success',
                        'pilihan' => 'warning',
                        'normative' => 'info',
                        'adaptive' => 'warning',
                        'productive' => 'success',
                        'mulok' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'umum' => 'Umum',
                        'kejuruan' => 'Kejuruan',
                        'pilihan' => 'Pilihan',
                        'normative' => 'Normatif',
                        'adaptive' => 'Adaptif',
                        'productive' => 'Produktif',
                        'mulok' => 'Mulok',
                        default => $state,
                    }),
                TextColumn::make('competency.name')
                    ->label('Jurusan')
                    ->placeholder('Semua Jurusan')
                    ->badge()
                    ->color('success')
                    ->toggleable(),

                TextColumn::make('kkms_count')
                    ->label('KKM')
                    ->counts('kkms')
                    ->badge()
                    ->color('warning')
                    ->suffix(' kelas'),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->defaultSort('name', 'asc')
            ->filters([
                SelectFilter::make('subject_type')
                    ->label('Kelompok')
                    ->options([
                        'umum' => 'Umum (Merdeka)',
                        'kejuruan' => 'Kejuruan (Merdeka)',
                        'pilihan' => 'Pilihan (Merdeka)',
                        'normative' => 'Normatif (K13)',
                        'adaptive' => 'Adaptif (K13)',
                        'productive' => 'Produktif (K13)',
                        'mulok' => 'Muatan Lokal',
                    ]),

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

    public static function getPages(): array
    {
        return [
            'index' => ListSubjects::route('/'),
            'create' => CreateSubject::route('/create'),
            'edit' => EditSubject::route('/{record}/edit'),
        ];
    }

    public static function getKkmRepeater(string $grade): Repeater
    {
        return Repeater::make('kkms_list_'.$grade)
            ->label('')
            ->afterStateHydrated(function ($component, $record) use ($grade) {
                if (! $record) {
                    return;
                }

                $component->state(
                    $record->kkms()
                        ->whereHas('classroom', fn ($q) => $q->where('grade', $grade))
                        ->get()
                        ->map(fn ($k) => [
                            'id' => $k->id,
                            'academic_year_id' => $k->academic_year_id,
                            'academic_semester_id' => $k->academic_semester_id,
                            'classroom_id' => $k->classroom_id,
                            'kkm' => $k->kkm,
                        ])->toArray()
                );
            })
            ->dehydrated(false)
            ->schema([
                Hidden::make('id'),
                Select::make('academic_year_id')
                    ->label('Tahun Ajaran')
                    ->options(AcademicYear::orderBy('name', 'desc')->pluck('name', 'id'))
                    ->disabled()
                    ->dehydrated(true),

                Select::make('academic_semester_id')
                    ->label('Semester')
                    ->options(AcademicSemester::orderBy('semester')->pluck('name', 'id'))
                    ->disabled()
                    ->dehydrated(true),

                Select::make('classroom_id')
                    ->label('Kelas')
                    ->options(Classroom::orderBy('name')->pluck('name', 'id'))
                    ->disabled()
                    ->dehydrated(true),

                TextInput::make('kkm')
                    ->label('Nilai KKM')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->required()
                    ->helperText('Edit nilai KKM di sini.'),
            ])
            ->columns(4)
            ->defaultItems(0)
            ->reorderable(false)
            ->addable(false)
            ->itemLabel(
                fn (array $state): ?string => isset($state['classroom_id']) && $state['classroom_id']
                    ? (Classroom::find($state['classroom_id'])?->name ?? 'Kelas #'.$state['classroom_id'])
                    .' — KKM: '.($state['kkm'] ?? '-')
                    : 'KKM: '.($state['kkm'] ?? '-')
            )
            ->collapsed()
            ->collapsible();
    }
}
