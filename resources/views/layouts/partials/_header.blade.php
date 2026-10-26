@php
    $regionLabel = app()->getLocale() === 'en' ? $region->name_en : $region->name;
    $countryLabel = app()->getLocale() === 'en' ? $country->name_en : $country->name;
@endphp

<header class="pb-2 pt-3">
    <div class="container mx-auto py-2 px-4">
        <div class="w-full flex  gap-5 text-xs justify-between sm:justify-end mb-2 pr-0.5">
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
            <div class="lg:pl-1">
                <div class="text-xs">{{ __('Мебель в :region', ['region' => app()->getLocale() === 'en' ? $region->name_en : $region->name_in]) }}</div>
                <a href="{{ $country->url() }}" class="logo">{{ config('app.name') }}</a>
            </div>

            <div>
                <div class="text-xs opacity-70">{{ __('Показаны товары в регионе') }}</div>
                <span id="region-button"
                      class="border-b border-dashed border-bord-blue  text-[19px] font-semibold text-brand-blue cursor-pointer hover:border-transparent hover:opacity-60">{{ $regionLabel }}</span>
            </div>

            <div class="max-md:flex max-md:w-full max-md:mt-3  md:flex-1  mx-auto">
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
                        class="flex-1 h-11 pl-3 sm:pl-11 pr-4 bg-gray-55/50 bg-transparent border border-gray-200 border-none rounded-xl sm:rounded-none text-gray-800 placeholder-gray-300 outline-none focus:border-brand-blue  focus:ring-brand-blue/10 focus:ring-0 transition-all duration-200"
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

        </div>

        <!-- Popup -->
        <!-- 1. Popup`s Background -->
        <div id="region-popup-overlay"
             class="fixed hidden inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center justify-items-center p-4 sm:p-6">
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


        <!-- Menu -->
        <div
            class="mt-4 bg-linear-to-b from-green-first to-green-second text-white py-5 px-5 rounded-xl flex flex-wrap gap-x-5">
            <a href="#"
               class="hover:text-black/80 border-b-gray-200/15 border-b hover:border-b-transparent">{{ __('Гостиная') }}</a>
            <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">{{ __('Кухня') }}</a>
            <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">{{ __('Спальня') }}</a>
            <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">{{ __('Детская') }}</a>
            <a href="#"
               class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">{{ __('Гардеробная') }}</a>
            <a href="#"
               class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">{{ __('Прихожая') }}</a>
            <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">{{ __('Ванная и туалет') }}</a>
            <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">{{ __('Офис') }}</a>
            <a href="#"
               class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">{{ __('Текстиль') }}</a>
            <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">{{ __('Услуги') }}</a>
            <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">{{ __('Отдых') }}</a>
        </div>
        <!-- end Menu -->
    </div>
</header>
