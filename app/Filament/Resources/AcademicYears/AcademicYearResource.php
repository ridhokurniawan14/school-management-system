<?php

namespace App\Filament\Resources\AcademicYears;

use App\Filament\Resources\AcademicYears\Pages\CreateAcademicYear;
use App\Filament\Resources\AcademicYears\Pages\EditAcademicYear;
use App\Filament\Resources\AcademicYears\Pages\ListAcademicYears;
use App\Models\AcademicYear;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AcademicYearResource extends Resource
{
    protected static ?string $model = AcademicYear::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|\UnitEnum|null $navigationGroup = 'Data Sekolah';

    protected static ?string $navigationLabel = 'Tahun Ajaran';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Tahun Ajaran';

    protected static ?string $pluralModelLabel = 'Tahun Ajaran';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Section::make('Informasi Tahun Ajaran')
                    ->description('Periode dan status tahun ajaran.')
                    ->icon('heroicon-o-calendar-days')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Tahun Ajaran')
                            ->placeholder('2024/2025')
                            ->helperText('Format: YYYY/YYYY. Contoh: 2024/2025')
                            ->required()
                            ->maxLength(20)
                            ->columnSpanFull(),

                        DatePicker::make('start_date')
                            ->label('Tanggal Mulai')
                            ->helperText('Tanggal awal masuk tahun ajaran baru.')
                            ->required()
                            ->native(false)
                            ->displayFormat('d F Y'),

                        DatePicker::make('end_date')
                            ->label('Tanggal Selesai')
                            ->helperText('Tanggal akhir / penutupan tahun ajaran.')
                            ->required()
                            ->native(false)
                            ->displayFormat('d F Y')
                            ->after('start_date'),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'upcoming' => 'Akan Datang',
                                'active' => 'Aktif / Berjalan',
                                'completed' => 'Selesai',
                            ])
                            ->default('upcoming')
                            ->required()
                            ->helperText('Status otomatis berubah saat toggle Aktif dinyalakan.'),

                        Toggle::make('is_active')
                            ->label('Jadikan Tahun Ajaran Aktif')
                            ->helperText('Hanya 1 yang boleh aktif. Yang lain otomatis dinonaktifkan.')
                            ->onColor('success')
                            ->offColor('gray')
                            ->live()
                            ->afterStateUpdated(
                                fn ($state, callable $set) => $state ? $set('status', 'active') : null
                            ),
                    ]),

                Section::make('Semester')
                    ->description('Atur Semester Ganjil dan Genap untuk tahun ajaran ini. Maksimal 2 semester.')
                    ->icon('heroicon-o-calendar')
                    ->schema([
                        Repeater::make('semesters')
                            ->label('')
                            ->relationship('semesters')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Semester')
                                    ->placeholder('Semester Ganjil 2024/2025')
                                    ->required()
                                    ->maxLength(100),

                                Select::make('semester')
                                    ->label('Jenis Semester')
                                    ->options([
                                        '1' => 'Semester 1 — Ganjil',
                                        '2' => 'Semester 2 — Genap',
                                    ])
                                    ->required(),

                                DatePicker::make('start_date')
                                    ->label('Tanggal Mulai')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d F Y'),

                                DatePicker::make('end_date')
                                    ->label('Tanggal Selesai')
                                    ->required()
                                    ->native(false)
                                    ->displayFormat('d F Y')
                                    ->after('start_date'),

                                Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'upcoming' => 'Akan Datang',
                                        'active' => 'Aktif / Berjalan',
                                        'completed' => 'Selesai',
                                    ])
                                    ->default('upcoming')
                                    ->required(),

                                Toggle::make('is_active')
                                    ->label('Semester Aktif')
                                    ->helperText('Semester yang sedang berjalan saat ini.')
                                    ->onColor('success')
                                    ->offColor('gray'),
                            ])
                            ->columns(2)
                            ->maxItems(2)
                            ->addActionLabel('+ Tambah Semester')
                            ->reorderable(false)
                            ->defaultItems(0),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Tahun Ajaran')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('start_date')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('end_date')
                    ->label('Selesai')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('semesters_count')
                    ->label('Semester')
                    ->counts('semesters')
                    ->badge()
                    ->color('info')
                    ->suffix(' semester'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'upcoming' => 'warning',
                        'completed' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Aktif',
                        'upcoming' => 'Akan Datang',
                        'completed' => 'Selesai',
                        default => $state,
                    }),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->defaultSort('start_date', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'upcoming' => 'Akan Datang',
                        'active' => 'Aktif',
                        'completed' => 'Selesai',
                    ]),
            ])
            ->actions([
                EditAction::make(),

                Action::make('clone')
                    ->label('Duplikasi')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Duplikasi Tahun Ajaran')
                    ->modalDescription('Akan dibuat tahun ajaran baru +1 tahun beserta semesternya.')
                    ->modalSubmitActionLabel('Ya, Duplikasi')
                    ->action(function (AcademicYear $record): void {
                        $parts = explode('/', $record->name);
                        $newName = ($parts[0] + 1).'/'.($parts[1] + 1);

                        $newYear = AcademicYear::create([
                            'name' => $newName,
                            'start_date' => $record->start_date->addYear(),
                            'end_date' => $record->end_date->addYear(),
                            'is_active' => false,
                            'status' => 'upcoming',
                        ]);

                        foreach ($record->semesters as $semester) {
                            $newYear->semesters()->create([
                                'name' => str_replace($record->name, $newName, $semester->name),
                                'semester' => $semester->semester,
                                'start_date' => $semester->start_date->addYear(),
                                'end_date' => $semester->end_date->addYear(),
                                'is_active' => false,
                                'status' => 'upcoming',
                            ]);
                        }
                    }),

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
            'index' => ListAcademicYears::route('/'),
            'create' => CreateAcademicYear::route('/create'),
            'edit' => EditAcademicYear::route('/{record}/edit'),
        ];
    }
}
