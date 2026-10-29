@props([
    'items',
    'region',
    'favoriteIds' => null,
    'activeCategoryId' => null,
    'removable' => false,
])

@php
    $locale = app()->getLocale();
@endphp

<div data-ads-list class="divide-y divide-gray-200/80">
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
            $hasDetails = $ad->description || $company->legal_name || $company->tax_id || $company->phones->isNotEmpty();
            $isFavorite = $favorite !== null || ($favoriteIds?->contains($ad->id) ?? false);
            $savedRegionName = $favorite?->region
                ? ($locale === 'en' ? $favorite->region->name_en : $favorite->region->name)
                : null;
        @endphp

        <article @if($removable) data-favorite-card @endif class="py-6 first:pt-0 last:pb-0">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:gap-7">
                <div class="relative flex aspect-[630/467] w-full shrink-0 items-center justify-center self-start overflow-hidden rounded-xl bg-[#f5f5f2] lg:w-60">

                    @if($image)
                        <img src="{{ $image->thumbnailUrl(630) }}" alt="{{ $ad->title }}" loading="lazy" class="h-full w-full object-contain">
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
                        <div data-ad-description class="ad-description rich-text mt-2 text-sm leading-6 text-gray-600">{!! $ad->description !!}</div>
                    @endif
                </div>

                <div class="shrink-0 border-t border-gray-100 pt-4 lg:w-64 lg:border-l lg:border-t-0 lg:pl-6 lg:pt-0">
                    <p class="text-2xl font-bold tracking-tight text-gray-900">{{ number_format((float) $ad->price, 2, ',', ' ') }} <span class="text-sm font-semibold text-gray-500">{{ $ad->currency }}</span></p>

                    @if($favorite)
                        <p class="mt-1 text-[11px] text-gray-400">{{ __('Добавлено') }} {{ $favorite->created_at?->format('d.m.Y') }}</p>
                    @elseif($publishedAt)
                        <p class="mt-1 text-[11px] {{ $isRecent ? 'font-semibold text-green-third' : 'text-gray-400' }}">{{ __('Актуален на') }} {{ $publishedAt }}</p>
                    @endif

                    <div class="mt-4 border-t border-gray-100 pt-3">
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

                        <div data-ad-company-details class="hidden">
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
                                class="mt-3 inline-flex cursor-pointer items-center gap-1 text-xs font-semibold text-gray-500 transition hover:text-green-first"
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
