<?php

namespace App\Models;

use App\Casts\PurifiedHtml;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'company_id', 'category_id', 'title', 'number', 'article', 'video', 'description',
    'price', 'currency', 'warranty_value', 'warranty_unit', 'is_moderated', 'is_active', 'is_banned',
])]
class Ad extends Model
{
    public const WARRANTY_UNITS = ['days', 'months', 'years'];

    /**
     * Available ad list filters.
     *
     * @var list<string>
     */
    public const STATUSES = ['all', 'active', 'disabled', 'moderation', 'banned'];

    protected function casts(): array
    {
        return [
            'description' => PurifiedHtml::class.':company',
            'price' => 'decimal:4',
            'warranty_value' => 'integer',
            'is_moderated' => 'boolean',
            'is_active' => 'boolean',
            'is_banned' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('is_banned', false);
    }

    public function scopeDisabled(Builder $query): Builder
    {
        return $query->where('is_active', false)->where('is_banned', false);
    }

    public function scopeModeration(Builder $query): Builder
    {
        return $query->where('is_moderated', false)->where('is_banned', false);
    }

    public function scopeBanned(Builder $query): Builder
    {
        return $query->where('is_banned', true);
    }

    /**
     * @return array{all: int, active: int, disabled: int, moderation: int, banned: int}
     */
    public static function countsForCompany(int $companyId): array
    {
        $row = static::query()
            ->where('company_id', $companyId)
            ->selectRaw('COUNT(*) AS all_count')
            ->selectRaw('SUM(CASE WHEN is_active = 1 AND is_banned = 0 THEN 1 ELSE 0 END) AS active_count')
            ->selectRaw('SUM(CASE WHEN is_active = 0 AND is_banned = 0 THEN 1 ELSE 0 END) AS disabled_count')
            ->selectRaw('SUM(CASE WHEN is_moderated = 0 AND is_banned = 0 THEN 1 ELSE 0 END) AS moderation_count')
            ->selectRaw('SUM(CASE WHEN is_banned = 1 THEN 1 ELSE 0 END) AS banned_count')
            ->first();

        return [
            'all' => (int) ($row?->all_count ?? 0),
            'active' => (int) ($row?->active_count ?? 0),
            'disabled' => (int) ($row?->disabled_count ?? 0),
            'moderation' => (int) ($row?->moderation_count ?? 0),
            'banned' => (int) ($row?->banned_count ?? 0),
        ];
    }

    /**
     * @return array{all: int, active: int, disabled: int, moderation: int, banned: int}
     */
    public static function emptyCounts(): array
    {
        return ['all' => 0, 'active' => 0, 'disabled' => 0, 'moderation' => 0, 'banned' => 0];
    }

    public function images(): HasMany
    {
        return $this->hasMany(AdImage::class)->orderBy('order')->orderBy('id');
    }

    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class, 'ad_region');
    }

    public function filterInts(): HasMany
    {
        return $this->hasMany(AdvFilterInt::class);
    }

    public function filterFloats(): HasMany
    {
        return $this->hasMany(AdvFilterFloat::class);
    }

    public function filterVariants(): HasMany
    {
        return $this->hasMany(AdvFilterVariant::class);
    }

    /**
     * Number of distinct filters that have a value for this ad.
     */
    public function filledFiltersCount(): int
    {
        return $this->filterInts->pluck('filter_id')
            ->merge($this->filterFloats->pluck('filter_id'))
            ->merge($this->filterVariants->pluck('filter_id'))
            ->unique()
            ->count();
    }

    /**
     * Replace all filter values of the ad.
     *
     * @param  array{ints: array<int, int>, floats: array<int, float|int|string>, variants: array<int, array<int, int>>}  $values
     */
    public function syncFilters(array $values): void
    {
        $this->filterInts()->delete();
        $this->filterFloats()->delete();
        $this->filterVariants()->delete();

        foreach ($values['ints'] as $filterId => $value) {
            $this->filterInts()->create(['filter_id' => $filterId, 'value' => $value]);
        }

        foreach ($values['floats'] as $filterId => $value) {
            $this->filterFloats()->create(['filter_id' => $filterId, 'value' => $value]);
        }

        foreach ($values['variants'] as $filterId => $variantIds) {
            foreach ($variantIds as $variantId) {
                $this->filterVariants()->create([
                    'filter_id' => $filterId,
                    'filter_variant_id' => $variantId,
                ]);
            }
        }
    }
}
