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
        body { margin: 0; font-family: system-ui, -apple-system, "Segoe UI", sans-serif; color: #1f2937; background: #f9fafb; }
        .header { position: relative; display: flex; align-items: center; gap: 16px; padding: 12px 24px; background: #fff; border-bottom: 1px solid #e5e7eb; }
        .header a.logo { font-weight: 700; color: inherit; text-decoration: none; }
        .region-button { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border: 1px solid #d1d5db; border-radius: 6px; background: #fff; font: inherit; cursor: pointer; }
        .region-button:hover { background: #f3f4f6; }
        .popup-backdrop { position: fixed; inset: 0; z-index: 10; display: none; background: rgba(17, 24, 39, .5); }
        .popup-backdrop.is-open { display: block; }
        .popup { position: absolute; top: 64px; left: 24px; z-index: 11; display: none; width: 360px; max-width: calc(100vw - 48px); max-height: 70vh; overflow-y: auto; padding: 16px; border-radius: 8px; background: #fff; box-shadow: 0 10px 30px rgba(0, 0, 0, .2); }
        .popup.is-open { display: block; }
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
    <header class="header">
        <div class="container mx-auto  p-3 flex justify-between">
            <div>
                <div class="text-xs">Мебель в {{ $region->name_in }}</div>
                <a href="{{ $country->url() }}" class="logo">{{ config('app.name') }}</a>
            </div>


            <div>
                <span class="border-b border-dashed border-bord-blue  text-[19px] font-semibold text-brand-blue cursor-pointer hover:border-transparent">{{ $region->name }}</span>
            </div>
            <button type="button" class="region-button ml-3" id="region-button" aria-haspopup="true" aria-expanded="false">
                {{ $region->name }}
                <span aria-hidden="true">▾</span>
            </button>

            <div class="popup-backdrop" id="region-backdrop"></div>

            <div class="popup" id="region-popup" role="dialog" aria-label="{{ $country->name }}">
                <h2>{{ $country->name }}</h2>

                <div class="my-1 text-xs">Выберите город. Будет показана мебель в вашем городе или с доставкой в ваш регион.</div>

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
    </header>

    <main class="grow">
        <div class="container mx-auto p-3">
            @yield('content')
        </div>
    </main>

    <footer>
        <div class="container mx-auto p-3">
            Footer
        </div>
    </footer>
    <script>
        (function () {
            // regions popup
            var button = document.getElementById('region-button');
            var popup = document.getElementById('region-popup');
            var backdrop = document.getElementById('region-backdrop');

            function setOpen(open) {
                popup.classList.toggle('is-open', open);
                backdrop.classList.toggle('is-open', open);
                button.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            button.addEventListener('click', function () { setOpen(!popup.classList.contains('is-open')); });
            backdrop.addEventListener('click', function () { setOpen(false); });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setOpen(false); });

            document.querySelectorAll('[data-area-toggle]').forEach(function (toggle) {
                toggle.addEventListener('click', function () { toggle.parentElement.classList.toggle('is-open'); });
            });
            // - - - regions popup
        })();
    </script>
</body>
</html>
