<?php

// FILE: app/Models/Student.php

namespace App\Models;

use App\Observers\StudentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[ObservedBy([StudentObserver::class])]
class Student extends Model
{
    protected $guarded = [];

    protected $casts = [
        'birth_date' => 'date',
        'is_active' => 'boolean',
        'documents' => 'array',
    ];

    public function competency(): BelongsTo
    {
        return $this->belongsTo(Competency::class);
    }

    public function parents(): HasMany
    {
        return $this->hasMany(StudentParent::class);
    }

    public function classrooms(): HasMany
    {
        return $this->hasMany(ClassroomStudent::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function internships(): HasMany
    {
        return $this->hasMany(Internship::class);
    }

    public function detail(): HasOne
    {
        return $this->hasOne(StudentDetail::class);
    }
}
