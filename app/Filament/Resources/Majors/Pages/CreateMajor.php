<?php

namespace App\Filament\Resources\Majors\Pages;

use App\Filament\Resources\Majors\MajorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMajor extends CreateRecord
{
    protected static string $resource = MajorResource::class;

    protected static ?string $title = 'Tambah Bidang Keahlian';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
