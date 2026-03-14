<?php

namespace App\Models;

use App\Observers\TeacherObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([TeacherObserver::class])]
class Teacher extends Model
{
    protected $guarded = [];

    protected $casts = [
        'birth_date' => 'date',
        'join_date'  => 'date',
        'is_active'  => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function principalAssignments(): HasMany
    {
        return $this->hasMany(PrincipalAssignment::class);
    }

    public function getFullNameWithTitleAttribute(): string
    {
        return $this->full_name;
    }
}
