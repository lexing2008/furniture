<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'company_id', 'day_of_week', 'open_at', 'close_at',
    'has_break', 'break_start_at', 'break_end_at', 'is_closed',
])]
class CompanySchedule extends Model
{
    protected function casts(): array
    {
        return [
            'has_break' => 'boolean',
            'is_closed' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }
}
