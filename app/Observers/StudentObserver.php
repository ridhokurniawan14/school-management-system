<?php

// FILE: app/Observers/StudentObserver.php

namespace App\Observers;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentObserver
{
    /**
     * Saat siswa baru dibuat:
     * - Auto-generate email jika belum diisi
     * - Buatkan akun User
     */
    public function creating(Student $student): void
    {
        // Auto-generate email jika kosong
        if (empty($student->email)) {
            $student->email = self::generateEmail($student->full_name);
        }
    }

    public function created(Student $student): void
    {
        // Buatkan akun User untuk login siswa
        if ($student->email && ! $student->user_id ?? true) {
            $user = User::create([
                'name'     => $student->full_name,
                'email'    => $student->email,
                'password' => Hash::make('smkpgri1@' . now()->year),
            ]);

            // Jika tabel students punya kolom user_id, uncomment ini:
            // $student->updateQuietly(['user_id' => $user->id]);
        }
    }

    /**
     * Saat siswa diupdate:
     * - Hapus foto lama jika diganti
     * - Hapus dokumen yang dihapus dari JSON
     */
    public function updated(Student $student): void
    {
        // Hapus foto awal lama jika diganti
        if ($student->wasChanged('photo') && $student->getOriginal('photo')) {
            Storage::disk('public')->delete($student->getOriginal('photo'));
        }

        // Hapus foto wisuda lama jika diganti
        if ($student->wasChanged('graduation_photo') && $student->getOriginal('graduation_photo')) {
            Storage::disk('public')->delete($student->getOriginal('graduation_photo'));
        }

        // Hapus dokumen yang dihapus dari JSON
        if ($student->wasChanged('documents')) {
            $oldDocs  = $student->getOriginal('documents') ?? [];
            $newDocs  = $student->documents ?? [];
            $oldFiles = collect($oldDocs)->pluck('file')->filter()->toArray();
            $newFiles = collect($newDocs)->pluck('file')->filter()->toArray();

            foreach (array_diff($oldFiles, $newFiles) as $file) {
                Storage::disk('public')->delete($file);
            }
        }
    }

    /**
     * Saat siswa dihapus → hapus semua file terkait
     */
    public function deleted(Student $student): void
    {
        // Hapus foto awal
        if ($student->photo) {
            Storage::disk('public')->delete($student->photo);
        }

        // Hapus foto wisuda
        if ($student->graduation_photo) {
            Storage::disk('public')->delete($student->graduation_photo);
        }

        // Hapus semua berkas dokumen
        if ($student->documents) {
            foreach ($student->documents as $doc) {
                if (!empty($doc['file'])) {
                    Storage::disk('public')->delete($doc['file']);
                }
            }
        }

        // Hapus akun User terkait via email
        if ($student->email) {
            User::where('email', $student->email)->delete();
        }
    }

    /**
     * Auto-generate email dari nama siswa
     * "Ridho Kurniawan Wibowo" → ridho@smkpgri1giri.sch.id
     * Jika sudah ada → ridho2@smkpgri1giri.sch.id, dst
     */
    public static function generateEmail(string $fullName): string
    {
        $domain    = 'smkpgri1giri.sch.id';
        $firstName = Str::lower(
            Str::ascii(
                explode(' ', trim($fullName))[0]
            )
        );

        // Bersihkan karakter non-alphanumeric
        $firstName = preg_replace('/[^a-z0-9]/', '', $firstName);
        $email     = $firstName . '@' . $domain;

        // Cek duplikat, tambah angka jika sudah ada
        $counter = 2;
        while (Student::where('email', $email)->exists() || User::where('email', $email)->exists()) {
            $email = $firstName . $counter . '@' . $domain;
            $counter++;
        }

        return $email;
    }
}
