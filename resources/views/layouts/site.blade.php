<!DOCTYPE html>
<html lang="{{ $language }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title')</title>
    @hasSection('description')
        <meta name="description" content="@yield('description')">
    @endif

    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, -apple-system, "Segoe UI", sans-serif; color: #1f2937; /*background: #f9fafb;*/ }
        .popup-backdrop { position: fixed; inset: 0; z-index: 10; display: none; background: rgba(17, 24, 39, .5); }
        .popup-backdrop.is-open { display: block; }
        /*.popup { position: absolute; top: 64px; left: 24px; z-index: 11; display: none; width: 360px; max-width: calc(100vw - 48px); max-height: 70vh; overflow-y: auto; padding: 16px; border-radius: 8px; background: #fff; box-shadow: 0 10px 30px rgba(0, 0, 0, .2); }*/
        .popup { display: none; }
        .popup.is-open { display: block; }
        #region-popup-overlay{ display: none; }
        #region-popup-overlay.is-open { display: block; }
        .popup h2 { margin: 0 0 12px; font-size: 16px; }
        .popup ul { margin: 0; padding: 0; list-style: none; }
        .area-button { display: flex; align-items: center; justify-content: space-between; width: 100%; padding: 8px 4px; border: 0; border-bottom: 1px solid #f3f4f6; background: none; font: inherit; text-align: left; cursor: pointer; }
        .area-button:hover { background: #f9fafb; }
        .area-button .arrow { transition: transform .15s; color: #9ca3af; }
        .area.is-open > .area-button .arrow { transform: rotate(90deg); }
        .cities { display: none; padding: 4px 0 8px 16px; }
        .area.is-open > .cities { display: block; }
        .area-link { display: block; padding: 6px 4px; color: #4338ca; text-decoration: none; }
        .content { max-width: 960px; margin: 0 auto; padding: 32px 24px; }
        .seo-block { margin-top: 24px; line-height: 1.6; }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

</head>
<body class="flex flex-col min-h-screen">
    <header class="py-3">
        <div class="container mx-auto py-3 px-4">
            <div class="w-full flex  gap-5 text-xs justify-between sm:justify-end mb-2 pr-0.5">
                <a href="#" class="link">Каталог мебели</a>
                <a href="#" class="link relative">Разместить мебель
                    <span class="bg-red-500 text-white absolute right-0 -top-3.5 rounded-2xl px-1">бесплатно</span>
                </a>
                <a href="#" class="link">О проекте</a>
                <a href="{{route('login')}}" class="link">Вход</a>
            </div>
            <div class="flex flex-wrap gap-x-10 justify-between items-center">
                <div>
                    <div class="text-xs">Мебель в {{ $region->name_in }}</div>
                    <a href="{{ $country->url() }}" class="logo">{{ config('app.name') }}</a>
                </div>

                <div>
                    <div class="text-xs opacity-70">Показаны товары в регионе</div>
                    <span id="region-button" class="border-b border-dashed border-bord-blue  text-[19px] font-semibold text-brand-blue cursor-pointer hover:border-transparent hover:opacity-60">{{ $region->name }}</span>
                </div>

                <div class="max-md:flex max-md:w-full max-md:mt-3  md:flex-1  mx-auto">
                    <!-- Search form -->
                    <form action="#" method="GET" class="relative w-full flex justify-between items-center flex-row gap-0 bg-white p-1.5 border border-gray-200 rounded-2xl shadow-sm focus-within:border-brand-blue focus-within:ring-4 focus-within:ring-brand-blue/10 transition-all duration-200">

                        <div class="absolute left-4 top-[18px] sm:top-1/2 sm:-translate-y-1/2 text-gray-400 pointer-events-none hidden sm:block">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.604 10.604Z" />
                            </svg>
                        </div>

                        <input
                            type="text"
                            name="query"
                            placeholder="Поиск по мебели и услугам"
                            class="flex-1 h-11 pl-3 sm:pl-11 pr-4 bg-gray-55/50 bg-transparent border border-gray-200 border-none rounded-xl sm:rounded-none text-gray-800 placeholder-gray-400 outline-none focus:border-brand-blue  focus:ring-brand-blue/10 focus:ring-0 transition-all duration-200"
                            required
                        >

                        <button
                            type="submit"
                            class="w-auto h-11 px-6 border-green-first border-2 hover:bg-green-first/90   text-green-first! hover:text-white! font-medium rounded-xl shadow-md sm:shadow-none hover:shadow-lg sm:hover:shadow-none active:scale-[0.98] sm:active:scale-95 transition-all duration-150 flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <span>Найти</span>
                            <!-- Иконка стрелочки для мобильных -->
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4 sm:hidden">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </button>

                    </form>
                    <!-- - - - end Search form -->
                </div>

            </div>

            <!-- 1. Задний фон (Overlay) — Фиксирован, центрирует окно -->
            <div id="region-popup-overlay" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center justify-items-center p-4 sm:p-6">

                <!-- 2. Сам попап (Контентное окно) — УБРАН overflow-y-auto, добавлен относительный класс -->
                <div id="region-popup" class="w-full relative max-w-lg bg-white rounded-2xl shadow-xl border border-gray-100 p-6 max-h-[calc(100vh-4rem)] flex flex-col">
                    <!-- Cross -->
                    <button
                        id="region-popup-close"
                        class="group absolute right-4 top-4 p-2 text-gray-400 hover:text-gray-600 bg-gray-50 hover:bg-gray-100 rounded-xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-brand-blue/20 cursor-pointer z-10"
                        aria-label="Закрыть модальное окно"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5 transform group-hover:rotate-90 transition-transform duration-250 ease-out">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>

                    <!-- Заголовок окна (Не скроллится) -->
                    <div class="mb-5 pr-10 shrink-0">
                        <h2>{{ $country->name }}</h2>
                        <div class="my-1 text-xs">Выберите город. Будет показана мебель в вашем городе или с доставкой в ваш регион.</div>
                    </div>

                    <!-- 3. ВНУТРЕННИЙ БЛОК КОНТЕНТА: Скроллбар перенесен исключительно сюда -->
                    <div class="flex-1 overflow-y-auto pr-2 custom-scrollbar text-gray-600">
                        <ul>
                            @foreach ($areas as $item)
                                <li class="area{{ $item['region']->id === $region->id || $item['cities']->contains('id', $region->id) ? ' is-open' : '' }}">
                                    @if ($item['cities']->isEmpty())
                                        <a href="{{ $item['region']->url() }}" class="area-link">{{ $item['region']->name }}</a>
                                    @else
                                        <button type="button" class="area-button" data-area-toggle>
                                            <span>{{ $item['region']->name }}</span>
                                            <span class="arrow" aria-hidden="true">▸</span>
                                        </button>

                                        <div class="cities">
                                            <a href="{{ $item['region']->url() }}"
                                                @class([
                                                     'link text-sm',
                                                     'font-bold' => $item['region']->id === $region->id,
                                                ])
                                            >{{ $item['region']->name }}</a>

                                            @foreach ($item['cities'] as $city)
                                                <div class="py-1">
                                                    <a href="{{ $city->url() }}"
                                                        @class([
                                                             'link text-sm',
                                                             'font-bold text-black' => $city->id === $region->id,
                                                        ])
                                                    >{{ $city->name }}</a>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>

                </div>
            </div>

        </div>
    </header>

    <main class="grow">
        <div class="container mx-auto py-3 px-4">
            @yield('content')
        </div>
    </main>

    <footer>
        <div class="container mx-auto py-3 px-4">
            Footer
        </div>
    </footer>
    <script>
        (function () {
            // regions popup
            var button = document.getElementById('region-button');
            var buttonClose = document.getElementById('region-popup-close');
            var popup = document.getElementById('region-popup');
            var backdrop = document.getElementById('region-popup-overlay');

            function setOpen(open) {
                popup.classList.toggle('is-open', open);
                backdrop.classList.toggle('is-open', open);
                button.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            button.addEventListener('click', function () { setOpen(!popup.classList.contains('is-open')); });
            buttonClose.addEventListener('click', function () { setOpen(false); });
            backdrop.addEventListener('click', function(event) {
                // Находим сам попап (белое окно)
                const popup = document.getElementById('region-popup');

                // Проверяем: если кликнули на подложку И клик пришелся НЕ внутрь белого окна
                if (event.target === this && !popup.contains(event.target)) {
                    // скрываем подложку со всем содержимым
                    setOpen(false);

                    // Либо, если вы скрываете через классы Tailwind, можно написать:
                    // this.classList.add('hidden');
                }
            });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(false); });

            document.querySelectorAll('[data-area-toggle]').forEach(function (toggle) {
                toggle.addEventListener('click', function () { toggle.parentElement.classList.toggle('is-open'); });
            });
            // - - - regions popup
        })();
    </script>
</body>
</html>
