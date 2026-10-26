<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ad_id', 'filter_id', 'filter_variant_id'])]
class AdvFilterVariant extends Model
{
    protected $table = 'adv_filter_variants';

    public $timestamps = false;

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    public function filter(): BelongsTo
    {
        return $this->belongsTo(Filter::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(FilterVariant::class, 'filter_variant_id');
    }
}
