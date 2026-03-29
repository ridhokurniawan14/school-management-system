<?php

// FILE: app/Observers/TeacherObserver.php

namespace App\Observers;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class TeacherObserver
{
    /**
     * Saat teacher baru dibuat → otomatis buatkan akun User
     */
    public function created(Teacher $teacher): void
    {
        if ($teacher->email && ! $teacher->user_id) {
            $user = User::create([
                'name' => $teacher->full_name,
                'email' => $teacher->email,
                'password' => Hash::make('smkpgri1@'.now()->year),
            ]);

            $teacher->updateQuietly(['user_id' => $user->id]);
        }
    }

    /**
     * Saat teacher diupdate → sync nama & email ke User
     */
    public function updated(Teacher $teacher): void
    {
        if ($teacher->user) {
            $teacher->user->update([
                'name' => $teacher->full_name,
                'email' => $teacher->email,
            ]);
        }

        // Hapus foto lama dari storage jika diganti
        if ($teacher->wasChanged('photo') && $teacher->getOriginal('photo')) {
            Storage::disk('public')->delete($teacher->getOriginal('photo'));
        }

        // Hapus dokumen lama yang dihapus dari JSON
        if ($teacher->wasChanged('documents')) {
            $oldDocs = $teacher->getOriginal('documents') ?? [];
            $newDocs = $teacher->documents ?? [];

            // Ambil file yang sudah tidak ada di documents baru
            $oldFiles = collect($oldDocs)->pluck('file')->filter()->toArray();
            $newFiles = collect($newDocs)->pluck('file')->filter()->toArray();
            $deletedFiles = array_diff($oldFiles, $newFiles);

            foreach ($deletedFiles as $file) {
                Storage::disk('public')->delete($file);
            }
        }
    }

    /**
     * Saat teacher dihapus → hapus semua file + User terkait
     */
    public function deleted(Teacher $teacher): void
    {
        // Hapus foto
        if ($teacher->photo) {
            Storage::disk('public')->delete($teacher->photo);
        }

        // Hapus semua berkas dokumen
        if ($teacher->documents) {
            foreach ($teacher->documents as $doc) {
                if (! empty($doc['file'])) {
                    Storage::disk('public')->delete($doc['file']);
                }
            }
        }

        // Hapus akun User terkait
        $teacher->user?->delete();
    }
}
