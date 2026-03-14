<?php

namespace App\Observers;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeacherObserver
{
    /**
     * Saat teacher baru dibuat → otomatis buatkan akun User
     */
    public function created(Teacher $teacher): void
    {
        if ($teacher->email && ! $teacher->user_id) {
            $user = User::create([
                'name'     => $teacher->full_name,
                'email'    => $teacher->email,
                'password' => Hash::make('smkpgri1@' . now()->year),
                // Default password: smkpgri1@2025
                // Guru wajib ganti password saat login pertama
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
                'name'  => $teacher->full_name,
                'email' => $teacher->email,
            ]);
        }
    }

    /**
     * Saat teacher dihapus → hapus User terkait
     */
    public function deleted(Teacher $teacher): void
    {
        $teacher->user?->delete();
    }
}
