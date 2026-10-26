<?php

namespace App\Models;

use App\Support\AdImagePath;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ad_id', 'path', 'uuid', 'order'])]
class AdImage extends Model
{
    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }

    public function ad(): BelongsTo
    {
        return $this->belongsTo(Ad::class);
    }

    public function url(?int $size = null): string
    {
        $path = $size === null ? $this->path : AdImagePath::thumbnail($this->path, $size);

        return url('/storage/'.ltrim($path, '/'));
    }

    public function thumbnailUrl(int $size): string
    {
        return $this->url($size);
    }
}
