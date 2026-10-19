<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['lang', 'name_in_filter', 'name_full', 'name_buy', 'slug', 'seo_template_id', 'is_active'])]
class FilterVariantTranslation extends Model
{
    use SoftDeletes;

    protected $table = 'filter_variant_trans';

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(FilterVariant::class, 'filter_variant_id');
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'lang', 'code');
    }

    public function seoTemplate(): BelongsTo
    {
        return $this->belongsTo(SeoTemplate::class);
    }

    /**
     * Slug is unique within a category and a language, deleted filters and variants don't count.
     */
    public static function slugTaken(int $categoryId, string $lang, string $slug, ?int $exceptVariantId = null): bool
    {
        return self::query()
            ->where('lang', $lang)
            ->where('slug', $slug)
            ->when($exceptVariantId, fn ($query) => $query->where('filter_variant_id', '!=', $exceptVariantId))
            ->whereHas('variant.filter', fn ($query) => $query->where('category_id', $categoryId))
            ->exists();
    }
}
