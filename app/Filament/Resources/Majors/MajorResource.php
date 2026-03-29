<?php

namespace App\Filament\Resources\Majors;

use App\Filament\Resources\Majors\Pages\CreateMajor;
use App\Filament\Resources\Majors\Pages\EditMajor;
use App\Filament\Resources\Majors\Pages\ListMajors;
use App\Models\Major;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class MajorResource extends Resource
{
    protected static ?string $model = Major::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static string|\UnitEnum|null $navigationGroup = 'Akademik';

    protected static ?string $navigationLabel = 'Bidang Keahlian';

    protected static ?int $navigationSort = 1;

    protected static ?string $modelLabel = 'Bidang Keahlian';

    protected static ?string $pluralModelLabel = 'Bidang Keahlian';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                // ── Informasi Bidang Keahlian ─────────────────────────────
                Section::make('Informasi Bidang Keahlian')
                    ->description('Bidang keahlian adalah kelompok besar program studi. Contoh: Teknologi Informasi dan Komunikasi.')
                    ->icon('heroicon-o-rectangle-stack')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Bidang Keahlian')
                            ->placeholder('Teknologi Informasi dan Komunikasi')
                            ->helperText('Nama lengkap sesuai struktur kurikulum SMK.')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('code')
                            ->label('Kode')
                            ->placeholder('TIK')
                            ->helperText('Kode singkat bidang keahlian. Contoh: TIK, TEK, BIS.')
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),

                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->helperText('Nonaktifkan jika bidang keahlian ini sudah tidak dibuka.')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('gray'),
                    ]),

                // ── Konsentrasi Keahlian (inline) ──────────────────────────
                Section::make('Konsentrasi Keahlian')
                    ->description('Program studi / jurusan di bawah bidang keahlian ini. Contoh: RPL, TKJ, Multimedia.')
                    ->icon('heroicon-o-tag')
                    ->schema([
                        Repeater::make('competencies')
                            ->label('')
                            ->relationship('competencies')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Nama Konsentrasi Keahlian')
                                    ->placeholder('Rekayasa Perangkat Lunak')
                                    ->helperText('Nama lengkap program studi / jurusan.')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('code')
                                    ->label('Kode')
                                    ->placeholder('RPL')
                                    ->helperText('Kode singkat. Contoh: RPL, TKJ, MM.')
                                    ->required()
                                    ->maxLength(20),

                                Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->helperText('Nonaktifkan jika jurusan ini sudah tidak dibuka.')
                                    ->default(true)
                                    ->onColor('success')
                                    ->offColor('gray'),
                            ])
                            ->columns(3)
                            ->addActionLabel('+ Tambah Konsentrasi Keahlian')
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
                    ->label('Bidang Keahlian')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('code')
                    ->label('Kode')
                    ->badge()
                    ->color('info'),

                TextColumn::make('competencies_count')
                    ->label('Jurusan')
                    ->counts('competencies')
                    ->badge()
                    ->color('success')
                    ->suffix(' jurusan'),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name', 'asc')
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
            'index' => ListMajors::route('/'),
            'create' => CreateMajor::route('/create'),
            'edit' => EditMajor::route('/{record}/edit'),
        ];
    }
}
