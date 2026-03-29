<?php

namespace App\Filament\Resources\Staff\Pages;

use App\Filament\Resources\Staff\StaffResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStaff extends CreateRecord
{
    protected static string $resource = StaffResource::class;

    protected static ?string $title = 'Tambah Staff';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Auto set role = staff saat create
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['role'] = 'staff';

        return $data;
    }
}
