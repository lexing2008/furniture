<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'type', 'path', 'uuid'])]
class TmpFile extends Model
{
    public const TYPE_AD_IMAGE = 'ad_image';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
