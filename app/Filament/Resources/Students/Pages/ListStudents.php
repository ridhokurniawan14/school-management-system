<?php

// FILE: app/Filament/Resources/Students/Pages/ListStudents.php

namespace App\Filament\Resources\Students\Pages;

use App\Filament\Resources\Students\StudentResource;
use Filament\Actions;
use Filament\Actions\ImportAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\View\View;

class ListStudents extends ListRecords
{
    protected static string $resource = StudentResource::class;
    protected static ?string $title = 'Data Siswa';

    // Polling setiap 3 detik untuk auto-refresh table
    public function getPollingInterval(): ?string
    {
        return '3s';
    }

    // Cek apakah ada import yang sedang berjalan
    public function hasActiveImport(): bool
    {
        return \Filament\Actions\Imports\Models\Import::query()
            ->where('importer', \App\Filament\Imports\StudentImporter::class)
            ->whereNull('completed_at')
            ->where('created_at', '>=', now()->subMinutes(10)) // max 10 menit
            ->exists();
    }

    // Cek import yang baru selesai (dalam 30 detik terakhir)
    public function getLatestImportResult(): ?array
    {
        $import = \Filament\Actions\Imports\Models\Import::query()
            ->where('importer', \App\Filament\Imports\StudentImporter::class)
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', now()->subSeconds(30))
            ->latest('completed_at')
            ->first();

        if (!$import) return null;

        return [
            'successful_rows' => $import->successful_rows,
            'total_rows'      => $import->total_rows,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Siswa'),
        ];
    }

    // Tambah banner info di atas table saat import sedang berjalan
    protected function getHeaderWidgets(): array
    {
        return [];
    }

    public function getSubheading(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return null;
    }
}
