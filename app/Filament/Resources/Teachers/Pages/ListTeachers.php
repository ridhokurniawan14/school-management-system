<?php

namespace App\Filament\Resources\Teachers\Pages;

use App\Filament\Resources\Teachers\TeacherResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTeachers extends ListRecords
{
    protected static string $resource = TeacherResource::class;
    protected static ?string $title = 'Data Guru';

    // Filament v4 — pakai method bukan property
    public function getPollingInterval(): ?string
    {
        return '5s';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Guru'),
        ];
    }
}
