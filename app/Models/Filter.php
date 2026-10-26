<?php

namespace App\Models;

use App\Enums\FilterType;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['category_id', 'type', 'digits_after_point', 'order', 'is_active'])]
class Filter extends Model
{
    use SoftDeletes;

    /** Filters are filled in this language, other languages are translations of it */
    public const BASE_LANG = 'ru';

    protected function casts(): array
    {
        return [
            'type' => FilterType::class,
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(FilterTranslation::class);
    }

    public function baseTranslation(): HasOne
    {
        return $this->hasOne(FilterTranslation::class)->where('lang', self::BASE_LANG);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(FilterVariant::class)->orderBy('order')->orderBy('id');
    }

    public function displayName(): string
    {
        return $this->baseTranslation?->name ?? "#{$this->id}";
    }

    /**
     * Name in the language of the site, falling back to the base language.
     */
    public function nameIn(?string $lang): string
    {
        $translation = $this->translations->firstWhere('lang', $lang)
            ?? $this->translations->firstWhere('lang', self::BASE_LANG)
            ?? $this->baseTranslation;

        return $translation?->name ?? $this->displayName();
    }

    public function unitIn(?string $lang): ?string
    {
        $translation = $this->translations->firstWhere('lang', $lang)
            ?? $this->translations->firstWhere('lang', self::BASE_LANG)
            ?? $this->baseTranslation;

        return $translation?->unit;
    }
}
