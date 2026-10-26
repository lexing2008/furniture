<?php

namespace App\Models;

use App\Casts\PurifiedHtml;
use Illuminate\Database\Eloquent\Attributes\Fillable;
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
