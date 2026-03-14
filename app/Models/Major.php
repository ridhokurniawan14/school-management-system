<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Major extends Model
{
    protected $guarded = [];

    public function competencies(): HasMany
    {
        return $this->hasMany(Competency::class);
    }
}
