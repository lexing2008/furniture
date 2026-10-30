@props([
    'items',
    'region',
    'favoriteIds' => null,
    'activeCategoryId' => null,
    'removable' => false,
    'eagerFirst' => false,
])

<div data-ads-list class="divide-y divide-gray-200/80">
    @php
        $lcpImageAssigned = false;
    @endphp

    @foreach($items as $item)
        @php
            $card = \App\Support\AdCardData::from(
                $item,
                $region,
                $favoriteIds,
                $activeCategoryId,
                $eagerFirst && ! $lcpImageAssigned,
            );

            if ($card === null) {
                continue;
            }

            if ($card->isLcpImage) {
                $lcpImageAssigned = true;
            }
        @endphp

        <x-ad-card :card="$card" :region="$region" :removable="$removable" />
    @endforeach
</div>
