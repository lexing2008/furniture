<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['lang', 'name', 'slug', 'seo_title', 'seo_description', 'seo_block'])]
class CategoryTranslation extends Model
{
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'lang', 'code');
    }
}
