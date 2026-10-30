<?php

namespace App\Support;

use App\Models\Ad;
use App\Models\Favorite;
use App\Models\Region;
use Illuminate\Support\Collection;

/**
 * Normalized data for one catalog card, regardless of whether it comes from
 * a category list or from the favorites list.
 */
final class AdCardData
{
    public function __construct(
        public readonly Ad $ad,
        public readonly ?Favorite $favorite,
        public readonly Region $region,
        public readonly bool $isFavorite,
        public readonly bool $isLcpImage,
        public readonly bool $showCategory,
        public readonly bool $showRegion,
        public readonly bool $showBadges,
        public readonly bool $hasDetails,
        public readonly bool $hasSchedule,
        public readonly bool $hasPayment,
        public readonly bool $hasDelivery,
        public readonly bool $hasWarranty,
        public readonly string $warrantyLabel,
        public readonly ?string $savedRegionName,
    ) {}

    /**
     * @param  Collection<int, int>|null  $favoriteIds
     */
    public static function from(
        mixed $item,
        Region $region,
        ?Collection $favoriteIds,
        ?int $activeCategoryId,
        bool $eagerLcp,
    ): ?self {
        $favorite = $item instanceof Favorite ? $item : null;
        $ad = $favorite?->ad ?? $item;

        if (! $ad instanceof Ad) {
            return null;
        }

        $company = $ad->company;
        $hasSchedule = $company->schedules->isNotEmpty();
        $hasPayment = filled($company->payment);
        $hasDelivery = filled($company->delivery);
        $hasWarranty = filled($ad->warranty_value);
        $warrantyLabel = $hasWarranty
            ? match ($ad->warranty_unit) {
                'days' => trans_choice('день|дня|дней', (int) $ad->warranty_value),
                'years' => trans_choice('год|года|лет', (int) $ad->warranty_value),
                default => trans_choice('месяц|месяца|месяцев', (int) $ad->warranty_value),
            }
            : '';
        $locale = app()->getLocale();
        $savedRegionName = $favorite?->region
            ? ($locale === 'en' ? $favorite->region->name_en : $favorite->region->name)
            : null;
        $showCategory = $activeCategoryId === null || $ad->category_id !== $activeCategoryId;

        return new self(
            ad: $ad,
            favorite: $favorite,
            region: $region,
            isFavorite: $favorite !== null || ($favoriteIds?->contains($ad->id) ?? false),
            isLcpImage: $eagerLcp && $ad->images->isNotEmpty(),
            showCategory: $showCategory,
            showRegion: $savedRegionName !== null,
            showBadges: $showCategory || $savedRegionName !== null,
            hasDetails: (bool) $ad->description
                || $company->legal_name
                || $company->tax_id
                || $company->phones->isNotEmpty()
                || $hasSchedule
                || $hasPayment
                || $hasDelivery
                || $hasWarranty,
            hasSchedule: $hasSchedule,
            hasPayment: $hasPayment,
            hasDelivery: $hasDelivery,
            hasWarranty: $hasWarranty,
            warrantyLabel: $warrantyLabel,
            savedRegionName: $savedRegionName,
        );
    }
}
