@props([
    'card',
    'region',
    'removable' => false,
])

@php
    $ad = $card->ad;
    $favorite = $card->favorite;
    $company = $ad->company;
    $locale = app()->getLocale();
    $publishedAt = $ad->published_at?->copy()->timezone($company->timezone())->format('d.m.Y H:i');
    $isRecent = $ad->published_at !== null && $ad->published_at->gt(now()->subDays(3));
    $image = $ad->images->first();
    $galleryTotal = (int) $ad->images_count;
    $galleryImages = $ad->images
        ->take(2)
        ->map(fn ($adImage) => [
            'url' => $adImage->url(),
            'alt' => $ad->title,
            'width' => $adImage->width,
            'height' => $adImage->height,
        ])
        ->values()
        ->all();
    $imageWidth = $image?->thumb_325_width ?: 325;
    $imageHeight = $image?->thumb_325_height ?: 237;
    $isFavorite = $card->isFavorite;
    $isLcpImage = $card->isLcpImage;
    $showCategory = $card->showCategory;
    $showRegion = $card->showRegion;
    $showBadges = $card->showBadges;
    $hasDetails = $card->hasDetails;
    $hasSchedule = $card->hasSchedule;
    $hasPayment = $card->hasPayment;
    $hasDelivery = $card->hasDelivery;
    $hasWarranty = $card->hasWarranty;
    $warrantyLabel = $card->warrantyLabel;
    $savedRegionName = $card->savedRegionName;
@endphp

<article @if($removable) data-favorite-card @endif class="py-6 first:pt-0 last:pb-0">
    <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:gap-7">
        <div data-ad-image class="relative flex aspect-[630/467] h-auto w-full shrink-0 items-center justify-center self-start overflow-hidden rounded-xl bg-[#f5f5f2] md:aspect-auto md:h-[237px] lg:w-[240px] xl:w-[325px]">

            @if($image)
                <button
                    type="button"
                    data-gallery-open
                    data-gallery-images="{{ json_encode($galleryImages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}"
                    data-gallery-total="{{ $galleryTotal }}"
                    data-gallery-url="{{ route('region.ad.gallery', ['region' => $region->subdomain, 'ad' => $ad->id]) }}"
                    data-gallery-title="{{ $ad->title }}"
                    data-gallery-price="{{ number_format((float) $ad->price, 2, ',', ' ') }} {{ $ad->currency }}"
                    aria-label="{{ __('Открыть галерею: :title', ['title' => $ad->title]) }}"
                    class="group relative flex h-full w-full cursor-pointer items-center justify-center focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-green-first"
                >
                    <img
                        src="{{ $image->thumbnailUrl(325) }}"
                        srcset="{{ $image->thumbnailUrl(325) }} {{ $image->thumb_325_width ?: 325 }}w, {{ $image->thumbnailUrl(630) }} {{ $image->thumb_630_width ?: 630 }}w"
                        sizes="(min-width: 1280px) 325px, (min-width: 1024px) 240px, calc(100vw - 32px)"
                        alt="{{ $ad->title }}"
                        width="{{ $imageWidth }}"
                        height="{{ $imageHeight }}"
                        loading="{{ $isLcpImage ? 'eager' : 'lazy' }}"
                        @if($isLcpImage) fetchpriority="high" @endif
                        decoding="async"
                        class="max-h-full max-w-full object-contain"
                    >

                    <span class="gallery-zoom pointer-events-none absolute inset-0 items-center justify-center">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-white/30 text-gray-700 shadow-lg backdrop-blur-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-6 w-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.85-5.15a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
                            </svg>
                        </span>
                    </span>

                    <span class="pointer-events-none absolute bottom-2 left-2 inline-flex items-center gap-1 rounded-full bg-black/25 px-2 py-1 text-[10px] font-semibold text-white backdrop-blur-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3 w-3">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" />
                        </svg>
                        {{ $galleryTotal }}
                    </span>
                </button>
            @else
                <div class="flex h-full w-full items-center justify-center bg-[#f5f5f2]">
                    <span class="text-xs font-medium uppercase tracking-[0.12em] text-gray-300">{{ __('Нет фото') }}</span>
                </div>
            @endif
        </div>

        <div class="min-w-0 flex-1">
            <div class="flex items-start justify-between gap-3">
                <h2 class="min-w-0 text-lg font-bold tracking-tight text-gray-900 sm:text-xl">{{ $ad->title }}</h2>
                <button
                    type="button"
                    data-favorite-toggle
                    @if($removable) data-favorite-remove @endif
                    data-favorite-url="{{ route('favorites.toggle', ['region' => $region->subdomain, 'ad' => $ad->id]) }}"
                    data-ad-id="{{ $ad->id }}"
                    data-add-label="{{ __('Добавить в избранное') }}"
                    data-remove-label="{{ __('Убрать из избранного') }}"
                    aria-pressed="{{ $isFavorite ? 'true' : 'false' }}"
                    aria-label="{{ $isFavorite ? __('Убрать из избранного') : __('Добавить в избранное') }}"
                    @class([
                        'mt-0.5 flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center rounded-full transition disabled:cursor-wait disabled:opacity-50',
                        'bg-green-first/5 text-green-first/70' => $isFavorite,
                        'bg-white/70 text-gray-300 hover:text-green-first' => ! $isFavorite,
                    ])
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="{{ $isFavorite ? 'currentColor' : 'none' }}" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" />
                    </svg>
                </button>
            </div>

            @if($showBadges)
                <div class="flex flex-wrap gap-1.5">
                    @if($showCategory)
                        <a href="{{ route('region.category', ['region' => $region->subdomain, 'categorySlug' => $ad->category->slugIn($locale)]) }}" class="inline-flex items-center gap-1.5 rounded-full bg-green-first/5 px-2.5 py-1 text-[11px] font-medium text-green-first ring-1 ring-green-first/10">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3 w-3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                            </svg>
                            {{ $ad->category->nameIn($locale) }}
                        </a>
                    @endif

                    @if($showRegion)
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-medium text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3 w-3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                            </svg>
                            {{ __('Добавлено в :region', ['region' => $savedRegionName]) }}
                        </span>
                    @endif
                </div>
            @endif

            @if($ad->description)
                <div class="relative">
                    <div data-ad-description class="ad-description rich-text mt-2 text-sm leading-6 text-gray-600">{!! $ad->description !!}</div>
                    <div data-ad-description-fade class="pointer-events-none absolute inset-x-0 bottom-0 hidden h-12 bg-gradient-to-t from-white via-white/90 to-transparent"></div>
                </div>
            @endif
        </div>

        <div class="-mt-5 shrink-0 lg:mt-0 lg:w-56 lg:border-l lg:border-gray-100 lg:pl-6 xl:w-64">
            <p class="text-2xl font-bold tracking-tight text-gray-900">{{ number_format((float) $ad->price, 2, ',', ' ') }} <span class="text-sm font-semibold text-gray-500">{{ $ad->currency }}</span></p>

            @if($favorite)
                <p class="mt-1 text-[11px] text-gray-400">{{ __('Добавлено') }} {{ $favorite->created_at?->format('d.m.Y') }}</p>
            @elseif($publishedAt)
                <p class="mt-1 text-[11px] {{ $isRecent ? 'text-green-third' : 'text-gray-400' }}">{{ __('Актуален на') }} {{ $publishedAt }}</p>
            @endif

            <div class="lg:mt-4 lg:border-t lg:border-gray-100 lg:pt-3">
                <p class="truncate text-sm font-semibold text-gray-800">{{ $company->display_name }}</p>

                @if($hasDetails)
                    <button
                        type="button"
                        data-ad-expand
                        aria-expanded="false"
                        class="mt-2 inline-flex cursor-pointer items-center gap-1 text-xs font-semibold text-green-first transition hover:text-green-second"
                    >
                        {{ __('Подробнее') }}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                        </svg>
                    </button>
                @endif

                <div data-ad-company-details aria-hidden="true">
                    @if($company->legal_name)
                        <p class="mt-1 text-[11px] leading-5 text-gray-500">{{ $company->legal_name }}</p>
                    @endif

                    @if($company->tax_id)
                        <p class="mt-2 text-[11px] leading-5 text-gray-500">{{ $company->country?->countryData?->tax_id_label ?: 'Tax ID' }} {{ $company->tax_id }}</p>
                    @endif

                    @if($hasWarranty)
                        <div class="mt-3 flex items-center gap-2">
                            <span class="text-xs font-medium text-gray-500">{{ __('Гарантия') }} {{ $ad->warranty_value }} {{ $warrantyLabel }}</span>
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-green-first/10 text-green-first">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.75c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751A11.959 11.959 0 0 1 12 2.714Z" />
                                </svg>
                            </span>
                        </div>
                    @endif

                    @if($company->phones->isNotEmpty())
                        <p class="mt-3 text-[10px] font-semibold uppercase tracking-[0.14em] text-gray-400">{{ __('Телефоны') }}</p>
                        <div class="mt-1 space-y-1">
                            @foreach($company->phones as $phone)
                                <a href="tel:{{ str_replace([' ', '(', ')', '-'], '', $phone->code.$phone->number) }}" class="block text-sm font-medium text-gray-700 transition hover:text-green-first">
                                    {{ $phone->code }} {{ $phone->number }}
                                </a>
                            @endforeach
                        </div>
                    @endif

                    @if($hasSchedule || $hasPayment || $hasDelivery)
                        <div class="mt-3 flex flex-wrap gap-2">
                            @if($hasSchedule)
                                <button
                                    type="button"
                                    data-company-info-open="schedule"
                                    data-company-info-title="{{ __('График работы') }}"
                                    data-company-info-url="{{ route('region.company.info', ['region' => $region->subdomain, 'company' => $company->id, 'type' => 'schedule']) }}"
                                    class="inline-flex cursor-pointer items-center gap-1.5 rounded-full border border-green-first/15 bg-green-first/5 px-3 py-1.5 text-[11px] font-semibold text-green-first transition hover:border-green-first/30 hover:bg-green-first/10"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3.5 w-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    {{ __('График работы') }}
                                </button>
                            @endif
                            @if($hasPayment)
                                <button
                                    type="button"
                                    data-company-info-open="payment"
                                    data-company-info-title="{{ __('Оплата') }}"
                                    data-company-info-url="{{ route('region.company.info', ['region' => $region->subdomain, 'company' => $company->id, 'type' => 'payment']) }}"
                                    class="inline-flex cursor-pointer items-center gap-1.5 rounded-full border border-green-first/15 bg-green-first/5 px-3 py-1.5 text-[11px] font-semibold text-green-first transition hover:border-green-first/30 hover:bg-green-first/10"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3.5 w-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                                    </svg>
                                    {{ __('Оплата') }}
                                </button>
                            @endif
                            @if($hasDelivery)
                                <button
                                    type="button"
                                    data-company-info-open="delivery"
                                    data-company-info-title="{{ __('Доставка') }}"
                                    data-company-info-url="{{ route('region.company.info', ['region' => $region->subdomain, 'company' => $company->id, 'type' => 'delivery']) }}"
                                    class="inline-flex cursor-pointer items-center gap-1.5 rounded-full border border-green-first/15 bg-green-first/5 px-3 py-1.5 text-[11px] font-semibold text-green-first transition hover:border-green-first/30 hover:bg-green-first/10"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3.5 w-3.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.25h1.5a2.25 2.25 0 0 1 2.25 2.25v6.75m-11.25-6.75h6.75a2.25 2.25 0 0 1 2.25 2.25v4.5" />
                                    </svg>
                                    {{ __('Доставка') }}
                                </button>
                            @endif
                        </div>
                    @endif

                    <button
                        type="button"
                        data-ad-collapse
                        class="mt-3 hidden cursor-pointer items-center gap-1 text-xs font-semibold text-gray-500 transition hover:text-green-first lg:inline-flex"
                    >
                        {{ __('Скрыть') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div data-ad-gradient class="pointer-events-none mt-2 hidden h-10 w-full bg-gradient-to-t from-gray-100/80 via-gray-50/50 to-transparent"></div>
</article>