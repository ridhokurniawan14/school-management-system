<?php

namespace App\Observers;

use App\Models\AcademicYear;

class AcademicYearObserver
{
    /**
     * Handle the AcademicYear "created" event.
     */
    public function created(AcademicYear $academicYear): void
    {
        //
    }

    /**
     * Handle the AcademicYear "updated" event.
     */
    public function updated(AcademicYear $academicYear): void
    {
        //
    }

    /**
     * Handle the AcademicYear "deleted" event.
     */
    public function deleted(AcademicYear $academicYear): void
    {
        //
    }

    /**
     * Handle the AcademicYear "restored" event.
     */
    public function restored(AcademicYear $academicYear): void
    {
        //
    }

    /**
     * Handle the AcademicYear "force deleted" event.
     */
    public function forceDeleted(AcademicYear $academicYear): void
    {
        //
    }

    public function saving(AcademicYear $academicYear): void
    {
        if ($academicYear->is_active) {
            AcademicYear::where('id', '!=', $academicYear->id)
                ->where('is_active', true)
                ->update([
                    'is_active' => false,
                    'status' => 'completed',
                ]);
        }
    }
}
