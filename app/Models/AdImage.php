<?php

namespace App\Models;

use App\Support\AdImagePath;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'ad_id',
    'path',
    'uuid',
    'order',
    'width',
    'height',
    'thumb_100_width',
    'thumb_100_height',
    'thumb_325_width',
    'thumb_325_height',
    'thumb_630_width',
    'thumb_630_height',
])]
class AdImage extends Model
{
    protected static function booted(): void
    {
        static::created(function (AdImage $image) {
            Ad::query()->whereKey($image->ad_id)->increment('images_count');
        });

        static::deleted(function (AdImage $image) {
            Ad::query()
                ->whereKey($image->ad_id)
                ->where('images_count', '>', 0)
                ->decrement('images_count');
        });
    }

    protected function casts(): array
    {
        return [
            'order' => 'integer',
            'width' => 'integer',
            'height' => 'integer',
            'thumb_100_width' => 'integer',
            'thumb_100_height' => 'integer',
            'thumb_325_width' => 'integer',
            'thumb_325_height' => 'integer',
            'thumb_630_width' => 'integer',
            'thumb_630_height' => 'integer',
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
