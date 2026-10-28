@extends('layouts.site')

@section('title', $seo['seo_title'] ?: $categoryName)
@section('description', $seo['seo_description'] ?: '')

@section('content')
    @php
        $locale = app()->getLocale();
        $regionNameIn = $locale === 'en' ? $region->name_en : $region->name_in;
        $currentUrl = url()->current();
        $rootUrl = route('region.category', ['region' => $region->subdomain, 'categorySlug' => $root->slugIn($locale)]);
    @endphp

    <div class="space-y-6">
        <section class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-green-first to-green-second px-5 py-7 text-white shadow-lg shadow-green-first/15 sm:px-7">
            <div class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-white/10 blur-3xl"></div>
            <div class="relative">
                <nav class="flex flex-wrap items-center gap-2 text-xs text-white/75" aria-label="{{ __('Хлебные крошки') }}">
                    <a href="{{ $region->url() }}" class="transition hover:text-white">{{ $locale === 'en' ? $region->name_en : $region->name }}</a>
                    <span aria-hidden="true">/</span>
                    <a href="{{ $rootUrl }}" class="transition hover:text-white">{{ $rootName }}</a>
                    @if($root->id !== $category->id)
                        <span aria-hidden="true">/</span>
                        <a href="{{ $categoryUrl }}" class="transition hover:text-white">{{ $categoryName }}</a>
                    @endif
                    @if(count($selectedVariantNames) === 1)
                        <span aria-hidden="true">/</span>
                        <span class="text-white">{{ $selectedVariantNames[0] }}</span>
                    @endif
                </nav>

                <h1 class="mt-3 text-2xl font-bold tracking-tight sm:text-3xl">{{ $pageHeading }}</h1>
            </div>
        </section>

        <div class="grid items-start gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
            <aside class="space-y-4">
                <section class="rounded-2xl border border-gray-200/80 bg-white p-4">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-gray-900">{{ __('Категории') }}</h2>

                    <nav class="mt-3 space-y-1" aria-label="{{ __('Категории') }}">
                        @foreach($sidebarCategories as $item)
                            @php
                                $itemUrl = route('region.category', ['region' => $region->subdomain, 'categorySlug' => $item->slugIn($locale)]);
                            @endphp
                            <a
                                href="{{ $itemUrl }}"
                                @class([
                                    'flex items-center justify-between gap-2 rounded-xl px-3 py-2 text-sm transition',
                                    'bg-green-first/10 font-semibold text-green-first' => $item->id === $category->id,
                                    'text-brand-blue hover:bg-brand-blue/5' => $item->id !== $category->id,
                                ])
                            >
                                <span>{{ $item->nameIn($locale) }}</span>
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3.5 w-3.5 shrink-0 opacity-40">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5 15.75 12l-7.5 7.5" />
                                </svg>
                            </a>
                        @endforeach
                    </nav>
                </section>

                <section class="rounded-2xl border border-gray-200/80 bg-white p-4">
                    <h2 class="text-sm font-bold uppercase tracking-wide text-gray-900">{{ __('Фильтры') }}</h2>

                    <form method="GET" action="{{ $currentUrl }}" data-filter-base-url="{{ $categoryUrl }}" class="mt-4 space-y-5">
                        @if($sort !== 'published')
                            <input type="hidden" name="sort" value="{{ $sort }}">
                        @endif
                        @if($perPage !== 20)
                            <input type="hidden" name="per_page" value="{{ $perPage }}">
                        @endif


                        @foreach($filters as $filter)
                                    @php
                                        $selected = is_array($filterValues[$filter['id']] ?? null) ? $filterValues[$filter['id']] : [];
                                        $selectedVariants = is_array($selected['values'] ?? null) ? $selected['values'] : [];
                                    @endphp

                                    <fieldset>
                                        <legend class="text-xs font-semibold text-gray-700">
                                            {{ $filter['name'] }}
                                            @if($filter['unit'])
                                                <span class="font-normal text-gray-400">({{ $filter['unit'] }})</span>
                                            @endif
                                        </legend>

                                        @if(in_array($filter['type'], ['checkbox', 'radio'], true))
                                            <div class="mt-2 space-y-1.5">
                                                @foreach($filter['variants'] as $variant)
                                                    <label class="flex cursor-pointer items-center gap-2 text-xs text-gray-600">
                                                        <input
                                                            type="checkbox"
                                                            name="filters[{{ $filter['id'] }}][values][]"
                                                            value="{{ $variant['id'] }}"
                                                            @if(!empty($variant['slug']))
                                                                data-filter-variant
                                                                data-filter-slug="{{ $variant['slug'] }}"
                                                            @endif
                                                            @checked(in_array((string) $variant['id'], array_map('strval', $selectedVariants), true))
                                                            class="rounded-sm border-gray-300 text-green-first focus:ring-green-first"
                                                        >
                                                        {{ $variant['name'] }}
                                                    </label>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="mt-2 grid grid-cols-2 gap-2">
                                                <label class="block">
                                                    <span class="mb-1 block text-[10px] text-gray-400">{{ __('От') }}</span>
                                                    <input
                                                        type="number"
                                                        name="filters[{{ $filter['id'] }}][min]"
                                                        value="{{ $selected['min'] ?? '' }}"
                                                        step="{{ $filter['type'] === 'input_int' ? '1' : '0.01' }}"
                                                        class="block w-full rounded-lg border-gray-300 px-2 py-1.5 text-xs focus:border-green-first focus:ring-green-first"
                                                    >
                                                </label>
                                                <label class="block">
                                                    <span class="mb-1 block text-[10px] text-gray-400">{{ __('До') }}</span>
                                                    <input
                                                        type="number"
                                                        name="filters[{{ $filter['id'] }}][max]"
                                                        value="{{ $selected['max'] ?? '' }}"
                                                        step="{{ $filter['type'] === 'input_int' ? '1' : '0.01' }}"
                                                        class="block w-full rounded-lg border-gray-300 px-2 py-1.5 text-xs focus:border-green-first focus:ring-green-first"
                                                    >
                                                </label>
                                            </div>
                                        @endif
                                    </fieldset>
                                @endforeach

                        <fieldset>
                            <legend class="text-xs font-semibold text-gray-700">{{ __('Цена') }}</legend>
                            <div class="mt-2 grid grid-cols-2 gap-2">
                                <label class="block">
                                    <span class="mb-1 block text-[10px] text-gray-400">{{ __('От') }}</span>
                                    <input
                                        type="number"
                                        name="price_from"
                                        value="{{ $priceFrom ?? '' }}"
                                        min="0"
                                        step="0.01"
                                        placeholder="{{ __('От') }}"
                                        class="block w-full rounded-lg border-gray-300 px-2 py-1.5 text-xs text-gray-800 placeholder-gray-300 focus:border-green-first focus:ring-green-first"
                                    >
                                </label>
                                <label class="block">
                                    <span class="mb-1 block text-[10px] text-gray-400">{{ __('До') }}</span>
                                    <input
                                        type="number"
                                        name="price_to"
                                        value="{{ $priceTo ?? '' }}"
                                        min="0"
                                        step="0.01"
                                        placeholder="{{ __('До') }}"
                                        class="block w-full rounded-lg border-gray-300 px-2 py-1.5 text-xs text-gray-800 placeholder-gray-300 focus:border-green-first focus:ring-green-first"
                                    >
                                </label>
                            </div>
                        </fieldset>

                                <div class="flex flex-wrap gap-2 border-t border-gray-100 pt-4">
                                    <button type="submit" class="cursor-pointer rounded-full bg-green-first px-4 py-2 text-xs font-semibold text-white transition hover:bg-green-second">
                                        {{ __('Применить') }}
                                    </button>
                                    <a href="{{ $categoryUrl }}" data-filter-reset="{{ $categoryUrl }}" class="rounded-full border border-gray-200 px-4 py-2 text-xs font-medium text-gray-600 transition hover:bg-gray-50">
                                        {{ __('Сбросить') }}
                                    </a>
                                </div>
                    </form>
                </section>
            </aside>

            <section class="min-w-0">
                @if($ads->total() > 0)
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2 opacity-40 transition duration-500 hover:opacity-100">
                    <p class="text-sm text-gray-500">{{ __('Найдено: :count', ['count' => $ads->total()]) }}</p>
                    <div class="flex flex-wrap items-center gap-2">
                    <label class="flex items-center gap-2 text-xs text-gray-500">
                        <span class="hidden sm:inline">{{ __('Отображать по') }}:</span>
                        <span class="relative inline-flex">
                            <select
                                data-per-page-select
                                class="h-9 cursor-pointer appearance-none rounded-full border border-gray-200/90 bg-white py-0 pl-3 pr-8 text-xs font-semibold text-gray-600 shadow-sm transition hover:border-green-first/40 hover:text-green-first focus:border-green-first focus:outline-none focus:ring-4 focus:ring-green-first/10"
                                aria-label="{{ __('Количество товаров на странице') }}"
                            >
                                @foreach([10, 20, 50, 100] as $option)
                                    <option value="{{ $perPageUrls[$option] }}" @selected($perPage === $option)>{{ $option }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </span>
                        </span>
                    </label>

                    <label class="group flex items-center gap-2" aria-label="{{ __('Сортировка') }}">
                        <span class="hidden text-[10px] font-semibold uppercase tracking-[0.14em] text-gray-400 sm:inline">{{ __('Сортировка') }}</span>
                        <span class="relative inline-flex">
                            <span class="pointer-events-none absolute left-3 top-1/2 z-10 -translate-y-1/2 text-green-first">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3.5 w-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5h11.25M3 12h7.5M3 16.5h4.5M16.5 4.5v15m0 0 3-3m-3 3-3-3" />
                                </svg>
                            </span>
                            <select
                                data-sort-select
                                class="h-9 max-w-[230px] cursor-pointer appearance-none truncate rounded-full border border-gray-200/90 bg-gradient-to-b from-white to-gray-50/80 py-0 pl-9 pr-9 text-xs font-semibold text-gray-600 shadow-sm transition duration-200 hover:border-green-first/40 hover:text-green-first focus:border-green-first focus:outline-none focus:ring-4 focus:ring-green-first/10"
                            >
                                @foreach($sortOptions as $value => $label)
                                    <option value="{{ $sortUrls[$value] }}" @selected($sort === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 transition group-focus-within:text-green-first">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </span>
                        </span>
                    </label>
                    </div>
                </div>
                @endif

                <div data-ads-list class="divide-y divide-gray-200/80">
                    @forelse($ads as $ad)
                        @php
                            $company = $ad->company;
                            $publishedAt = $ad->published_at?->copy()->timezone($company->timezone())->format('d.m.Y H:i');
                            $isRecent = $ad->published_at !== null && $ad->published_at->gt(now()->subDays(3));
                            $image = $ad->images->first();
                            $hasDetails = $ad->description || $company->legal_name || $company->phones->isNotEmpty();
                        @endphp

                        <article class="py-6 first:pt-0 last:pb-0">
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
                                    <h2 class="text-lg font-bold tracking-tight text-gray-900 sm:text-xl">{{ $ad->title }}</h2>

                                    @if($ad->category_id !== $category->id)
                                        <div class="mt-2">
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-green-first/5 px-2.5 py-1 text-[11px] font-medium text-green-first ring-1 ring-green-first/10">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3 w-3">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                                                </svg>
                                                {{ $ad->category->nameIn($locale) }}
                                            </span>
                                        </div>
                                    @endif

                                    @if($ad->description)
                                        <div data-ad-description class="ad-description rich-text mt-2 text-sm leading-6 text-gray-600">{!! $ad->description !!}</div>
                                    @endif
                                </div>

                                <div class="shrink-0 border-t border-gray-100 pt-4 lg:w-64 lg:border-l lg:border-t-0 lg:pl-6 lg:pt-0">
                                    <p class="text-2xl font-bold tracking-tight text-gray-900">{{ number_format((float) $ad->price, 2, ',', ' ') }} <span class="text-sm font-semibold text-gray-500">{{ $ad->currency }}</span></p>

                                    @if($publishedAt)
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
                                                <p class="mt-3 text-[11px] leading-5 text-gray-500">{{ $company->legal_name }}</p>
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
                    @empty
                        <div class="relative overflow-hidden rounded-3xl border border-green-first/15 bg-gradient-to-br from-green-first/[0.04] via-white to-green-second/[0.08] px-6 py-14 text-center shadow-sm">
                            <div class="pointer-events-none absolute -right-10 -top-12 h-44 w-44 rounded-full bg-green-first/10 blur-2xl"></div>
                            <div class="pointer-events-none absolute -bottom-16 -left-12 h-44 w-44 rounded-full bg-green-second/10 blur-2xl"></div>

                            <span class="relative mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-green-first shadow-sm ring-1 ring-green-first/15">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-7 w-7">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                            </span>

                            <h2 class="relative mt-4 text-lg font-bold tracking-tight text-gray-900">{{ __('Нет товаров/услуг.') }}</h2>
                            <p class="relative mx-auto mt-2 max-w-2xl text-sm leading-6 text-gray-500">{{ __('Вы можете стать первым. Разместите мебель/услуги, пока другие спят.') }}</p>

                            <div class="relative mt-6">
                                <a href="{{ route('account.register', ['region' => $region->subdomain]) }}" class="inline-flex items-center gap-2 rounded-full bg-green-first px-5 py-2.5 text-sm font-semibold text-white shadow-md shadow-green-first/20 transition hover:bg-green-second hover:shadow-lg">
                                    {{ __('Регистрация') }}
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>

                @if($ads->hasPages())
                    <div data-pagination class="mt-6">
                        {{ $ads->links('region.category.pagination') }}
                    </div>
                @endif

                @if($ads->hasMorePages())
                    <div data-load-more-wrapper class="mt-4 flex justify-center">
                        <button
                            type="button"
                            data-load-more-url="{{ $ads->nextPageUrl() }}"
                            data-label="{{ __('Показать ещё') }}"
                            data-loading-label="{{ __('Загрузка товаров...') }}"
                            class="group inline-flex cursor-pointer items-center gap-2 rounded-full border border-green-first/25 bg-white px-5 py-2.5 text-sm font-semibold text-green-first shadow-sm transition hover:border-green-first/50 hover:bg-green-first hover:text-white disabled:cursor-wait disabled:opacity-60"
                        >
                            <span data-load-more-label>{{ __('Показать ещё') }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4 transition group-hover:translate-y-0.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25 12 15.75 4.5 8.25" />
                            </svg>
                        </button>
                    </div>
                @endif

                @if(!empty($seo['seo_block']))
                    <article class="seo-block rich-text mb-10 mt-8 border-t border-gray-100 pt-7 text-sm leading-6 text-gray-600">
                        {!! $seo['seo_block'] !!}
                    </article>
                @endif
            </section>
        </div>
    </div>
@endsection
