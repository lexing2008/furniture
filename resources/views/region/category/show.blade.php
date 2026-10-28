@extends('layouts.site')

@section('title', $seo['seo_title'] ?? $categoryName)
@section('description', $seo['seo_description'] ?? '')

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
                        <span class="text-white">{{ $categoryName }}</span>
                    @endif
                </nav>

                <h1 class="mt-3 text-2xl font-bold tracking-tight sm:text-3xl">{{ __(':category в :region', ['category' => $categoryName, 'region' => $regionNameIn]) }}</h1>
            </div>
        </section>

        <div class="grid items-start gap-6 lg:grid-cols-[280px_minmax(0,1fr)]">
            <aside class="space-y-4">
                <section class="rounded-2xl border border-gray-200/80 bg-white p-4 shadow-sm">
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

                @if($filters !== [])
                    <section class="rounded-2xl border border-gray-200/80 bg-white p-4 shadow-sm">
                        <h2 class="text-sm font-bold uppercase tracking-wide text-gray-900">{{ __('Фильтры') }}</h2>

                            <form method="GET" action="{{ $currentUrl }}" class="mt-4 space-y-5">
                                @if($sort !== 'published')
                                    <input type="hidden" name="sort" value="{{ $sort }}">
                                @endif
                                @foreach($filters as $filter)
                                    @php
                                        $selected = is_array($filterValues[$filter['id']] ?? null) ? $filterValues[$filter['id']] : [];
                                        $selectedVariants = $filter['type'] === 'radio'
                                            ? [$selected['values'] ?? null]
                                            : (is_array($selected['values'] ?? null) ? $selected['values'] : []);
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
                                                            type="{{ $filter['type'] === 'radio' ? 'radio' : 'checkbox' }}"
                                                            name="{{ $filter['type'] === 'radio' ? 'filters['.$filter['id'].'][values]' : 'filters['.$filter['id'].'][values][]' }}"
                                                            value="{{ $variant['id'] }}"
                                                            @checked($filter['type'] === 'radio'
                                                                ? (string) ($selected['values'] ?? '') === (string) $variant['id']
                                                                : in_array((string) $variant['id'], array_map('strval', $selectedVariants), true))
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

                                <div class="flex flex-wrap gap-2 border-t border-gray-100 pt-4">
                                    <button type="submit" class="cursor-pointer rounded-full bg-green-first px-4 py-2 text-xs font-semibold text-white transition hover:bg-green-second">
                                        {{ __('Применить') }}
                                    </button>
                                    <a href="{{ $currentUrl }}" class="rounded-full border border-gray-200 px-4 py-2 text-xs font-medium text-gray-600 transition hover:bg-gray-50">
                                        {{ __('Сбросить') }}
                                    </a>
                                </div>
                            </form>
                    </section>
                @endif
            </aside>

            <section class="min-w-0">
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2  opacity-40 hover:opacity-100 transition duration-500">
                    <p class="text-sm text-gray-500">{{ __('Найдено: :count', ['count' => $ads->total()]) }}</p>
                    <label class="flex items-center gap-2 text-xs text-gray-500">
                        <span>{{ __('Сортировка') }}:</span>
                        <select data-sort-select class="cursor-pointer rounded-lg border-gray-200 bg-white py-1.5 pl-2.5 pr-8 text-xs text-gray-700 shadow-sm focus:border-green-first focus:ring-green-first">
                            @foreach($sortOptions as $value => $label)
                                <option value="{{ $sortUrls[$value] }}" @selected($sort === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <div class="space-y-4">
                    @forelse($ads as $ad)
                        @php
                            $company = $ad->company;
                            $publishedAt = $ad->published_at?->copy()->timezone($company->timezone())->format('d.m.Y H:i');
                            $isRecent = $ad->published_at !== null && $ad->published_at->gt(now()->subDays(3));
                            $image = $ad->images->first();
                            $hasDetails = $ad->description || $company->legal_name || $company->phones->isNotEmpty();
                        @endphp

                        <article class="overflow-hidden rounded-2xl border border-gray-200/80 bg-white shadow-sm transition duration-200 hover:border-green-first/25 hover:shadow-md">
                            <div class="flex flex-col gap-5 p-4 lg:flex-row lg:p-5">
                                <div class="relative h-56 w-full shrink-0 overflow-hidden rounded-xl bg-gray-100 lg:h-44 lg:w-60">
                                    @if($image)
                                        <img src="{{ $image->thumbnailUrl(630) }}" alt="{{ $ad->title }}" loading="lazy" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center bg-gradient-to-br from-gray-50 to-gray-100 text-gray-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-10 w-10">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1">
                                    <h2 class="text-lg font-bold tracking-tight text-gray-900 sm:text-xl">{{ $ad->title }}</h2>

                                    @if($ad->description)
                                        <div data-ad-description class="ad-description rich-text mt-2 text-sm leading-6 text-gray-600">{!! $ad->description !!}</div>
                                    @endif
                                </div>

                                <div class="shrink-0 border-t border-gray-100 pt-4 lg:w-64 lg:border-l lg:border-t-0 lg:pl-5 lg:pt-0">
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
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-gray-200 bg-white px-6 py-14 text-center shadow-sm">
                            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-green-first/10 text-green-first">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-6 w-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35m1.1-5.4a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0Z" />
                                </svg>
                            </span>
                            <p class="mt-3 text-sm font-semibold text-gray-800">{{ __('Нет товаров/услуг.') }}</p>
                            <p class="mt-1 text-xs text-gray-400">{{ __('Попробуйте изменить фильтры или выбрать другую категорию.') }}</p>
                        </div>
                    @endforelse
                </div>

                @if($ads->hasPages())
                    <div class="mt-6">
                        {{ $ads->links() }}
                    </div>
                @endif
            </section>
        </div>
    </div>
@endsection
