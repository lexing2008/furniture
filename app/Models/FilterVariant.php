<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['filter_id', 'order', 'seo_template_id'])]
class FilterVariant extends Model
{
    use SoftDeletes;

    public function filter(): BelongsTo
    {
        return $this->belongsTo(Filter::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(FilterVariantTranslation::class);
    }

    public function seoTemplate(): BelongsTo
    {
        return $this->belongsTo(SeoTemplate::class);
    }
}
