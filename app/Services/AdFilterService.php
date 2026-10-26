<?php

namespace App\Services;

use App\Enums\FilterType;
use App\Models\Ad;
use App\Models\Filter;
use App\Models\FilterVariant;

/**
 * Filter definitions of an ad category together with the values of a concrete ad.
 */
class AdFilterService
{
    /**
     * @return array<int, array{
     *     id: int, name: string, unit: ?string, type: string, digits_after_point: ?int,
     *     variants: array<int, array{id: int, name: string}>, value: mixed
     * }>
     */
    public function forCategory(int $categoryId, ?Ad $ad = null, ?string $lang = null): array
    {
        $lang ??= app()->getLocale();

        $filters = Filter::query()
            ->where('category_id', $categoryId)
            ->where('is_active', true)
            ->with(['translations', 'baseTranslation', 'variants.translations'])
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        $ad?->loadMissing(['filterInts', 'filterFloats', 'filterVariants']);

        return $filters->map(function (Filter $filter) use ($ad, $lang): array {
            $variants = $filter->variants
                ->filter(fn (FilterVariant $variant) => $this->variantIsActive($variant, $lang))
                ->map(fn (FilterVariant $variant) => [
                    'id' => $variant->id,
                    'name' => $variant->nameIn($lang),
                ])
                ->values()
                ->all();

            $value = null;

            if ($filter->type->hasVariants()) {
                $ids = $ad === null
                    ? []
                    : $ad->filterVariants
                        ->where('filter_id', $filter->id)
                        ->pluck('filter_variant_id')
                        ->map(fn ($id) => (int) $id)
                        ->values()
                        ->all();

                $value = $filter->type === FilterType::RADIO ? ($ids[0] ?? null) : $ids;
            } elseif ($ad !== null) {
                $value = match ($filter->type) {
                    FilterType::INPUT_INT => $ad->filterInts->firstWhere('filter_id', $filter->id)?->value,
                    FilterType::INPUT_FLOAT => $ad->filterFloats->firstWhere('filter_id', $filter->id)?->value,
                    default => null,
                };
            }

            return [
                'id' => $filter->id,
                'name' => $filter->nameIn($lang),
                'unit' => $filter->unitIn($lang),
                'type' => $filter->type->value,
                'digits_after_point' => $filter->digits_after_point,
                'variants' => $variants,
                'value' => $value,
            ];
        })->values()->all();
    }

    private function variantIsActive(FilterVariant $variant, string $lang): bool
    {
        $translation = $variant->translations->firstWhere('lang', $lang)
            ?? $variant->translations->firstWhere('lang', Filter::BASE_LANG)
            ?? $variant->translations->first();

        return $translation?->is_active ?? true;
    }
}
