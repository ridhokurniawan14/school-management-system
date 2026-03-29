<?php

namespace App\Filament\Resources\Subjects\Pages;

use App\Filament\Resources\Subjects\SubjectResource;
use App\Models\SubjectKkm;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSubject extends EditRecord
{
    protected static string $resource = SubjectResource::class;

    protected static ?string $title = 'Edit Mata Pelajaran';

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Berhasil disimpan')
            ->body('Data mata pelajaran telah diperbarui.');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['kkms']);
        unset($data['kkms_list_10']);
        unset($data['kkms_list_11']);
        unset($data['kkms_list_12']);

        unset($data['kkm_academic_year_id']);
        unset($data['kkm_semester_id']);
        unset($data['kkm_classroom_ids']);
        unset($data['kkm_value']);
        unset($data['kkm_source_year_id']);
        unset($data['kkm_target_year_id']);

        return $data;
    }

    protected function afterSave(): void
    {
        $data = $this->form->getRawState();

        // Gabungkan 3 list repeater tadi menjadi 1 array panjang
        $allKkmsList = array_merge(
            $data['kkms_list_10'] ?? [],
            $data['kkms_list_11'] ?? [],
            $data['kkms_list_12'] ?? []
        );

        // 🔥 1. Kumpulkan semua ID KKM yang MASIH ADA di layar (tidak dihapus user)
        $submittedKkmIds = [];
        foreach ($allKkmsList as $item) {
            if (! empty($item['id'])) {
                $submittedKkmIds[] = $item['id'];
            }
        }

        // 🔥 2. HAPUS KKM dari database yang ID-nya sudah hilang dari layar
        SubjectKkm::where('subject_id', $this->record->id)
            ->whereNotIn('id', $submittedKkmIds)
            ->delete();

        // 3. Simpan atau Update KKM yang masih tersisa di layar
        if (! empty($allKkmsList)) {
            foreach ($allKkmsList as $item) {
                if (empty($item['classroom_id']) || empty($item['academic_semester_id'])) {
                    continue;
                }

                SubjectKkm::updateOrCreate(
                    [
                        'subject_id' => $this->record->id,
                        'classroom_id' => $item['classroom_id'],
                        'academic_semester_id' => $item['academic_semester_id'],
                    ],
                    [
                        'academic_year_id' => $item['academic_year_id'],
                        'kkm' => $item['kkm'],
                    ]
                );
            }
        }
    }
}
