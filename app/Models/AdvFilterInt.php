<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ad_id', 'filter_id', 'value'])]
class AdvFilterInt extends Model
{
    protected $table = 'adv_filter_ints';

    public $timestamps = false;

    protected $casts = ['value' => 'integer'];

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    public function filter(): BelongsTo
    {
        return $this->belongsTo(Filter::class);
    }
}
