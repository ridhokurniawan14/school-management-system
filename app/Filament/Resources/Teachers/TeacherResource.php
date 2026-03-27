<?php

namespace App\Filament\Resources\Teachers;

use App\Filament\Imports\TeacherImporter;
use App\Filament\Resources\Concerns\HasDocumentsTab;
use App\Filament\Resources\Teachers\Pages\CreateTeacher;
use App\Filament\Resources\Teachers\Pages\EditTeacher;
use App\Filament\Resources\Teachers\Pages\ListTeachers;
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

class TeacherResource extends Resource
{
    use HasDocumentsTab;

    protected static ?string $model = Teacher::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';
    protected static string|\UnitEnum|null $navigationGroup = 'SDM';
    protected static ?string $navigationLabel = 'Data Guru';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Guru';
    protected static ?string $pluralModelLabel = 'Data Guru';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('teacher_tabs')
                    ->tabs([

                        // ── Tab 1: Data Pribadi ───────────────────────────
                        Tab::make('Data Pribadi')
                            ->icon('heroicon-o-user')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('full_name')
                                            ->label('Nama Lengkap')
                                            ->placeholder('Drs. Ahmad Fauzi, M.Pd')
                                            ->helperText('Nama lengkap beserta gelar.')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpanFull(),

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

                                        TextInput::make('birth_place')
                                            ->label('Tempat Lahir')
                                            ->placeholder('Banyuwangi'),

                                        DatePicker::make('birth_date')
                                            ->label('Tanggal Lahir')
                                            ->native(false)
                                            ->displayFormat('d F Y')
                                            ->maxDate(now()->subYears(18)),

                                        DatePicker::make('join_date')
                                            ->label('Tanggal Mulai Bertugas')
                                            ->native(false)
                                            ->displayFormat('d F Y')
                                            ->helperText('Tanggal pertama kali bertugas di sekolah ini.'),

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

                                        Toggle::make('is_active')
                                            ->label('Aktif Mengajar')
                                            ->default(true)
                                            ->onColor('success')
                                            ->offColor('gray'),
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
                                            ->label('No. HP / WhatsApp')
                                            ->placeholder('08123456789')
                                            ->helperText('Nomor aktif yang bisa dihubungi via WA.')
                                            ->tel()
                                            ->maxLength(20),

                                        TextInput::make('email')
                                            ->label('Email')
                                            ->placeholder('ahmad.fauzi@smkpgri1giri.sch.id')
                                            ->helperText('Email ini digunakan sebagai akun login sistem.')
                                            ->email()
                                            ->required()           // ← wajib
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255),

                                        Textarea::make('address')
                                            ->label('Alamat Lengkap')
                                            ->placeholder('Jl. Raya Giri No. 10, Banyuwangi')
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
                                            ->label('Foto Guru')
                                            ->helperText('Format PNG/JPG. Ukuran ideal 3×4 atau 4×6. Maksimal 2MB.')
                                            ->image()
                                            ->imageEditor()
                                            ->disk('public')
                                            ->directory('teachers')
                                            ->maxSize(2048),
                                    ]),
                            ]),

                        // ── Tab 4: Berkas & Dokumen ───────────────────────
                        self::makeDocumentsTab('documents/teachers'),

                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->poll('3s')
            ->columns([
                ImageColumn::make('photo')
                    ->label('')
                    ->circular()
                    ->disk('public')
                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->full_name) . '&color=0F6E56&background=E1F5EE'),

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

                TextColumn::make('phone')
                    ->label('No. HP')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('employment_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'pns'     => 'success',
                        'p3k'     => 'info',
                        'honorer' => 'info',
                        'gty'     => 'success',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'pns'     => 'PNS',
                        'p3k'     => 'P3K',
                        'honorer' => 'Honorer',
                        'gty'     => 'GTY',
                        default   => $state,
                    }),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
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
                                ->withFilename('data-guru-' . now()->format('Y-m-d')),
                        ]),
                ]),
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(TeacherImporter::class)
                    ->label('Import Data')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info'),

                ExportAction::make()
                    ->exports([
                        ExcelExport::make()
                            ->withColumns(self::getExportColumns())
                            ->withFilename('data-guru-' . now()->format('Y-m-d')),
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
            Column::make('phone')->heading('No. HP'),
            Column::make('email')->heading('Email'),
            Column::make('address')->heading('Alamat'),
            Column::make('join_date')
                ->heading('Tanggal Mulai Bertugas')
                ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->format('d/m/Y') : '-'),
            Column::make('is_active')
                ->heading('Status Aktif')
                ->formatStateUsing(fn($state) => $state ? 'Aktif' : 'Tidak Aktif'),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListTeachers::route('/'),
            'create' => CreateTeacher::route('/create'),
            'edit'   => EditTeacher::route('/{record}/edit'),
        ];
    }
    // Tambahkan method ini di TeacherResource
    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->where('role', 'teacher');
    }
}
