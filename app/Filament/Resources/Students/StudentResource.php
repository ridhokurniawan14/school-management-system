<?php

namespace App\Filament\Resources\Students;

use App\Filament\Imports\StudentImporter;
use App\Filament\Resources\Concerns\HasDocumentsTab;
use App\Filament\Resources\Students\Pages\CreateStudent;
use App\Filament\Resources\Students\Pages\EditStudent;
use App\Filament\Resources\Students\Pages\ListStudents;
use App\Models\Competency;
use App\Models\Student;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ImportAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
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
use Filament\Tables\Table;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;
use pxlrbt\FilamentExcel\Columns\Column;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class StudentResource extends Resource
{
    use HasDocumentsTab;

    protected static ?string $model = Student::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user';
    protected static string|\UnitEnum|null $navigationGroup = 'Kesiswaan';
    protected static ?string $navigationLabel = 'Daftar Siswa';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Siswa';
    protected static ?string $pluralModelLabel = 'Data Siswa';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('student_tabs')
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
                                            ->placeholder('Ahmad Fauzi')
                                            ->helperText('Nama lengkap siswa sesuai akta lahir.')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpanFull(),

                                        TextInput::make('nis')
                                            ->label('NIS')
                                            ->placeholder('2024001')
                                            ->helperText('Nomor Induk Siswa — unik per sekolah.')
                                            ->required()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(20),

                                        TextInput::make('nisn')
                                            ->label('NISN')
                                            ->placeholder('0012345678')
                                            ->helperText('Nomor Induk Siswa Nasional — 10 digit.')
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(10),

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
                                            ->maxDate(now()->subYears(10)),

                                        Select::make('blood_type')
                                            ->label('Golongan Darah')
                                            ->options([
                                                'A'       => 'A',
                                                'B'       => 'B',
                                                'AB'      => 'AB',
                                                'O'       => 'O',
                                                'unknown' => 'Tidak Diketahui',
                                            ])
                                            ->default('unknown'),

                                        Select::make('status')
                                            ->label('Status Siswa')
                                            ->options([
                                                'active'      => 'Aktif',
                                                'graduated'   => 'Lulus',
                                                'transferred' => 'Pindah',
                                                'dropped'     => 'Keluar',
                                            ])
                                            ->default('active')
                                            ->required(),

                                        TextInput::make('entry_year')
                                            ->label('Tahun Masuk')
                                            ->placeholder('2024')
                                            ->helperText('Tahun pertama masuk sekolah ini. Boleh dikosongkan.')
                                            ->numeric()
                                            ->minValue(2000)
                                            ->maxValue(now()->year),

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
                                            ->placeholder('Pilih jurusan')
                                            ->helperText('Kompetensi keahlian / jurusan siswa.'),
                                    ]),
                            ]),

                        // ── Tab 2: Kontak & Alamat ────────────────────────
                        Tab::make('Kontak & Alamat')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('phone')
                                            ->label('No. HP Siswa')
                                            ->placeholder('08123456789')
                                            ->helperText('No. HP siswa jika sudah punya.')
                                            ->tel()
                                            ->maxLength(20),

                                        TextInput::make('email')
                                            ->label('Email Siswa')
                                            ->placeholder('akan otomatis di-generate jika dikosongkan')
                                            ->helperText('Kosongkan untuk auto-generate. Format: nama@smkpgri1giri.sch.id')
                                            ->email()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(255),

                                        Textarea::make('address')
                                            ->label('Alamat Lengkap')
                                            ->placeholder('Jl. Raya Giri No. 5, Banyuwangi')
                                            ->rows(3)
                                            ->columnSpanFull(),

                                        TextInput::make('province')
                                            ->label('Provinsi')
                                            ->placeholder('Jawa Timur'),

                                        TextInput::make('city')
                                            ->label('Kabupaten / Kota')
                                            ->placeholder('Kabupaten Banyuwangi'),

                                        TextInput::make('district')
                                            ->label('Kecamatan')
                                            ->placeholder('Giri'),
                                    ]),
                            ]),

                        // ── Tab 3: Orang Tua / Wali ───────────────────────
                        Tab::make('Orang Tua / Wali')
                            ->icon('heroicon-o-home')
                            ->schema([
                                Section::make()
                                    ->description('Data orang tua atau wali siswa. Minimal 1 data.')
                                    ->schema([
                                        Repeater::make('parents')
                                            ->label('')
                                            ->relationship('parents')
                                            ->schema([
                                                Select::make('relationship')
                                                    ->label('Hubungan')
                                                    ->options([
                                                        'father'   => 'Ayah',
                                                        'mother'   => 'Ibu',
                                                        'guardian' => 'Wali',
                                                    ])
                                                    ->required(),

                                                TextInput::make('full_name')
                                                    ->label('Nama Lengkap')
                                                    ->placeholder('Budi Santoso')
                                                    ->required()
                                                    ->maxLength(255),

                                                TextInput::make('nik')
                                                    ->label('NIK')
                                                    ->placeholder('3510012501850001')
                                                    ->helperText('16 digit NIK KTP.')
                                                    ->maxLength(16),

                                                TextInput::make('occupation')
                                                    ->label('Pekerjaan')
                                                    ->placeholder('Wiraswasta'),

                                                TextInput::make('phone')
                                                    ->label('No. HP')
                                                    ->placeholder('08123456789')
                                                    ->tel()
                                                    ->maxLength(20),

                                                TextInput::make('email')
                                                    ->label('Email')
                                                    ->placeholder('budi@gmail.com')
                                                    ->email()
                                                    ->maxLength(255),

                                                Toggle::make('is_emergency_contact')
                                                    ->label('Kontak Darurat')
                                                    ->helperText('Centang jika ini kontak yang dihubungi saat darurat.')
                                                    ->default(false)
                                                    ->onColor('warning'),

                                                Textarea::make('address')
                                                    ->label('Alamat (jika beda dengan siswa)')
                                                    ->rows(2)
                                                    ->columnSpanFull(),
                                            ])
                                            ->columns(2)
                                            ->addActionLabel('+ Tambah Orang Tua / Wali')
                                            ->maxItems(3)
                                            ->defaultItems(0)
                                            ->reorderable(false),
                                    ]),
                            ]),

                        // ── Tab 4: Foto ───────────────────────────────────
                        Tab::make('Foto')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        FileUpload::make('photo')
                                            ->label('Foto Awal (Saat Masuk)')
                                            ->helperText('Foto saat pertama masuk sekolah. Format PNG/JPG. Maks 2MB.')
                                            ->image()
                                            ->imageEditor()
                                            ->disk('public')
                                            ->directory('students/photos')
                                            ->maxSize(2048),

                                        FileUpload::make('graduation_photo')
                                            ->label('Foto Wisuda / Kelulusan')
                                            ->helperText('Foto saat menjelang lulus/wisuda. Format PNG/JPG. Maks 2MB.')
                                            ->image()
                                            ->imageEditor()
                                            ->disk('public')
                                            ->directory('students/graduation')
                                            ->maxSize(2048),
                                    ]),
                            ]),

                        // ── Tab 5: Informasi Tambahan ─────────────────────
                        Tab::make('Informasi Tambahan')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->schema([
                                Section::make('Identitas Kependudukan')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('detail.nik')
                                            ->label('NIK')
                                            ->placeholder('3510012508080001')
                                            ->helperText('16 digit Nomor Induk Kependudukan siswa.')
                                            ->maxLength(16),

                                        TextInput::make('detail.no_kk')
                                            ->label('No. KK')
                                            ->placeholder('3510012508080001')
                                            ->helperText('16 digit Nomor Kartu Keluarga.')
                                            ->maxLength(16),

                                        TextInput::make('detail.child_order')
                                            ->label('Anak ke')
                                            ->placeholder('1')
                                            ->helperText('Urutan anak dalam keluarga.')
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(20),

                                        TextInput::make('detail.siblings_count')
                                            ->label('Jumlah Saudara Kandung')
                                            ->placeholder('2')
                                            ->helperText('Tidak termasuk siswa sendiri.')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(20),
                                    ]),

                                Section::make('Data Fisik')
                                    ->columns(3)
                                    ->schema([
                                        TextInput::make('detail.weight')
                                            ->label('Berat Badan (kg)')
                                            ->placeholder('55')
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(200),

                                        TextInput::make('detail.height')
                                            ->label('Tinggi Badan (cm)')
                                            ->placeholder('165')
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(250),

                                        TextInput::make('detail.head_circumference')
                                            ->label('Lingkar Kepala (cm)')
                                            ->placeholder('54')
                                            ->numeric()
                                            ->minValue(1)
                                            ->maxValue(100),
                                    ]),

                                Section::make('Sekolah & Transportasi')
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('detail.previous_school')
                                            ->label('Sekolah Asal')
                                            ->placeholder('SMPN 1 Banyuwangi')
                                            ->helperText('Nama sekolah sebelumnya.')
                                            ->maxLength(255),

                                        Select::make('detail.transportation')
                                            ->label('Alat Transportasi')
                                            ->options([
                                                'jalan_kaki'   => 'Jalan Kaki',
                                                'sepeda'       => 'Sepeda',
                                                'motor'        => 'Sepeda Motor',
                                                'mobil'        => 'Mobil Pribadi',
                                                'angkot'       => 'Angkutan Umum',
                                                'antar_jemput' => 'Antar Jemput Sekolah',
                                                'lainnya'      => 'Lainnya',
                                            ])
                                            ->placeholder('Pilih alat transportasi'),

                                        TextInput::make('detail.distance_to_school')
                                            ->label('Jarak Rumah ke Sekolah (km)')
                                            ->placeholder('3.5')
                                            ->helperText('Dalam satuan kilometer.')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(999),
                                    ]),
                            ]),

                        // ── Tab 6: Berkas & Dokumen ───────────────────────
                        self::makeDocumentsTab('documents/students'),

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
                    ->defaultImageUrl(fn($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->full_name) . '&color=185FA5&background=E6F1FB'),

                TextColumn::make('nis')
                    ->label('NIS')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('full_name')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('competency.name')
                    ->label('Jurusan')
                    ->badge()
                    ->color('info')
                    ->placeholder('-'),

                TextColumn::make('entry_year')
                    ->label('Angkatan')
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('No. HP')
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'active'      => 'success',
                        'graduated'   => 'info',
                        'transferred' => 'warning',
                        'dropped'     => 'danger',
                        default       => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'active'      => 'Aktif',
                        'graduated'   => 'Lulus',
                        'transferred' => 'Pindah',
                        'dropped'     => 'Keluar',
                        default       => $state,
                    }),
            ])
            ->defaultSort('nis', 'desc')
            ->filters([
                SelectFilter::make('competency_id')
                    ->label('Jurusan')
                    ->options(
                        \App\Models\Competency::where('is_active', true)
                            ->with('major')
                            ->get()
                            ->filter(fn($c) => $c->major !== null)
                            ->mapWithKeys(fn($c) => [
                                $c->id => $c->major->name . ' — ' . $c->name,
                            ])
                    )
                    ->searchable(),

                SelectFilter::make('status')
                    ->label('Status Siswa')
                    ->options([
                        'active'      => 'Aktif',
                        'graduated'   => 'Lulus',
                        'transferred' => 'Pindah',
                        'dropped'     => 'Keluar',
                    ]),

                SelectFilter::make('entry_year')
                    ->label('Angkatan')
                    ->options(
                        Student::selectRaw('entry_year')
                            ->whereNotNull('entry_year')
                            ->distinct()
                            ->orderBy('entry_year', 'desc')
                            ->pluck('entry_year', 'entry_year')
                    ),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),

                    \Filament\Actions\BulkAction::make('ganti_status')
                        ->label('Ganti Status')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->requiresConfirmation(false)
                        ->modalHeading('Ganti Status Siswa Terpilih')
                        ->modalDescription('Pilih status baru untuk semua siswa yang dicentang.')
                        ->modalSubmitActionLabel('Simpan Perubahan')
                        ->form([
                            Select::make('status')
                                ->label('Status Siswa')
                                ->options([
                                    'active'      => 'Aktif',
                                    'graduated'   => 'Lulus',
                                    'transferred' => 'Pindah',
                                    'dropped'     => 'Keluar',
                                ])
                                ->required()
                                ->helperText('Status baru akan diterapkan ke semua siswa yang dicentang.'),
                        ])
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data): void {
                            $records->each(fn($student) => $student->update([
                                'status' => $data['status'],
                            ]));
                        })
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Status siswa berhasil diperbarui!'),

                    \Filament\Actions\BulkAction::make('ganti_jurusan')
                        ->label('Ganti Jurusan')
                        ->icon('heroicon-o-academic-cap')
                        ->color('warning')
                        ->requiresConfirmation(false)
                        ->modalHeading('Ganti Jurusan Siswa Terpilih')
                        ->modalDescription('Pilih jurusan baru untuk semua siswa yang dicentang.')
                        ->modalSubmitActionLabel('Simpan Perubahan')
                        ->form([
                            Select::make('competency_id')
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
                                ->helperText('Jurusan baru akan diterapkan ke semua siswa yang dicentang.'),
                        ])
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records, array $data): void {
                            $records->each(fn($student) => $student->update([
                                'competency_id' => $data['competency_id'],
                            ]));
                        })
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Jurusan siswa berhasil diperbarui!'),

                    ExportBulkAction::make()
                        ->exports([
                            ExcelExport::make()
                                ->withColumns(self::getExportColumns())
                                ->withFilename('data-siswa-' . now()->format('Y-m-d')),
                        ]),
                ]),
            ])
            ->headerActions([
                ImportAction::make()
                    ->importer(StudentImporter::class)
                    ->label('Import Data')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info'),

                ExportAction::make()
                    ->exports([
                        ExcelExport::make()
                            ->withColumns(self::getExportColumns())
                            ->withFilename('data-siswa-' . now()->format('Y-m-d')),
                    ])
                    ->label('Export Data')
                    ->icon('heroicon-o-arrow-up-tray'),
            ]);
    }

    protected static function getExportColumns(): array
    {
        return [
            Column::make('nis')->heading('NIS'),
            Column::make('nisn')->heading('NISN'),
            Column::make('full_name')->heading('Nama Lengkap'),
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
                ->formatStateUsing(fn($state) => $state
                    ? \Carbon\Carbon::parse($state)->format('d/m/Y') : '-'),
            Column::make('blood_type')->heading('Gol. Darah'),
            Column::make('competency.name')->heading('Jurusan'),
            Column::make('entry_year')->heading('Tahun Masuk'),
            Column::make('status')
                ->heading('Status')
                ->formatStateUsing(fn($state) => match ($state) {
                    'active'      => 'Aktif',
                    'graduated'   => 'Lulus',
                    'transferred' => 'Pindah',
                    'dropped'     => 'Keluar',
                    default       => $state,
                }),
            Column::make('phone')->heading('No. HP'),
            Column::make('email')->heading('Email'),
            Column::make('address')->heading('Alamat'),
            Column::make('province')->heading('Provinsi'),
            Column::make('city')->heading('Kota/Kab'),
            Column::make('district')->heading('Kecamatan'),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => ListStudents::route('/'),
            'create' => CreateStudent::route('/create'),
            'edit'   => EditStudent::route('/{record}/edit'),
        ];
    }
}
