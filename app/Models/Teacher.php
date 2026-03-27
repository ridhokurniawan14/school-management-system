<?php

// FILE: app/Models/Teacher.php

namespace App\Models;

use App\Observers\TeacherObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([TeacherObserver::class])]
class Teacher extends Model
{
    protected $guarded = [];

    protected $casts = [
        'birth_date' => 'date',
        'join_date'  => 'date',
        'is_active'  => 'boolean',
        'documents'  => 'array',  // ← penting untuk JSON berkas
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function principalAssignments(): HasMany
    {
        return $this->hasMany(PrincipalAssignment::class);
    }
    // Tambahkan di model Teacher
    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'teacher_subjects')
            ->withTimestamps();
    }
}
