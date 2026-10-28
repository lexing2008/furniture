@php
    $locale = app()->getLocale();
    $regionLabel = $locale === 'en' ? $region->name_en : $region->name;
    $regionNameIn = $locale === 'en' ? $region->name_en : $region->name_in;
    $countryLabel = $locale === 'en' ? $country->name_en : $country->name;
    $isCustomer = request()->user()?->isCustomer();
    $isEmployee = request()->user()?->isAdminPanelRole();
@endphp

<header class="max-md:sticky max-md:top-0 max-md:z-30 max-md:bg-white pb-2 pt-2">
    <div class="container mx-auto py-2 px-4">
        <div class="hidden w-full flex gap-5 text-xs justify-between sm:justify-end mb-2 pr-0.5 md:flex">
            <a href="#" class="link">{{ __('Каталог мебели') }}</a>
            @if(!request()->user())
                <a href="{{route('account.register')}}" class="link relative">{{ __('Разместить мебель') }}
                    <span
                        class="bg-red-500 text-white absolute -right-2 -top-3 rounded-2xl px-1 text-[10px]">{{ __('бесплатно') }}</span>
                </a>
            @endif
            <a href="#" class="link">{{ __('О проекте') }}</a>
                @if(request()->user()?->isCustomer())
                    <a href="{{route('account.index')}}" class="link">{{ __('Личный кабинет') }}</a>
                @elseif(request()->user()?->isAdminPanelRole())
                    <a href="{{route('admin.index')}}" class="link">{{ __('Панель админа') }}</a>
                @else
                    <a href="{{route('account.login')}}" class="link">{{ __('Вход') }}</a>
                @endif

        </div>
        <div class="flex flex-wrap gap-x-10 justify-between items-center">
            <div class="min-w-0 max-md:flex-1 lg:pl-1">
                <div class="text-xs text-gray-500">{{ __('Мебель в :region', ['region' => $regionNameIn]) }}</div>
                <a href="{{ route('region.show') }}" class="logo">{{ config('app.name') }}</a>
            </div>

            <div class="max-md:hidden">
                <div class="text-xs opacity-70">{{ __('Показаны товары в регионе') }}</div>
                <span id="region-button"
                      class="border-b border-dashed border-bord-blue  text-[19px] font-semibold text-brand-blue cursor-pointer hover:border-transparent hover:opacity-60">{{ $regionLabel }}</span>
            </div>

            <div class="hidden w-full md:mx-auto md:flex md:flex-1">
                <!-- Search form -->
                <form action="#" method="GET"
                      class="relative w-full flex justify-between items-center flex-row gap-0 bg-white p-1.5 border border-gray-200 rounded-2xl shadow-sm focus-within:border-green-first focus-within:ring-4 focus-within:ring-brand-blue/10 transition-all duration-200">

                    <div
                        class="absolute left-4 top-[18px] sm:top-1/2 sm:-translate-y-1/2 text-gray-400 pointer-events-none hidden sm:block opacity-50">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                             stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.604 10.604Z"/>
                        </svg>
                    </div>

                    <input
                        type="text"
                        name="query"
                        placeholder="{{ __('Поиск по мебели и услугам') }}"
                        class="min-w-0 flex-1 h-11 pl-3 sm:pl-11 pr-4 bg-gray-55/50 bg-transparent border border-gray-200 border-none rounded-xl sm:rounded-none text-gray-800 placeholder-gray-300 outline-none focus:border-brand-blue  focus:ring-brand-blue/10 focus:ring-0 transition-all duration-200"
                        required
                    >

                    <button
                        type="submit"
                        class="w-auto h-11 px-6 border-green-first border-2 hover:bg-green-first/90   text-green-first! hover:text-white! font-medium rounded-xl shadow-md sm:shadow-none hover:shadow-lg sm:hover:shadow-none active:scale-[0.98] sm:active:scale-95 transition-all duration-150 flex items-center justify-center gap-2 cursor-pointer"
                    >
                        <span>{{ __('Найти') }}</span>
                        <!-- Иконка стрелочки для мобильных -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                             stroke="currentColor" class="w-4 h-4 sm:hidden">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                        </svg>
                    </button>

                </form>
                <!-- - - - end Search form -->
            </div>

            <button
                type="button"
                data-header-burger
                class="ml-auto inline-flex h-11 w-11 cursor-pointer items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-700 shadow-sm transition hover:border-green-first/30 hover:text-green-first md:hidden"
                aria-expanded="false"
                aria-controls="header-mobile-menu"
                aria-label="{{ __('Открыть меню') }}"
                data-open-label="{{ __('Открыть меню') }}"
                data-close-label="{{ __('Закрыть меню') }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
            </button>
        </div>


        <!-- Menu -->
        <div
            class="max-md:hidden mt-4 bg-linear-to-b from-green-first to-green-second text-white py-4 px-5 rounded-xl flex flex-wrap">
            @foreach ($menuCategories as $category)
                <a href="{{ $category['url'] }}"
                   class="px-3 py-1 active:bg-white/10 hover:bg-white/10 rounded-full">{{ $category['menu_name'] }}</a>
            @endforeach
        </div>
        <!-- end Menu -->
    </div>
</header>

<!-- Мобильное меню -->
<div id="header-mobile-menu" data-header-mobile-menu class="fixed inset-0 z-[60] hidden overflow-hidden bg-[#f4f7f1] md:hidden">
    <div class="flex h-full flex-col bg-white">
        <div class="sticky top-0 z-10 flex shrink-0 items-center justify-between border-b border-gray-100 bg-white px-4 py-3">
            <a href="{{ $country->url() }}" class="flex min-w-0 items-center gap-2.5">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-green-first to-green-second text-white shadow-md shadow-green-first/20">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5M5.25 9.75V21h4.5v-5.25h4.5V21h4.5V9.75" />
                    </svg>
                </span>
                <span class="min-w-0">
                    <span class="block truncate text-[10px] font-medium uppercase tracking-[0.12em] text-gray-400">{{ __('Мебель в :region', ['region' => $regionNameIn]) }}</span>
                    <span class="block truncate text-lg font-bold tracking-tight text-gray-900">{{ config('app.name') }}</span>
                </span>
            </a>
            <button
                type="button"
                data-header-mobile-close
                class="flex h-10 w-10 shrink-0 cursor-pointer items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 transition hover:border-gray-300 hover:text-gray-800"
                aria-label="{{ __('Закрыть меню') }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain px-4 py-5 pb-10">
            <button
                type="button"
                data-region-popup-open
                class="flex w-full cursor-pointer items-center gap-3 rounded-2xl border border-green-first/15 bg-green-first/5 px-4 py-3 text-left transition hover:border-green-first/35 hover:bg-green-first/10"
            >

                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4 text-green-first">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                    </svg>

                <span class="min-w-0 flex-1">
                    <span class="block text-[10px] font-semibold uppercase tracking-[0.12em] text-gray-400">{{ __('Регион') }}</span>
                    <span class="block truncate text-sm font-semibold text-gray-900">{{ $regionLabel }}</span>
                </span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4 text-gray-400">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                </svg>
            </button>

            <form action="#" method="GET" class="mt-4">
                <div class="relative flex items-center rounded-2xl border border-gray-200 bg-gray-50/70 p-1 shadow-sm focus-within:border-green-first/50 focus-within:bg-white focus-within:ring-4 focus-within:ring-green-first/10">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="pointer-events-none absolute left-3.5 h-5 w-5 text-gray-400">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.604 10.604Z" />
                    </svg>
                    <input type="text" name="query" placeholder="{{ __('Поиск по мебели и услугам') }}" class="h-10 min-w-0 flex-1 border-0 bg-transparent pl-10 pr-3 text-sm text-gray-800 placeholder-gray-400 focus:ring-0" required>
                    <button type="submit" class="inline-flex h-10 shrink-0 cursor-pointer items-center justify-center rounded-xl bg-green-first px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-green-second">{{ __('Найти') }}</button>
                </div>
            </form>

            <div class="mt-6">
                <p class="mb-2 px-1 text-[10px] font-semibold uppercase tracking-[0.16em] text-gray-400">{{ __('Каталог мебели') }}</p>
                <ul class="divide-y divide-gray-100 rounded-2xl border border-gray-100 bg-white px-3">
                    @foreach ($menuCategories as $category)
                        <li data-category-item>
                            <div class="flex items-center gap-1">
                                <a href="{{ $category['url'] }}" class="min-w-0 flex-1 rounded-xl px-2 py-3 text-sm font-medium text-gray-800 transition hover:text-green-first">
                                    {{ $category['menu_name'] }}
                                </a>
                                @if(!empty($category['has_children']))
                                    <button
                                        type="button"
                                        data-category-toggle
                                        data-children-url="{{ route('region.category.children', ['region' => $region->subdomain, 'category' => $category['id']]) }}"
                                        data-loading-label="{{ __('Загрузка...') }}"
                                        data-error-label="{{ __('Не удалось загрузить подкатегории.') }}"
                                        data-empty-label="{{ __('Нет подкатегорий.') }}"
                                        aria-expanded="false"
                                        aria-label="{{ __('Показать подкатегории') }}"
                                        class="flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center rounded-full text-gray-400 transition hover:bg-green-first/10 hover:text-green-first"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4 transition-transform duration-200">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                            <div data-category-children class="hidden px-2 pb-3"></div>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="mt-6 space-y-1 border-t border-gray-100 pt-4">
                @if($isCustomer)
                    <a href="{{ route('account.index') }}" class="block rounded-xl px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">{{ __('Личный кабинет') }}</a>
                @elseif($isEmployee)
                    <a href="{{ route('admin.index') }}" class="block rounded-xl px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">{{ __('Панель админа') }}</a>
                @else
                    <a href="{{ route('account.login') }}" class="block rounded-xl px-3 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-50">{{ __('Вход') }}</a>
                    <a href="{{ route('account.register') }}" class="block rounded-xl px-3 py-2 text-sm font-semibold text-green-first transition hover:bg-green-first/10">{{ __('Разместить мебель') }}</a>
                @endif
                <a href="#" class="block rounded-xl px-3 py-2 text-sm text-gray-600 transition hover:bg-gray-50">{{ __('О проекте') }}</a>
                <a href="#" class="block rounded-xl px-3 py-2 text-sm text-gray-600 transition hover:bg-gray-50">{{ __('Пользовательское соглашение') }}</a>
            </div>
        </div>
    </div>
</div>

<!-- Popup -->
<!-- 1. Popup`s Background -->
<div id="region-popup-overlay"
     class="fixed hidden inset-0 bg-black/40 backdrop-blur-sm z-[70] flex items-center justify-center justify-items-center p-4 sm:p-6">
    <!-- 2. Сам попап (Контентное окно) — УБРАН overflow-y-auto, добавлен относительный класс -->
    <div id="region-popup"
         class="w-full relative max-w-lg bg-white rounded-2xl shadow-xl border border-gray-100 p-6 max-h-[calc(100vh-4rem)] flex flex-col">
        <!-- Cross -->
        <button
            id="region-popup-close"
            class="group absolute right-4 top-4 p-2 text-gray-400 hover:text-gray-600 bg-gray-50 hover:bg-gray-100 rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-brand-blue/20 cursor-pointer z-10"
            aria-label="{{ __('Закрыть модальное окно') }}"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                 stroke="currentColor"
                 class="w-5 h-5 transform group-hover:rotate-90 transition-transform duration-250 ease-out">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>

        <!-- Заголовок окна (Не скроллится) -->
        <div class="mb-5 pr-10 shrink-0">
            <h2 class="text-base mb-3">{{ $countryLabel }}</h2>
            <div class="my-1 text-xs">{{ __('Выберите город. Будет показана мебель в вашем городе или с доставкой в ваш регион.') }}</div>
        </div>

        <!-- 3. ВНУТРЕННИЙ БЛОК КОНТЕНТА: Скроллбар перенесен исключительно сюда -->
        <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar text-gray-600">
            <ul>
                @foreach ($areas as $item)
                    <li @class([
                                    'area',
                                    'is-open' => $item['region']->id === $region->id || $item['cities']->contains('id', $region->id),
                                ])
                    >
                        @if ($item['cities']->isEmpty())
                            <a href="{{ $item['region']->url() }}" class="link">{{ app()->getLocale() === 'en' ? $item['region']->name_en : $item['region']->name }}</a>
                        @else
                            <button type="button" class="area-button" data-area-toggle>
                                <span>{{ app()->getLocale() === 'en' ? $item['region']->name_en : $item['region']->name }}</span>
                                <span class="arrow mr-1.5" aria-hidden="true">▸</span>
                            </button>

                            <!-- Cities -->
                            <div class="cities hidden mt-1 mb-2 ml-4">
                                <a href="{{ $item['region']->url() }}"
                                    @class([
                                         'link text-sm',
                                         'font-bold' => $item['region']->id === $region->id,
                                    ])
                                >{{ app()->getLocale() === 'en' ? $item['region']->name_en : $item['region']->name }}</a>

                                @foreach ($item['cities'] as $city)
                                    <div class="py-1">
                                        <a href="{{ $city->url() }}"
                                            @class([
                                                 'link text-sm',
                                                 'font-bold text-black border-b-transparent!' => $city->id === $region->id,
                                            ])
                                        >{{ app()->getLocale() === 'en' ? $city->name_en : $city->name }}</a>
                                    </div>
                                @endforeach
                            </div>
                            <!-- end Cities -->
                        @endif
                    </li>
                @endforeach
            </ul>
        </div>

    </div>
</div>
<!-- - - - end Popup -->
