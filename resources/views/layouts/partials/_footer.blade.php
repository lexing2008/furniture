<footer class="bg-green-first">
    @php
        $locale = app()->getLocale();
        $regionName = $locale === 'en' ? $region->name_en : $region->name_in;
        $catalog = [
            'Мебель в гостиную в :city',
            'Кухонная мебель в :city',
            'Мебель для спальни в :city',
            'Детская мебель в :city',
            'Гардеробная мебель в :city',
            'Мебель в прихожую в :city',
            'Мебель в ванную и туалет в :city',
            'Офисная мебель в :city',
            'Текстиль в :city',
            'Услуги в :city',
            'Мебель для отдыха в :city',
        ];
        $otherCities = $locale === 'en'
            ? ['Brest', 'Vitebsk', 'Gomel', 'Grodno', 'Minsk', 'Mogilev']
            : ['Бресте', 'Витебске', 'Гомеле', 'Гродно', 'Минске', 'Могилеве'];
    @endphp

    <div class="container mx-auto py-3 px-4 pt-5">

        <div class="flex flex-col md:flex-row justify-between gap-5">
            <div>
                <div class="text-white font-bold mb-2">
                    {{ __('О нас') }}
                    <div class="h-0.5 w-7 bg-white/90"></div>
                </div>
                <div class="max-w-md text-white/80 text-xs">
                    {{ __(':region :domain — это мебельный торговый центр в интернете, где продавцы и производители могут разместить свою мебель и услуги.', [
                        'region' => $locale === 'en' ? $region->name_en : $region->name,
                        'domain' => config('app.domain'),
                    ]) }}
                </div>

                <div class="flex flex-col my-2">
                    <div>
                        <a href="{{ route('account.register', $region->subdomain) }}" class="text-white/80 hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">{{ __('Регистрация') }}</a>
                    </div>
                    <div>
                        <a href="{{ route('account.login') }}" class="text-white/80 hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">{{ __('Вход') }}</a>
                    </div>
                    <div>
                        <a href="#" class="text-xs text-white/80 hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">{{ __('Пользовательское соглашение') }}</a>
                    </div>
                </div>
            </div>

            <div>
                <div class="text-white font-bold mb-2">
                    {{ __('Каталог мебели') }}
                    <div class="h-0.5 w-7 bg-white/90"></div>
                </div>
                <div class="grid grid-cols-2 gap-1 gap-x-6 text-white/80 text-xs">
                    @foreach ($catalog as $key)
                        <div>
                            <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">
                                {{ __($key, ['city' => $regionName]) }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="max-sm:mt-3">
                <div class="text-white font-bold mb-2">
                    {{ __('Другие города') }}
                    <div class="h-0.5 w-7 bg-white/90"></div>
                </div>
                <div class="grid grid-cols-2 gap-1 gap-x-6 text-white/80 text-xs">
                    @foreach ($otherCities as $city)
                        <div>
                            <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">
                                {{ __('Мебель в :city', ['city' => $city]) }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <hr class="my-6 border-t border-white/15" />

        <div class="mb-2 text-center text-white/70 text-sm">
            {{ __('© :year Мебельный портал :domain', [
                'year' => date('Y'),
                'domain' => $region->subdomain.'.'.config('app.domain'),
            ]) }}
        </div>
    </div>
</footer>
