<?php

namespace App\Filament\Resources\Staff;

use App\Filament\Imports\StaffImporter;
use App\Filament\Resources\Staff\Pages\CreateStaff;
use App\Filament\Resources\Staff\Pages\EditStaff;
use App\Filament\Resources\Staff\Pages\ListStaff;
use App\Models\Teacher;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ImportAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class StaffResource extends Resource
{
    protected static ?string $model = Teacher::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';
    protected static string|\UnitEnum|null $navigationGroup = 'SDM';
    protected static ?string $navigationLabel = 'Data Staff';
    protected static ?int $navigationSort = 2;
    protected static ?string $modelLabel = 'Staff';
    protected static ?string $pluralModelLabel = 'Data Staff';
    protected static ?string $slug = 'staff';

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('role', 'staff');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('staff_tabs')
                    ->tabs([

                        // ── Tab 1: Data Utama ─────────────────────────────
                        Tab::make('Data Utama')
                            ->icon('heroicon-o-briefcase')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('full_name')
                                            ->label('Nama Lengkap')
                                            ->placeholder('Siti Aminah')
                                            ->helperText('Nama lengkap beserta gelar jika ada.')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpanFull(),

                                        Select::make('role')
                                            ->label('Jabatan')
                                            ->options([
                                                'staff'     => 'Staff / TU',
                                                'admin'     => 'Admin Sistem',
                                                'bendahara' => 'Bendahara / Petugas BPS',
                                                'security'  => 'Keamanan',
                                                'cleaning'  => 'Kebersihan',
                                                'librarian' => 'Pustakawan',
                                            ])
                                            ->default('staff')
                                            ->required()
                                            ->helperText('Petugas BPS/SPP pilih Bendahara / Petugas BPS.'),

                                        Select::make('gender')
                                            ->label('Jenis Kelamin')
                                            ->options([
                                                'male'   => 'Laki-laki',
                                                'female' => 'Perempuan',
                                            ])
                                            ->required(),

                                        Select::make('religion')
                                            ->label('Agama')
                                            ->options([
                                                'islam'    => 'Islam',
                                                'kristen'  => 'Kristen',
                                                'katolik'  => 'Katolik',
                                                'hindu'    => 'Hindu',
                                                'budha'    => 'Buddha',
                                                'konghucu' => 'Konghucu',
                                            ]),

                                        TextInput::make('nip')
                                            ->label('NIP')
                                            ->placeholder('197805152003121001')
                                            ->helperText('Nomor Induk Pegawai — kosongkan jika honorer.')
                                            ->maxLength(30)
                                            ->unique(ignoreRecord: true),

                                        TextInput::make('nuptk')
                                            ->label('NUPTK')
                                            ->placeholder('3847756657300012')
                                            ->helperText('Nomor Unik Pendidik dan Tenaga Kependidikan.')
                                            ->maxLength(20)
                                            ->unique(ignoreRecord: true),

                                        TextInput::make('birth_place')
                                            ->label('Tempat Lahir')
                                            ->placeholder('Banyuwangi'),

                                        DatePicker::make('birth_date')
                                            ->label('Tanggal Lahir')
                                            ->native(false)
                                            ->displayFormat('d F Y')
                                            ->maxDate(now()->subYears(18)),

                                        Select::make('employment_status')
                                            ->label('Status Kepegawaian')
                                            ->options([
                                                'pns'     => 'PNS',
                                                'p3k'     => 'P3K',
                                                'honorer' => 'Honorer',
                                                'gty'     => 'GTY',
                                            ])
                                            ->default('honorer')
                                            ->required(),

                                        DatePicker::make('join_date')
                                            ->label('Tanggal Mulai Bertugas')
                                            ->native(false)
                                            ->displayFormat('d F Y')
                                            ->helperText('Tanggal pertama kali bertugas di sekolah ini.'),

                                        Toggle::make('is_active')
                                            ->label('Aktif')
                                            ->default(true)
                                            ->onColor('success')
                                            ->offColor('gray')
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // ── Tab 2: Kontak ─────────────────────────────────
                        Tab::make('Kontak')
                            ->icon('heroicon-o-phone')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('phone')
                                            ->label('No. HP')
                                            ->placeholder('08123456789')
                                            ->helperText('Nomor aktif yang bisa dihubungi.')
                                            ->tel()
                                            ->maxLength(20),

                                        TextInput::make('email')
                                            ->label('Email')
                                            ->placeholder('staff@smkpgri1giri.sch.id')
                                            ->helperText('Email untuk akun login sistem.')
                                            ->email()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255),

                                        Textarea::make('address')
                                            ->label('Alamat')
                                            ->placeholder('Jl. Raya Giri No. 5, Banyuwangi')
                                            ->rows(3)
                                            ->columnSpanFull(),
                                    ]),
                            ]),

                        // ── Tab 3: Foto ───────────────────────────────────
                        Tab::make('Foto')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        FileUpload::make('photo')
                                            ->label('Foto Staff')
                                            ->helperText('Format PNG/JPG. Maksimal 2MB.')
                                            ->image()
                                            ->imageEditor()
                                            ->disk('public')
                                            ->directory('staff')
                                            ->maxSize(2048),
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
                ImageColumn::make('photo')
                    ->label('')
                    ->circular()
                    ->disk('public')
                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->full_name) . '&color=185FA5&background=E6F1FB'),

                TextColumn::make('full_name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('nip')
                    ->label('NIP')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('role')
                    ->label('Jabatan')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'bendahara' => 'warning',
                        'admin'     => 'danger',
                        'staff'     => 'info',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'staff'     => 'Staff / TU',
                        'admin'     => 'Admin Sistem',
                        'bendahara' => 'Bendahara / BPS',
                        'security'  => 'Keamanan',
                        'cleaning'  => 'Kebersihan',
                        'librarian' => 'Pustakawan',
                        default     => $state,
                    }),

                TextColumn::make('phone')
                    ->label('No. HP')
                    ->placeholder('-'),

                TextColumn::make('email')
                    ->label('Email')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('employment_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pns'     => 'success',
                        'p3k'     => 'info',
                        'honorer' => 'success',
                        'gty'     => 'success',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pns'     => 'PNS',
                        'p3k'     => 'P3K',
                        'honorer' => 'Honorer',
                        'gty'     => 'GTY',
                        default   => $state,
                    })
                    ->toggleable(),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->defaultSort('full_name', 'asc')
            ->filters([
                SelectFilter::make('role')
                    ->label('Jabatan')
                    ->options([
                        'staff'     => 'Staff / TU',
                        'admin'     => 'Admin Sistem',
                        'bendahara' => 'Bendahara / BPS',
                        'security'  => 'Keamanan',
                        'cleaning'  => 'Kebersihan',
                        'librarian' => 'Pustakawan',
                    ]),

                SelectFilter::make('employment_status')
                    ->label('Status Kepegawaian')
                    ->options([
                        'pns'     => 'PNS',
                        'p3k'     => 'P3K',
                        'honorer' => 'Honorer',
                        'gty'     => 'GTY',
                    ]),

                TernaryFilter::make('is_active')
                    ->label('Status Aktif')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif')
                    ->placeholder('Semua'),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ExportBulkAction::make()
                        ->exports([
                            ExcelExport::make()
                                ->withColumns(self::getExportColumns())
                                ->withFilename('data-staff-' . now()->format('Y-m-d')),
                        ]),
                ]),
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(StaffImporter::class)
                    ->label('Import Data')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info'),

                ExportAction::make()
                    ->exports([
                        ExcelExport::make()
                            ->withColumns(self::getExportColumns())
                            ->withFilename('data-staff-' . now()->format('Y-m-d')),
                    ])
                    ->label('Export Data')
                    ->icon('heroicon-o-arrow-up-tray'),
            ]);
    }

    protected static function getExportColumns(): array
    {
        return [
            Column::make('full_name')->heading('Nama Lengkap'),
            Column::make('nip')->heading('NIP'),
            Column::make('nuptk')->heading('NUPTK'),
            Column::make('role')
                ->heading('Jabatan')
                ->formatStateUsing(fn($state) => match ($state) {
                    'staff'     => 'Staff / TU',
                    'admin'     => 'Admin Sistem',
                    'bendahara' => 'Bendahara / BPS',
                    'security'  => 'Keamanan',
                    'cleaning'  => 'Kebersihan',
                    'librarian' => 'Pustakawan',
                    default     => $state,
                }),
            Column::make('gender')
                ->heading('Jenis Kelamin')
                ->formatStateUsing(fn($state) => match ($state) {
                    'male'   => 'Laki-laki',
                    'female' => 'Perempuan',
                    default  => $state,
                }),
            Column::make('religion')
                ->heading('Agama')
                ->formatStateUsing(fn($state) => ucfirst($state ?? '-')),
            Column::make('birth_place')->heading('Tempat Lahir'),
            Column::make('birth_date')
                ->heading('Tanggal Lahir')
                ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : '-'),
            Column::make('employment_status')
                ->heading('Status Kepegawaian')
                ->formatStateUsing(fn($state) => match ($state) {
                    'pns'     => 'PNS',
                    'p3k'     => 'P3K',
                    'honorer' => 'Honorer',
                    'gty'     => 'GTY',
                    default   => $state,
                }),
            Column::make('join_date')
                ->heading('Tanggal Mulai Bertugas')
                ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : '-'),
            Column::make('phone')->heading('No. HP'),
            Column::make('email')->heading('Email'),
            Column::make('address')->heading('Alamat'),
            Column::make('is_active')
                ->heading('Status Aktif')
                ->formatStateUsing(fn($state) => $state ? 'Aktif' : 'Tidak Aktif'),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListStaff::route('/'),
            'create' => CreateStaff::route('/create'),
            'edit'   => EditStaff::route('/{record}/edit'),
        ];
    }
}
