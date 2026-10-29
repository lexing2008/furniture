<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ad_id', 'region_id', 'user_id', 'visitor_token'])]
class Favorite extends Model
{
    public const UPDATED_AT = null;

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
