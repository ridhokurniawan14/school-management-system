<?php

namespace App\Filament\Resources\SchoolProfiles\Pages;

use App\Filament\Resources\SchoolProfiles\SchoolProfileResource;
use App\Models\SchoolProfile;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class ManageSchoolProfile extends EditRecord
{
    protected static string $resource = SchoolProfileResource::class;

    protected static ?string $title = 'Profil Sekolah';

    // Ambil record pertama, buat otomatis jika belum ada
    public function mount(int|string $record = null): void
    {
        $record = SchoolProfile::firstOrCreate([], [
            'name'                      => 'SMK PGRI 1 Giri Banyuwangi',
            'short_name'                => 'SMK PGRI 1 Giri',
            'school_type'               => 'SMK',
            'school_category'           => 'swasta',
            'curriculum'                => 'merdeka',
            'province'                  => 'Jawa Timur',
            'city'                      => 'Kabupaten Banyuwangi',
            'district'                  => 'Giri',
            'timezone'                  => 'Asia/Jakarta',
            'academic_year_start_month' => 7,
        ]);

        parent::mount($record->id);
    }

    // Tidak ada tombol Delete — profil sekolah tidak boleh dihapus
    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil disimpan')
            ->body('Data profil SMK PGRI 1 Giri Banyuwangi telah diperbarui.');
    }

    // Setelah save tetap di halaman yang sama
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
