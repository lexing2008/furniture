<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['region_id', 'phone_code', 'currency', 'currency_decimals', 'tax_id_label'])]
class RegionCountry extends Model
{
    protected function casts(): array
    {
        return [
            'currency_decimals' => 'integer',
        ];
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
