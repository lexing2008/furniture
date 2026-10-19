<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['lang', 'name', 'unit', 'is_active'])]
class FilterTranslation extends Model
{
    use SoftDeletes;

    protected $table = 'filter_trans';

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function filter(): BelongsTo
    {
        return $this->belongsTo(Filter::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'lang', 'code');
    }
}
