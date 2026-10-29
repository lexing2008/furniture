@props([
    'items',
    'region',
    'favoriteIds' => null,
    'activeCategoryId' => null,
    'removable' => false,
    'eagerFirst' => false,
])

@php
    $locale = app()->getLocale();
@endphp

<div data-ads-list class="divide-y divide-gray-200/80">
    @php
        $lcpImageAssigned = false;
    @endphp

    @foreach($items as $item)
        @php
            $favorite = $item instanceof \App\Models\Favorite ? $item : null;
            $ad = $favorite?->ad ?? $item;

            if (! $ad) {
                continue;
            }

            $company = $ad->company;
            $publishedAt = $ad->published_at?->copy()->timezone($company->timezone())->format('d.m.Y H:i');
            $isRecent = $ad->published_at !== null && $ad->published_at->gt(now()->subDays(3));
            $image = $ad->images->first();
            $galleryImages = $ad->images
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
            $hasDetails = $ad->description || $company->legal_name || $company->tax_id || $company->phones->isNotEmpty();
            $isFavorite = $favorite !== null || ($favoriteIds?->contains($ad->id) ?? false);
            $savedRegionName = $favorite?->region
                ? ($locale === 'en' ? $favorite->region->name_en : $favorite->region->name)
                : null;
            $isLcpImage = (bool) $eagerFirst && ! $lcpImageAssigned && $image !== null;

            if ($isLcpImage) {
                $lcpImageAssigned = true;
            }
        @endphp

        <article @if($removable) data-favorite-card @endif class="py-6 first:pt-0 last:pb-0">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:gap-7">
                <div data-ad-image class="relative flex h-[237px] w-[325px] max-w-full shrink-0 items-center justify-center self-start overflow-hidden rounded-xl bg-[#f5f5f2]">

                    @if($image)
                        <button
                            type="button"
                            data-gallery-open
                            data-gallery-images="{{ json_encode($galleryImages, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}"
                            data-gallery-title="{{ $ad->title }}"
                            data-gallery-price="{{ number_format((float) $ad->price, 2, ',', ' ') }} {{ $ad->currency }}"
                            aria-label="{{ __('Открыть галерею: :title', ['title' => $ad->title]) }}"
                            class="group relative flex h-full w-full cursor-pointer items-center justify-center focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-green-first"
                        >
                            <img
                                src="{{ $image->thumbnailUrl(325) }}"
                                srcset="{{ $image->thumbnailUrl(325) }} 1x, {{ $image->thumbnailUrl(630) }} 2x"
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

                            <span class="pointer-events-none absolute bottom-2 left-2 inline-flex items-center gap-1 rounded-full bg-black/55 px-2 py-1 text-[10px] font-semibold text-white backdrop-blur-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3 w-3">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" />
                                </svg>
                                {{ count($galleryImages) }}
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

                    @if($favorite?->region || $activeCategoryId === null || $ad->category_id !== $activeCategoryId)
                        <div class="flex flex-wrap gap-1.5">
                            @if($activeCategoryId === null || $ad->category_id !== $activeCategoryId)
                                <a href="{{ route('region.category', ['region' => $region->subdomain, 'categorySlug' => $ad->category->slugIn($locale)]) }}" class="inline-flex items-center gap-1.5 rounded-full bg-green-first/5 px-2.5 py-1 text-[11px] font-medium text-green-first ring-1 ring-green-first/10">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3 w-3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                                    </svg>
                                    {{ $ad->category->nameIn($locale) }}
                                </a>
                            @endif

                            @if($favorite?->region)
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
                            <div data-ad-description-fade class="pointer-events-none absolute inset-x-0 bottom-0 h-12 bg-gradient-to-t from-white via-white/90 to-transparent"></div>
                        </div>
                    @endif
                </div>

                <div class="-mt-5 shrink-0 lg:mt-0 lg:w-64 lg:border-l lg:border-gray-100 lg:pl-6">
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
    @endforeach
</div>
