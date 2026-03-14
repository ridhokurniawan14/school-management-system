<?php

namespace App\Filament\Resources\SchoolProfiles;

use App\Filament\Resources\SchoolProfiles\Pages\ManageSchoolProfile;
use App\Models\SchoolProfile;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;

class SchoolProfileResource extends Resource
{
    protected static ?string $model = SchoolProfile::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-building-office-2';
    protected static string|\UnitEnum|null $navigationGroup = 'Data Sekolah';
    protected static ?string $navigationLabel = 'Profil Sekolah';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'Profil Sekolah';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([

                Tabs::make('school_profile_tabs')
                    ->tabs([

                        // ── Tab 1: Identitas ──────────────────────────────
                        Tab::make('Identitas Sekolah')
                            ->icon('heroicon-o-building-office-2')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        TextInput::make('name')
                                            ->label('Nama Sekolah')
                                            ->placeholder('SMK PGRI 1 Giri Banyuwangi')
                                            ->helperText('Nama lengkap sesuai dokumen resmi / SK.')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpanFull(),

                                        TextInput::make('short_name')
                                            ->label('Nama Singkat')
                                            ->placeholder('SMK PGRI 1 Giri')
                                            ->helperText('Nama pendek untuk header laporan dan dokumen.')
                                            ->maxLength(50),

                                        Select::make('school_type')
                                            ->label('Jenis Sekolah')
                                            ->options([
                                                'SMA' => 'SMA',
                                                'MA'  => 'MA',
                                                'SMK' => 'SMK',
                                            ])
                                            ->default('SMK')
                                            ->required(),

                                        TextInput::make('npsn')
                                            ->label('NPSN')
                                            ->placeholder('20552852')
                                            ->helperText('8 digit — cek di referensi.data.kemdikbud.go.id')
                                            ->maxLength(20),

                                        TextInput::make('nss')
                                            ->label('NSS')
                                            ->placeholder('344052507002')
                                            ->helperText('Nomor Statistik Sekolah dari Dinas Pendidikan.')
                                            ->maxLength(30),

                                        Select::make('accreditation')
                                            ->label('Akreditasi')
                                            ->options([
                                                'A'              => 'A — Unggul',
                                                'B'              => 'B — Baik',
                                                'C'              => 'C — Cukup',
                                                'not_accredited' => 'Belum Terakreditasi',
                                            ])
                                            ->placeholder('Pilih status akreditasi'),

                                        Select::make('school_category')
                                            ->label('Status Sekolah')
                                            ->options([
                                                'negeri' => 'Negeri',
                                                'swasta' => 'Swasta',
                                            ])
                                            ->default('swasta')
                                            ->required(),

                                        TextInput::make('established_year')
                                            ->label('Tahun Berdiri')
                                            ->placeholder('1987')
                                            ->helperText('Tahun sekolah resmi berdiri.')
                                            ->numeric()
                                            ->minValue(1900)
                                            ->maxValue(now()->year),

                                        Select::make('curriculum')
                                            ->label('Kurikulum')
                                            ->options([
                                                'merdeka' => 'Kurikulum Merdeka',
                                                'k13'     => 'Kurikulum 2013 (K13)',
                                            ])
                                            ->default('merdeka')
                                            ->required(),
                                    ]),
                            ]),

                        // ── Tab 2: Kontak & Lokasi ────────────────────────
                        Tab::make('Kontak & Lokasi')
                            ->icon('heroicon-o-map-pin')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        Textarea::make('address')
                                            ->label('Alamat')
                                            ->placeholder('Jl. Raya Giri No. 1')
                                            ->helperText('Nama jalan dan nomor saja. Kecamatan/kota diisi di bawah.')
                                            ->rows(2)
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

                                        TextInput::make('village')
                                            ->label('Kelurahan / Desa')
                                            ->placeholder('Giri'),

                                        TextInput::make('postal_code')
                                            ->label('Kode Pos')
                                            ->placeholder('68425')
                                            ->helperText('5 digit kode pos wilayah sekolah.')
                                            ->maxLength(10),

                                        TextInput::make('phone')
                                            ->label('No. Telepon')
                                            ->placeholder('0333-421499')
                                            ->helperText('Nomor telepon aktif yang bisa dihubungi.')
                                            ->tel()
                                            ->maxLength(20),

                                        TextInput::make('email')
                                            ->label('Email Sekolah')
                                            ->placeholder('info@smkpgri1giri.sch.id')
                                            ->helperText('Email resmi untuk korespondensi dan notifikasi.')
                                            ->email()
                                            ->maxLength(255),

                                        TextInput::make('website')
                                            ->label('Website')
                                            ->placeholder('https://smkpgri1giri.sch.id')
                                            ->helperText('URL lengkap dengan https://')
                                            ->url()
                                            ->maxLength(255),
                                    ]),
                            ]),

                        // ── Tab 3: Branding & Dokumen ─────────────────────
                        Tab::make('Branding & Dokumen')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Section::make()
                                    ->columns(3)
                                    ->schema([
                                        FileUpload::make('logo')
                                            ->label('Logo Sekolah')
                                            ->helperText('PNG/JPG. Ukuran ideal 200×200 px. Tampil di header & laporan.')
                                            ->image()
                                            ->imageEditor()
                                            ->disk('public')
                                            ->directory('school')
                                            ->maxSize(2048),

                                        FileUpload::make('favicon')
                                            ->label('Favicon')
                                            ->helperText('PNG. Ukuran ideal 32×32 px. Tampil di tab browser.')
                                            ->image()
                                            ->disk('public')
                                            ->directory('school')
                                            ->maxSize(512),

                                        FileUpload::make('letterhead')
                                            ->label('Kop Surat')
                                            ->helperText('PNG/JPG. Lebar penuh A4. Dipakai otomatis saat cetak dokumen resmi.')
                                            ->image()
                                            ->imageEditor()
                                            ->disk('public')
                                            ->directory('school')
                                            ->maxSize(2048),
                                    ]),
                            ]),

                        // ── Tab 4: Pengaturan ─────────────────────────────
                        Tab::make('Pengaturan')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Section::make()
                                    ->columns(2)
                                    ->schema([
                                        Select::make('academic_year_start_month')
                                            ->label('Bulan Mulai Tahun Ajaran')
                                            ->options([
                                                1  => 'Januari',
                                                2  => 'Februari',
                                                3  => 'Maret',
                                                4  => 'April',
                                                5  => 'Mei',
                                                6  => 'Juni',
                                                7  => 'Juli',
                                                8  => 'Agustus',
                                                9  => 'September',
                                                10 => 'Oktober',
                                                11 => 'November',
                                                12 => 'Desember',
                                            ])
                                            ->default(7)
                                            ->helperText('Standar sekolah Indonesia dimulai bulan Juli.')
                                            ->required(),

                                        Select::make('timezone')
                                            ->label('Zona Waktu')
                                            ->options([
                                                'Asia/Jakarta'  => 'WIB — Asia/Jakarta',
                                                'Asia/Makassar' => 'WITA — Asia/Makassar',
                                                'Asia/Jayapura' => 'WIT — Asia/Jayapura',
                                            ])
                                            ->default('Asia/Jakarta')
                                            ->helperText('Banyuwangi menggunakan WIB (Asia/Jakarta).')
                                            ->required(),
                                    ]),
                            ]),

                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(), // ingat tab terakhir saat refresh

            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSchoolProfile::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
